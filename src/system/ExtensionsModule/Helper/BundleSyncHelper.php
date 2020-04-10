<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Helper;

use Composer\Semver\Semver;
use RuntimeException;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Helper\BundlesSchemaHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionDependencyRepository;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionVarRepositoryInterface;
use Zikula\ExtensionsModule\Event\ExtensionEntityPreInsertEvent;

/**
 * Helper functions for the extensions bundle
 */
class BundleSyncHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    /**
     * @var ExtensionVarRepositoryInterface
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
     * @var BundlesSchemaHelper
     */
    private $bundlesSchemaHelper;

    /**
     * @var ComposerValidationHelper
     */
    private $composerValidationHelper;

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionVarRepositoryInterface $extensionVarRepository,
        ExtensionDependencyRepository $extensionDependencyRepository,
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher,
        ExtensionStateHelper $extensionStateHelper,
        BundlesSchemaHelper $bundlesSchemaHelper,
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
        $this->bundlesSchemaHelper = $bundlesSchemaHelper;
        $this->composerValidationHelper = $composerValidationHelper;
        $this->session = $session;
    }

    /**
     * Scan the extensions directory for bundles and returns an array with all (potential) bundles found.
     */
    public function scanForBundles($includeCore = false): array
    {
        // sync the extensions directory and the bundles table
        $this->bundlesSchemaHelper->load();
        $scanner = $this->bundlesSchemaHelper->getScanner();
        foreach ($scanner->getInvalid() as $invalidName) {
            $this->session->getFlashBag()->add(
                'warning',
                $this->translator->trans(
                    'WARNING: %extension% has an invalid composer.json file which could not be decoded.',
                    ['%extension%' => $invalidName]
                )
            );
        }
        $extensions = $scanner->getExtensionsMetaData();

        $bundles = [];
        $srcDir = $this->kernel->getProjectDir() . '/src/';
        /** @var MetaData $bundleMetaData */
        foreach ($extensions as $name => $bundleMetaData) {
            foreach ($bundleMetaData->getPsr4() as $ns => $path) {
                $this->kernel->getAutoloader()->addPsr4($ns, $srcDir . $path);
            }

            $bundleClass = $bundleMetaData->getClass();

            /** @var $bundle \Zikula\ExtensionsModule\AbstractExtension */
            $bundle = new $bundleClass();
            $bundleMetaData->setTranslator($this->translator);
            $bundleVersionArray = $bundleMetaData->getFilteredVersionInfoArray();

            $finder = new Finder();
            $finder->files()->in($bundle->getPath())->depth(0)->name('composer.json');
            foreach ($finder as $splFileInfo) {
                // there will only be one loop here
                $this->composerValidationHelper->check($splFileInfo);
                if ($this->composerValidationHelper->isValid()) {
                    $bundles[$bundle->getName()] = $bundleVersionArray;
                    $bundles[$bundle->getName()]['oldnames'] = $bundleVersionArray['oldnames'] ?? '';
                } else {
                    $this->session->getFlashBag()->add(
                        'error',
                        $this->translator->trans(
                            'Cannot load %extension% because the composer file is invalid.',
                            ['%extension%' => $bundle->getName()]
                        )
                    );
                    foreach ($this->composerValidationHelper->getErrors() as $error) {
                        $this->session->getFlashBag()->add('error', $error);
                    }
                }
            }
        }

        if ($includeCore) {
            $this->appendCoreExtensionsMetaData($bundles);
        }
        $this->validate($bundles);

        return $bundles;
    }

    private function appendCoreExtensionsMetaData(array &$extensions): void
    {
        foreach (ZikulaKernel::$coreExtension as $systemModule => $bundleClass) {
            $bundle = $this->kernel->getBundle($systemModule);
            if ($bundle instanceof AbstractExtension) {
                $extensions[$systemModule] = $bundle->getMetaData()->getFilteredVersionInfoArray();
            }
        }
    }

    /**
     * Validate the extensions and ensure there are no duplicate names, display names or urls.
     *
     * @throws FatalError
     */
    private function validate(array $extensions = []): void
    {
        $fieldNames = ['name', 'displayname', 'url'];
        $moduleValues = [
            'name' => [],
            'displayname' => [],
            'url' => []
        ];

        // check for duplicate name, display name or url
        foreach ($extensions as $dir => $modInfo) {
            foreach ($fieldNames as $fieldName) {
                $key = mb_strtolower($modInfo[$fieldName]);
                if (!empty($moduleValues[$fieldName][$key]) && !empty($modInfo[$fieldName])) {
                    $message = $this->translator->trans('Fatal error: Two extensions share the same %field%. [%ext1%] and [%ext2%]', [
                        '%field%' => $fieldName,
                        '%ext1%' => $modInfo['name'],
                        '%ext2%' => $moduleValues[$fieldName][$key]
                    ]);
                    throw new FatalError($message, 500, error_get_last());
                }
                $moduleValues[$fieldName][$key] = $dir;
            }
        }
    }

    /**
     * Sync extensions in the filesystem and the extensions table.
     *
     * @return array $upgradedExtensions[<name>] = <version>
     */
    public function syncExtensions(array $extensionsFromFile, bool $forceDefaults = false): array
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
     */
    private function syncUpdatedExtensions(
        array $extensionsFromFile,
        array &$extensionsFromDB,
        bool $forceDefaults = false
    ): void {
        foreach ($extensionsFromFile as $name => $extensionFromFile) {
            foreach ($extensionsFromDB as $dbname => $extensionFromDB) {
                if (isset($extensionFromDB['name']) && in_array($extensionFromDB['name'], (array)$extensionFromFile['oldnames'], true)) {
                    // migrate its modvars
                    $this->extensionVarRepository->updateName($dbname, $name);
                    // rename the extension register
                    $this->extensionRepository->updateName($dbname, $name);
                    // replace the old extension with the new one in the $extensionsFromDB array
                    $extensionsFromDB[$name] = $extensionFromDB;
                    unset($extensionsFromDB[$dbname]);
                }
            }

            // If extension was previously determined to be incompatible with the core. return to original state
            if (isset($extensionsFromDB[$name]) && $extensionsFromDB[$name]['state'] > 10) {
                $extensionsFromDB[$name]['state'] -= Constant::INCOMPATIBLE_CORE_SHIFT;
                $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], $extensionsFromDB[$name]['state']);
            }

            // update the DB information for this extension to reflect user settings (e.g. url)
            if (isset($extensionsFromDB[$name]['id'])) {
                $extensionFromFile['id'] = $extensionsFromDB[$name]['id'];
                if (Constant::STATE_UNINITIALISED !== $extensionsFromDB[$name]['state'] && Constant::STATE_INVALID !== $extensionsFromDB[$name]['state']) {
                    unset($extensionFromFile['version']);
                }
                if (!$forceDefaults) {
                    unset($extensionFromFile['displayname'], $extensionFromFile['description'], $extensionFromFile['url']);
                }

                unset($extensionFromFile['oldnames'], $extensionFromFile['dependencies']);

                /** @var ExtensionEntity $extension */
                $extension = $this->extensionRepository->find($extensionFromFile['id']);
                $extension->merge($extensionFromFile);
                $this->extensionRepository->persistAndFlush($extension);
            }

            // check extension core requirement is compatible with current core
            $coreCompatibility = $extensionFromFile['coreCompatibility'];
            if (isset($extensionsFromDB[$name])) {
                if (!Semver::satisfies(ZikulaKernel::VERSION, $coreCompatibility)) {
                    // extension is incompatible with current core
                    $extensionsFromDB[$name]['state'] += Constant::INCOMPATIBLE_CORE_SHIFT;
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
     */
    private function syncLostExtensions(array $extensionsFromFile, array &$extensionsFromDB): void
    {
        foreach ($extensionsFromDB as $extensionName => $unusedVariable) {
            if ($this->kernel::isCoreExtension($extensionName) || array_key_exists($extensionName, $extensionsFromFile)) {
                continue;
            }

            $lostExtension = $this->extensionRepository->get($extensionName); // must obtain Entity because value from $extensionsFromDB is only an array
            if (!$lostExtension) {
                throw new RuntimeException($this->translator->trans('Error! Could not load data for %extension%.', ['%extension%' => $extensionName]));
            }
            $lostExtensionState = $lostExtension->getState();
            if ((Constant::STATE_INVALID === $lostExtensionState)
                || ($lostExtensionState === Constant::STATE_INVALID + Constant::INCOMPATIBLE_CORE_SHIFT)) {
                // extension was invalid and subsequently removed from file system,
                // or extension was incompatible with core and subsequently removed, delete it
                $this->extensionRepository->removeAndFlush($lostExtension);
            } elseif ((Constant::STATE_UNINITIALISED === $lostExtensionState)
                || ($lostExtensionState === Constant::STATE_UNINITIALISED + Constant::INCOMPATIBLE_CORE_SHIFT)) {
                // extension was uninitialised and subsequently removed from file system, delete it
                $this->extensionRepository->removeAndFlush($lostExtension);
            } else {
                // Set state of extension to 'missing'
                // This state cannot be reached in with an ACTIVE bundle. - ACTIVE bundles are part of the pre-compiled Kernel.
                // extensions that are inactive can be marked as missing.
                $this->extensionStateHelper->updateState($lostExtension->getId(), Constant::STATE_MISSING);
            }

            unset($extensionsFromDB[$extensionName]);
        }
    }

    /**
     * Add extensions to the DB that have been added to the filesystem.
     *  - add uninitialized extensions
     *  - update missing or invalid extensions
     *
     * @return array $upgradedExtensions[<name>] => <version>
     */
    private function syncAddedExtensions(array $extensionsFromFile, array $extensionsFromDB): array
    {
        $upgradedExtensions = [];

        foreach ($extensionsFromFile as $name => $extensionFromFile) {
            if (empty($extensionsFromDB[$name])) {
                $extensionFromFile['state'] = Constant::STATE_UNINITIALISED;
                if (!$extensionFromFile['version']) {
                    // set state to invalid if we can't determine a version
                    $extensionFromFile['state'] = Constant::STATE_INVALID;
                } else {
                    $coreCompatibility = $extensionFromFile['coreCompatibility'];
                    // shift state if extension is incompatible with core version
                    $extensionFromFile['state'] = Semver::satisfies(ZikulaKernel::VERSION, $coreCompatibility)
                        ? $extensionFromFile['state']
                        : $extensionFromFile['state'] + Constant::INCOMPATIBLE_CORE_SHIFT;
                }

                // unset vars that don't matter
                unset($extensionFromFile['oldnames'], $extensionFromFile['dependencies']);

                // insert new extension to db
                $newExtension = new ExtensionEntity();
                $newExtension->merge($extensionFromFile);
                $extensionPreInsertEvent = new ExtensionEntityPreInsertEvent($newExtension);
                $this->dispatcher->dispatch($extensionPreInsertEvent);
                if (!$extensionPreInsertEvent->isPropagationStopped()) {
                    $this->extensionRepository->persistAndFlush($newExtension);
                }
            } else {
                // extension is in the db already
                if ((Constant::STATE_MISSING === $extensionsFromDB[$name]['state'])
                    || ($extensionsFromDB[$name]['state'] === Constant::STATE_MISSING + Constant::INCOMPATIBLE_CORE_SHIFT)) {
                    // extension was lost, now it is here again
                    $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], Constant::STATE_INACTIVE);
                } elseif (((Constant::STATE_INVALID === $extensionsFromDB[$name]['state'])
                        || ($extensionsFromDB[$name]['state'] === Constant::STATE_INVALID + Constant::INCOMPATIBLE_CORE_SHIFT))
                    && $extensionFromFile['version']) {
                    $coreCompatibility = $extensionFromFile['coreCompatibility'];
                    if (Semver::satisfies(ZikulaKernel::VERSION, $coreCompatibility)) {
                        // extension was invalid, now it is valid
                        $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], Constant::STATE_UNINITIALISED);
                    }
                }

                if ($extensionsFromDB[$name]['version'] !== $extensionFromFile['version']) {
                    if (Constant::STATE_UNINITIALISED !== $extensionsFromDB[$name]['state'] &&
                        Constant::STATE_INVALID !== $extensionsFromDB[$name]['state']) {
                        $this->extensionStateHelper->updateState($extensionsFromDB[$name]['id'], Constant::STATE_UPGRADED);
                        $upgradedExtensions[$name] = $extensionFromFile['version'];
                    }
                }
            }
        }

        return $upgradedExtensions;
    }
}
