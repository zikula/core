<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Helper;

use Composer\Semver\Semver;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionDependencyRepository;
use Zikula\ExtensionsModule\Exception\ExtensionDependencyException;

class ExtensionDependencyHelper
{
    /**
     * @var ExtensionDependencyRepository
     */
    private $extensionDependencyRepo;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionEntityRepo;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var array
     */
    private $installedPackages = [];

    /**
     * ExtensionDependencyHelper constructor.
     *
     * @param ExtensionDependencyRepository $extensionDependencyRepo
     * @param ExtensionRepositoryInterface $extensionEntityRepo
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function __construct(
        ExtensionDependencyRepository $extensionDependencyRepo,
        ExtensionRepositoryInterface $extensionEntityRepo,
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->extensionDependencyRepo = $extensionDependencyRepo;
        $this->extensionEntityRepo = $extensionEntityRepo;
        $this->kernel = $kernel;
    }

    /**
     * Get an array of ExtensionEntities that are dependent on the $extension.
     *
     * @param ExtensionEntity $extension
     * @return ExtensionEntity[]
     */
    public function getDependentExtensions(ExtensionEntity $extension)
    {
        $requiredDependents = [];
        /** @var ExtensionDependencyEntity[] $dependents */
        $dependents = $this->extensionDependencyRepo->findBy([
            'modname' => $extension->getName(),
            'status' => MetaData::DEPENDENCY_REQUIRED
        ]);
        foreach ($dependents as $dependent) {
            $foundExtension = $this->extensionEntityRepo->findOneBy([
                'id' => $dependent->getModid(),
                'state' => Constant::STATE_ACTIVE
            ]);
            if (!is_null($foundExtension)) {
                $requiredDependents[] = $foundExtension;
            }
        }

        return $requiredDependents;
    }

    /**
     * Get an array of dependencies that are not currently met by the system and active extensions.
     *
     * @param ExtensionEntity $extension
     * @return ExtensionDependencyEntity[]
     */
    public function getUnsatisfiedExtensionDependencies(ExtensionEntity $extension)
    {
        $unsatisfiedDependencies = [];
        $dependencies = $this->extensionDependencyRepo->findBy(['modid' => $extension->getId()]);
        /** @var ExtensionDependencyEntity[] $dependencies */
        foreach ($dependencies as $dependency) {
            if ($this->bundleDependencySatisfied($dependency)) {
                continue;
            }
            $foundExtension = $this->extensionEntityRepo->get($dependency->getModname());
            if (!is_null($foundExtension)
                && Constant::STATE_ACTIVE == $foundExtension->getState()
                && $this->meetsVersionRequirements($dependency->getMinversion(), $dependency->getMaxversion(), $foundExtension->getVersion())) {
                continue;
            }
            $this->checkForFatalDependency($dependency);
            // get and set reason from bundle metaData temporarily
            if ($dependency->getReason() === false) {
                $bundle = $this->kernel->getModule($dependency->getModname());
                if (null !== $bundle) {
                    $bundleDependencies = $bundle->getMetaData()->getDependencies();
                    foreach ($bundleDependencies as $bundleDependency) {
                        if ($bundleDependency['modname'] == $dependency->getModname()) {
                            $reason = isset($dependency['reason']) ? $dependency['reason'] : '';
                            $dependency->setReason($reason);
                        }
                    }
                }
                $dependency->setReason('');
            }
            $unsatisfiedDependencies[$dependency->getId()] = $dependency;
        }

        return $unsatisfiedDependencies;
    }

    /**
     * Check for 'fatal' dependency.
     *
     * @param ExtensionDependencyEntity $dependency
     * @throws ExtensionDependencyException
     */
    private function checkForFatalDependency(ExtensionDependencyEntity $dependency)
    {
        $foundExtension = $this->extensionEntityRepo->get($dependency->getModname());
        if ($dependency->getStatus() == MetaData::DEPENDENCY_REQUIRED
            && (is_null($foundExtension) // never in the filesystem
                || $foundExtension->getState() == Constant::STATE_MISSING
                || $foundExtension->getState() == Constant::STATE_INVALID
                || $foundExtension->getState() > 10 // not compatible with current core
            )) {
            throw new ExtensionDependencyException(sprintf('Could not find a core-compatible, required dependency: %s.', $dependency->getModname()));
        }
        if (!is_null($foundExtension) && !$this->meetsVersionRequirements($dependency->getMinversion(), $dependency->getMaxversion(), $foundExtension->getVersion())) {
            $versionString = ($dependency->getMinversion() == $dependency->getMaxversion()) ? $dependency->getMinversion() : $dependency->getMinversion() . ' - ' . $dependency->getMaxversion();
            throw new ExtensionDependencyException(sprintf('A required dependency is found, but does not meet version requirements: %s (%s)', $dependency->getModname(), $versionString));
        }
    }

    /**
     * Compute if bundle requirements are met.
     *
     * @param ExtensionDependencyEntity $dependency
     * @return bool
     */
    private function bundleDependencySatisfied(ExtensionDependencyEntity &$dependency)
    {
        if ($dependency->getModname() == "php") {
            // Do not use PHP_VERSION constant, because it might throw off the semver parser.
            $phpVersion = PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION . "." . PHP_RELEASE_VERSION;
            if (!Semver::satisfies($phpVersion, $dependency->getMinversion())) {
                throw new \InvalidArgumentException('This module requires a higher version of PHP than you currently have installed.');
            }

            return true;
        }
        if (strpos($dependency->getModname(), 'composer/') !== false) {
            // this specifically is for `composer/installers` but will catch all with `composer/`
            return true;
        }

        return true;
        // The section below is disabled because it doesn't work with dependencies that are in the module's own vendor directory.
        /*
        if (strpos($dependency->getModname(), '/') !== false) {
            if ($this->kernel->isBundle($dependency->getModname())) {
                if (empty($this->installedPackages)) {
                    // create and cache installed packages from composer.lock file
                    $appPath = $this->kernel->getRootDir();
                    $composerLockPath = realpath($appPath . '/../') . 'composer.lock';
                    $packages = json_decode(file_get_contents($composerLockPath), true);
                    foreach ($packages as $package) {
                        $this->installedPackages[$package['name']] = $package;
                    }
                }
                if (Semver::satisfies($this->installedPackages[$dependency->getModname()]['version'], $dependency->getMinversion())) {
                    return true;
                }
            }

            throw new \InvalidArgumentException(sprintf('This dependency can only be resolved by adding %s to the core\'s composer.json file and running `composer update`.', $dependency->getModname()));
        }

        return false;*/
    }

    /**
     * Determine if a $currentVersion value is between $requiredMin and $requiredMax.
     *
     * @param string $requiredMin
     * @param string $requiredMax
     * @param string $currentVersion
     * @return bool
     */
    private function meetsVersionRequirements($requiredMin, $requiredMax, $currentVersion)
    {
        if (($requiredMin == $requiredMax) || empty($requiredMax)) {
            $compatibilityString = (preg_match("/>|=|</", $requiredMin)) ? $requiredMin : ">=$requiredMin";
        } else {
            $compatibilityString = "$requiredMin - $requiredMax";
        }

        return Semver::satisfies($currentVersion, $compatibilityString);
    }
}
