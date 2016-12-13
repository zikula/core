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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;
use Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionDependencyRepository;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionVarRepository;
use Zikula\ExtensionsModule\ExtensionEvents;

/**
 * Helper functions for the extensions bundle
 */
class BundleSyncHelper
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ExtensionRepository
     */
    private $extensionRepository;

    /**
     * @var ExtensionVarRepository
     */
    private $extensionVarRepository;

    /**
     * @var ExtensionDependencyRepository
     */
    private $extensionDependencyRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ExtensionStateHelper
     */
    private $extensionStateHelper;

    /**
     * @var BootstrapHelper
     */
    private $bootstrapHelper;

    /**
     * @var ComposerValidationHelper
     */
    private $composerValidationHelper;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * BundleSyncHelper constructor.
     *
     * @param KernelInterface $kernel
     * @param ExtensionRepository $extensionRepository
     * @param ExtensionVarRepository $extensionVarRepository
     * @param ExtensionDependencyRepository $extensionDependencyRepository
     * @param TranslatorInterface $translator
     * @param EventDispatcherInterface $dispatcher
     * @param ExtensionStateHelper $extensionStateHelper
     * @param ComposerValidationHelper $composerValidationHelper
     * @param SessionInterface $session
     */
    public function __construct(
        KernelInterface $kernel,
        ExtensionRepository $extensionRepository,
        ExtensionVarRepository $extensionVarRepository,
        ExtensionDependencyRepository $extensionDependencyRepository,
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher,
        ExtensionStateHelper $extensionStateHelper,
        BootstrapHelper $bootstrapHelper,
        ComposerValidationHelper $composerValidationHelper,
        SessionInterface $session
    ) {
        $this->kernel = $kernel;
        $this->extensionRepository = $extensionRepository;
        $this->extensionVarRepository = $extensionVarRepository;
        $this->extensionDependencyRepository = $extensionDependencyRepository;
        $this->translator = $translator;
        $this->dispatcher = $dispatcher;
        $this->extensionStateHelper = $extensionStateHelper;
        $this->bootstrapHelper = $bootstrapHelper;
        $this->composerValidationHelper = $composerValidationHelper;
        $this->session = $session;
    }

    /**
     * Scan the file system for bundles.
     *
     * This function scans the file system for bundles and returns an array with all (potential) bundles found.
     *
     * @param array $directories
     * @return array Thrown if the user doesn't have admin permissions over the bundle
     * @throws \Exception
     */
    public function scanForBundles(array $directories = [])
    {
        $directories = empty($directories) ? ['system', 'modules'] : $directories;

        // sync the filesystem and the bundles table
        $this->bootstrapHelper->load();

        // Get all bundles on filesystem
        $bundles = [];

        $scanner = new Scanner();
        $scanner->scan($directories, 5);
        $newModules = $scanner->getModulesMetaData();

        // scan for all bundle-type bundles (psr-4) in either /system or /bundles
        /** @var MetaData $bundleMetaData */
        foreach ($newModules as $name => $bundleMetaData) {
            foreach ($bundleMetaData->getPsr4() as $ns => $path) {
                $this->kernel->getAutoloader()->addPsr4($ns, $path);
            }

            $bundleClass = $bundleMetaData->getClass();

            /** @var $bundle \Zikula\Core\AbstractModule */
            $bundle = new $bundleClass();
            $bundleMetaData->setTranslator($this->translator);
            $bundleMetaData->setDirectoryFromBundle($bundle);
            $bundleVersionArray = $bundleMetaData->getFilteredVersionInfoArray();

            // loads the gettext domain for 3rd party bundles
            if (!strpos($bundle->getPath(), 'bundles') === false) {
                \ZLanguage::bindModuleDomain($bundle->getName());
            }

            $bundleVersionArray['capabilities'] = serialize($bundleVersionArray['capabilities']);
            $bundleVersionArray['securityschema'] = serialize($bundleVersionArray['securityschema']);
            $bundleVersionArray['dependencies'] = serialize($bundleVersionArray['dependencies']);

            $finder = new Finder();
            $finder->files()->in($bundle->getPath())->depth(0)->name('composer.json');
            foreach ($finder as $splFileInfo) {
                // there will only be one loop here
                $this->composerValidationHelper->check($splFileInfo);
                if ($this->composerValidationHelper->isValid()) {
                    $bundles[$bundle->getName()] = $bundleVersionArray;
                    $bundles[$bundle->getName()]['oldnames'] = isset($bundleVersionArray['oldnames']) ? $bundleVersionArray['oldnames'] : '';
                } else {
                    $this->session->getFlashBag()->add('error', $this->translator->__f('Cannot load %extension because the composer file is invalid.', ['%extension' => $bundle->getName()]));
                    foreach ($this->composerValidationHelper->getErrors() as $error) {
                        $this->session->getFlashBag()->add('error', $error);
                    }
                }
            }
        }

        $this->validate($bundles);

        return $bundles;
    }

    /**
     * Validate the extensions and ensure there are no duplicate names, displaynames or urls.
     *
     * @param array $extensions
     * @throws FatalErrorException
     */
    private function validate(array $extensions)
    {
        $modulenames = [];
        $displaynames = [];
        $urls = [];

        // check for duplicate names, display names or urls
        foreach ($extensions as $dir => $modInfo) {
            if (isset($modulenames[strtolower($modInfo['name'])])) {
                throw new FatalErrorException($this->translator->__f('Fatal Error: Two extensions share the same name. [%ext1%] and [%ext2%]', [
                    '%ext1%' => $modInfo['name'],
                    '%ext2%' => $modulenames[strtolower($modInfo['name'])]
                ]));
            }

            if (isset($displaynames[strtolower($modInfo['displayname'])])) {
                throw new FatalErrorException($this->translator->__f('Fatal Error: Two extensions share the same displayname. [%ext1%] and [%ext2%]', [
                    '%ext1%' => $modInfo['name'],
                    '%ext2%' => $modulenames[strtolower($modInfo['name'])]
                ]));
            }

            if (isset($urls[strtolower($modInfo['url'])])) {
                throw new FatalErrorException($this->translator->__f('Fatal Error: Two extensions share the same url. [%ext1%] and [%ext2%]', [
                    '%ext1%' => $modInfo['name'],
                    '%ext2%' => $modulenames[strtolower($modInfo['name'])]
                ]));
            }

            $modulenames[strtolower($modInfo['name'])] = $dir;
            $displaynames[strtolower($modInfo['displayname'])] = $dir;
            $urls[strtolower($modInfo['url'])] = $dir;
        }
    }

    /**
     * Sync extensions in the filesystem and the database.
     *
     * @param array $extensionsFromFile
     * @param bool $forceDefaults
     * @return array $upgradedExtensions[<name>] = <version>
     */
    public function syncExtensions(array $extensionsFromFile, $forceDefaults = false)
    {
        // Get all extensions in DB, indexed by name
        $extensionsFromDB = $this->extensionRepository->getIndexedArrayCollection('name');

        // see if any extensions have changed since last regeneration
        $this->syncUpdatedExtensions($extensionsFromFile, $extensionsFromDB, $forceDefaults);

        // See if any extensions have been lost since last sync
        $this->syncLostExtensions($extensionsFromFile, $extensionsFromDB);

        // See any extensions have been gained since last sync,
        // or if any current extensions have been upgraded
        $upgradedExtensions = $this->syncAddedExtensions($extensionsFromFile, $extensionsFromDB);

        // Clear and reload the dependencies table with all current dependencies
        $this->extensionDependencyRepository->reloadExtensionDependencies($extensionsFromFile);

        return $upgradedExtensions;
    }

    /**
     * Sync extensions that are already in the Database.
     *  - update from old names
     *  - update compatibility
     *  - update user settings (or reset to defaults)
     *  - ensure current core compatibility
     *
     * @param array $extensionsFromFile
     * @param array $extensionsFromDB
     * @param bool $forceDefaults
     */
    private function syncUpdatedExtensions(array $extensionsFromFile, array &$extensionsFromDB, $forceDefaults = false)
    {
        foreach ($extensionsFromFile as $name => $extensionFromFile) {
            foreach ($extensionsFromDB as $dbname => $extensionFromDB) {
                if (isset($extensionFromDB['name']) && in_array($extensionFromDB['name'], (array)$extensionFromFile['oldnames'])) {
                    // migrate its modvars
                    $this->extensionVarRepository->updateName($dbname, $name);
                    // rename the module register
                    $this->extensionRepository->updateName($dbname, $name);
                    // replace the old module with the new one in the $extensionsFromDB array
                    $extensionsFromDB[$name] = $extensionFromDB;
                    unset($extensionsFromDB[$dbname]);
                }
            }

            // If extension was previously determined to be incompatible with the core. return to original state
            if (isset($extensionsFromDB[$name]) && $extensionsFromDB[$name]['state'] > 10) {
                $extensionsFromDB[$name]['state'] = $extensionsFromDB[$name]['state'] - ExtensionApi::INCOMPATIBLE_CORE_SHIFT;
                $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], $extensionsFromDB[$name]['state']);
            }

            // update the DB information for this extension to reflect user settings (e.g. url)
            if (isset($extensionsFromDB[$name]['id'])) {
                $extensionFromFile['id'] = $extensionsFromDB[$name]['id'];
                if ($extensionsFromDB[$name]['state'] != ExtensionApi::STATE_UNINITIALISED && $extensionsFromDB[$name]['state'] != ExtensionApi::STATE_INVALID) {
                    unset($extensionFromFile['version']);
                }
                if (!$forceDefaults) {
                    unset($extensionFromFile['displayname']);
                    unset($extensionFromFile['description']);
                    unset($extensionFromFile['url']);
                }

                unset($extensionFromFile['oldnames']);
                unset($extensionFromFile['dependencies']);
                $extensionFromFile['capabilities'] = unserialize($extensionFromFile['capabilities']);
                $extensionFromFile['securityschema'] = unserialize($extensionFromFile['securityschema']);
                $extension = $this->extensionRepository->find($extensionFromFile['id']);
                $extension->merge($extensionFromFile);
                $this->extensionRepository->persistAndFlush($extension);
            }

            // check extension core requirement is compatible with current core
            $coreCompatibility = isset($extensionFromFile['corecompatibility'])
                ? $extensionFromFile['corecompatibility']
                : $this->formatCoreCompatibilityString($extensionFromFile['core_min'], $extensionFromFile['core_max']);
            $isCompatible = $this->isCoreCompatible($coreCompatibility);
            if (isset($extensionsFromDB[$name])) {
                if (!$isCompatible) {
                    // extension is incompatible with current core
                    $extensionsFromDB[$name]['state'] = $extensionsFromDB[$name]['state'] + ExtensionApi::INCOMPATIBLE_CORE_SHIFT;
                    $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], $extensionsFromDB[$name]['state']);
                }
                if (isset($extensionsFromDB[$name]['state'])) {
                    $extensionFromFile['state'] = $extensionsFromDB[$name]['state'];
                }
            }
        }
    }

    /**
     * Remove extensions from the DB that have been removed from the filesystem.
     *
     * @param array $extensionsFromFile
     * @param array $extensionsFromDB
     */
    private function syncLostExtensions(array $extensionsFromFile, array &$extensionsFromDB)
    {
        foreach ($extensionsFromDB as $name => $unusedVariable) {
            if (array_key_exists($name, $extensionsFromFile)) {
                continue;
            }

            $lostModule = $this->extensionRepository->get($name); // must obtain Entity because value from $extensionsFromDB is only an array
            if (!$lostModule) {
                throw new \RuntimeException($this->translator->__f('Error! Could not load data for module %s.', [$name]));
            }
            $lostModuleState = $lostModule->getState();
            if (($lostModuleState == ExtensionApi::STATE_INVALID)
                || ($lostModuleState == ExtensionApi::STATE_INVALID + ExtensionApi::INCOMPATIBLE_CORE_SHIFT)) {
                // extension was invalid and subsequently removed from file system,
                // or extension was incompatible with core and subsequently removed, delete it
                $this->extensionRepository->removeAndFlush($lostModule);
            } elseif (($lostModuleState == ExtensionApi::STATE_UNINITIALISED)
                || ($lostModuleState == ExtensionApi::STATE_UNINITIALISED + ExtensionApi::INCOMPATIBLE_CORE_SHIFT)) {
                // extension was uninitialised and subsequently removed from file system, delete it
                $this->extensionRepository->removeAndFlush($lostModule);
            } else {
                // Set state of module to 'missing'
                // This state cannot be reached in with an ACTIVE bundle. - ACTIVE bundles are part of the pre-compiled Kernel.
                // extensions that are inactive can be marked as missing.
                $this->extensionStateHelper->updateState($lostModule->getId(), ExtensionApi::STATE_MISSING);
            }

            unset($extensionsFromDB[$name]);
        }
    }

    /**
     * Add extensions to the DB that have been added to the filesystem.
     *  - add uninitialized extensions
     *  - update missing or invalid extensions
     *
     * @param array $extensionsFromFile
     * @param array $extensionsFromDB
     * @return array $upgradedExtensions[<name>] => <version>
     */
    private function syncAddedExtensions(array $extensionsFromFile, array $extensionsFromDB)
    {
        $upgradedExtensions = [];

        foreach ($extensionsFromFile as $name => $extensionFromFile) {
            if (empty($extensionsFromDB[$name])) {
                $extensionFromFile['state'] = ExtensionApi::STATE_UNINITIALISED;
                if (!$extensionFromFile['version']) {
                    // set state to invalid if we can't determine a version
                    $extensionFromFile['state'] = ExtensionApi::STATE_INVALID;
                } else {
                    $coreCompatibility = isset($extensionFromFile['corecompatibility'])
                        ? $extensionFromFile['corecompatibility']
                        : $this->formatCoreCompatibilityString($extensionFromFile['core_min'], $extensionFromFile['core_max']);
                    // shift state if module is incompatible with core version
                    $extensionFromFile['state'] = $this->isCoreCompatible($coreCompatibility)
                        ? $extensionFromFile['state']
                        : $extensionFromFile['state'] + ExtensionApi::INCOMPATIBLE_CORE_SHIFT;
                }

                // unset vars that don't matter
                unset($extensionFromFile['oldnames']);
                unset($extensionFromFile['dependencies']);

                // unserialize vars
                $extensionFromFile['capabilities'] = unserialize($extensionFromFile['capabilities']);
                $extensionFromFile['securityschema'] = unserialize($extensionFromFile['securityschema']);

                // insert new module to db
                $newExtension = new ExtensionEntity();
                $newExtension->merge($extensionFromFile);
                $vetoEvent = new GenericEvent($newExtension);
                $this->dispatcher->dispatch(ExtensionEvents::INSERT_VETO, $vetoEvent);
                if (!$vetoEvent->isPropagationStopped()) {
                    $this->extensionRepository->persistAndFlush($newExtension);
                }
            } else {
                // extension is in the db already
                if (($extensionsFromDB[$name]['state'] == ExtensionApi::STATE_MISSING)
                    || ($extensionsFromDB[$name]['state'] == ExtensionApi::STATE_MISSING + ExtensionApi::INCOMPATIBLE_CORE_SHIFT)) {
                    // extension was lost, now it is here again
                    $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], ExtensionApi::STATE_INACTIVE);
                } elseif ((($extensionsFromDB[$name]['state'] == ExtensionApi::STATE_INVALID)
                        || ($extensionsFromDB[$name]['state'] == ExtensionApi::STATE_INVALID + ExtensionApi::INCOMPATIBLE_CORE_SHIFT))
                    && $extensionFromFile['version']) {
                    $coreCompatibility = isset($extensionFromFile['corecompatibility'])
                        ? $extensionFromFile['corecompatibility']
                        : $this->formatCoreCompatibilityString($extensionFromFile['core_min'], $extensionFromFile['core_max']);
                    $isCompatible = $this->isCoreCompatible($coreCompatibility);
                    if ($isCompatible) {
                        // extension was invalid, now it is valid
                        $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], ExtensionApi::STATE_UNINITIALISED);
                    }
                }

                if ($extensionsFromDB[$name]['version'] != $extensionFromFile['version']) {
                    if ($extensionsFromDB[$name]['state'] != ExtensionApi::STATE_UNINITIALISED &&
                        $extensionsFromDB[$name]['state'] != ExtensionApi::STATE_INVALID) {
                        $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], ExtensionApi::STATE_UPGRADED);
                        $upgradedExtensions[$name] = $extensionFromFile['version'];
                    }
                }
            }
        }

        return $upgradedExtensions;
    }

    /**
     * Determine if $min and $max values are compatible with Current Core version
     *
     * @param string $compatibilityString Semver
     * @return bool
     */
    private function isCoreCompatible($compatibilityString)
    {
        $coreVersion = new version(\ZikulaKernel::VERSION);
        $requiredVersionExpression = new expression($compatibilityString);

        return $requiredVersionExpression->satisfiedBy($coreVersion);
    }

    /**
     * Format a compatibility string suitable for semver comparison using vierbergenlars/php-semver
     *
     * @param null $coreMin
     * @param null $coreMax
     * @return string
     */
    private function formatCoreCompatibilityString($coreMin = null, $coreMax = null)
    {
        $coreMin = !empty($coreMin) ? $coreMin : '1.4.0';
        $coreMax = !empty($coreMax) ? $coreMax : '2.9.99';

        return $coreMin . ' - ' . $coreMax;
    }
}
