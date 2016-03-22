<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionDependencyRepository;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
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
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var array
     */
    private $installedPackages = [];

    /**
     * ExtensionDependencyHelper constructor.
     * @param $extensionDependencyRepo
     * @param $extensionEntityRepo
     */
    public function __construct(
        ExtensionDependencyRepository $extensionDependencyRepo,
        ExtensionRepositoryInterface $extensionEntityRepo,
        KernelInterface $kernel
    ) {
        $this->extensionDependencyRepo = $extensionDependencyRepo;
        $this->extensionEntityRepo = $extensionEntityRepo;
        $this->kernel = $kernel;
    }

    /**
     * Get an array of ExtensionEntities that are dependent on the $extension.
     * @param ExtensionEntity $extension
     * @return ExtensionEntity[]
     */
    public function getDependentExtensions(ExtensionEntity $extension)
    {
        $requiredDependents = [];
        /** @var ExtensionDependencyEntity[] $dependents */
        $dependents = $this->extensionDependencyRepo->findBy([
            'modname' => $extension->getName(),
            'status' => \ModUtil::DEPENDENCY_REQUIRED
        ]);
        foreach ($dependents as $dependent) {
            $foundExtension = $this->extensionEntityRepo->findOneBy([
                'id' => $dependent->getModid(),
                'state' => ExtensionApi::STATE_ACTIVE
            ]);
            if (!is_null($foundExtension)) {
                $requiredDependents[] = $foundExtension;
            }
        }

        return $requiredDependents;
    }

    /**
     * Get an array of dependencies that are not currently met by the system and active extensions.
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
                && ExtensionApi::STATE_ACTIVE == $foundExtension->getState()
                && $this->meetsVersionRequirements($dependency->getMinversion(), $dependency->getMaxversion(), $foundExtension->getVersion())) {
                continue;
            }
            $this->checkForFatalDependency($dependency);
            $unsatisfiedDependencies[$dependency->getId()] = $dependency;
        }

        return $unsatisfiedDependencies;
    }

    /**
     * Check for 'fatal' dependency.
     * @param ExtensionDependencyEntity $dependency
     * @throws ExtensionDependencyException
     */
    private function checkForFatalDependency(ExtensionDependencyEntity $dependency)
    {
        $foundExtension = $this->extensionEntityRepo->get($dependency->getModname());
        if ($dependency->getStatus() == \ModUtil::DEPENDENCY_REQUIRED
            && (is_null($foundExtension) // never in the filesystem
                || $foundExtension->getState() == ExtensionApi::STATE_MISSING
                || $foundExtension->getState() == ExtensionApi::STATE_INVALID
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
     * compute if bundle requirements are met
     * @param ExtensionDependencyEntity $dependency
     * @return bool
     */
    private function bundleDependencySatisfied(ExtensionDependencyEntity &$dependency)
    {
        if ($dependency->getModname() == "php") {
            $phpVersion = new version(PHP_VERSION);
            $requiredVersionExpression = new expression($dependency->getMinversion());

            if (!$requiredVersionExpression->satisfiedBy($phpVersion)) {
                throw new \InvalidArgumentException('This module requires a higher version of PHP than you currently have installed.');
            }

            return true;
        }
        if (strpos($dependency->getModname(), 'composer/') !== false) {
            // @todo this specifically is for `composer/installers` but will catch all with `composer/`
            return true;
        }

        return true;
        /**
         * The section below is disabled because it doesn't work with dependencies that are in the module's own vendor directory.
         */
        /**
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
                $bundleVersion = new version($this->installedPackages[$dependency->getModname()]['version']);
                $requiredVersionExpression = new expression($dependency->getMinversion());

                if ($requiredVersionExpression->satisfiedBy($bundleVersion)) {
                    return true;
                }
            }

            throw new \InvalidArgumentException(sprintf('This dependency can only be resolved by adding %s to the core\'s composer.json file and running `composer update`.', $dependency->getModname()));
        }

        return false;
         */
    }

    /**
     * Determine if a $current value is between $requiredMin and $requiredMax.
     * @param $requiredMin
     * @param $requiredMax
     * @param $current
     * @return bool
     */
    private function meetsVersionRequirements($requiredMin, $requiredMax, $current)
    {
        if (($requiredMin == $requiredMax) || empty($requiredMax)) {
            $compatibilityString = (preg_match("/>|=|</", $requiredMin)) ? $requiredMin : ">=$requiredMin";
        } else {
            $compatibilityString = "$requiredMin - $requiredMax";
        }
        $requiredVersionExpression = new expression($compatibilityString);

        return $requiredVersionExpression->satisfiedBy(new version($current));
    }
}
