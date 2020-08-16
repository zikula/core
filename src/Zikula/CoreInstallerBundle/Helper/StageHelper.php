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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Helper\BundlesSchemaHelper;
use Zikula\Bundle\CoreBundle\Helper\PersistedBundleHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreInstallationPreExtensionInstallation;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreInstallerBundleEvent;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreUpgradePreExtensionUpgrade;
use Zikula\Bundle\CoreInstallerBundle\Stage\AjaxStageInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;

class StageHelper
{
    /**
     * @var ExtensionHelper
     */
    private $extensionHelper;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var PersistedBundleHelper
     */
    private $persistedBundleHelper;

    /**
     * @var BundlesSchemaHelper
     */
    private $bundlesSchemaHelper;

    /**
     * @var CoreInstallerExtensionHelper
     */
    private $coreInstallerExtensionHelper;

    /**
     * @var AdminCategoryHelper
     */
    private $adminCategoryHelper;

    /**
     * @var BlockHelper
     */
    private $blockHelper;

    /**
     * @var ParameterHelper
     */
    private $parameterHelper;

    /**
     * @var SuperUserHelper
     */
    private $superUserHelper;

    /**
     * @var string
     */
    private $installed;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        PersistedBundleHelper $persistedBundleHelper,
        BundlesSchemaHelper $bundlesSchemaHelper,
        ExtensionHelper $extensionHelper,
        EventDispatcherInterface $eventDispatcher,
        CoreInstallerExtensionHelper $coreInstallerExtensionHelper,
        AdminCategoryHelper $adminCategoryHelper,
        BlockHelper $blockHelper,
        ParameterHelper $parameterHelper,
        SuperUserHelper $superUserHelper,
        string $installed
    ) {
        $this->kernel = $kernel;
        $this->persistedBundleHelper = $persistedBundleHelper;
        $this->bundlesSchemaHelper = $bundlesSchemaHelper;
        $this->extensionHelper = $extensionHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->coreInstallerExtensionHelper = $coreInstallerExtensionHelper;
        $this->adminCategoryHelper = $adminCategoryHelper;
        $this->blockHelper = $blockHelper;
        $this->parameterHelper = $parameterHelper;
        $this->superUserHelper = $superUserHelper;
        $this->installed = $installed;
    }

    /**
     * Specific stages are assigned in Ajax(Installer|Upgrader)Stage
     *
     * @throws \Exception
     */
    public function executeStage(string $stageName): bool
    {
        switch ($stageName) {
            case 'bundles':
                return $this->createBundles();
            case 'install_event':
                return $this->dispatchEvent(new CoreInstallationPreExtensionInstallation());
            case 'extensions':
                return $this->coreInstallerExtensionHelper->install('ZikulaExtensionsModule');
            case 'settings':
                return $this->coreInstallerExtensionHelper->install('ZikulaSettingsModule');
            case 'theme':
                return $this->coreInstallerExtensionHelper->install('ZikulaThemeModule');
            case 'admin':
                return $this->coreInstallerExtensionHelper->install('ZikulaAdminModule');
            case 'permissions':
                return $this->coreInstallerExtensionHelper->install('ZikulaPermissionsModule');
            case 'groups':
                return $this->coreInstallerExtensionHelper->install('ZikulaGroupsModule');
            case 'blocks':
                return $this->coreInstallerExtensionHelper->install('ZikulaBlocksModule');
            case 'users':
                return $this->coreInstallerExtensionHelper->install('ZikulaUsersModule');
            case 'zauth':
                return $this->coreInstallerExtensionHelper->install('ZikulaZAuthModule');
            case 'security':
                return $this->coreInstallerExtensionHelper->install('ZikulaSecurityCenterModule');
            case 'categories':
                return $this->coreInstallerExtensionHelper->install('ZikulaCategoriesModule');
            case 'mailer':
                return $this->coreInstallerExtensionHelper->install('ZikulaMailerModule');
            case 'search':
                return $this->coreInstallerExtensionHelper->install('ZikulaSearchModule');
            case 'routes':
                return $this->coreInstallerExtensionHelper->install('ZikulaRoutesModule');
            case 'menu':
                return $this->coreInstallerExtensionHelper->install('ZikulaMenuModule');
            case 'createadmin':
                return $this->superUserHelper->createAdmin();
            case 'loginadmin':
                return $this->superUserHelper->loginAdmin();
            case 'activateextensions':
                return $this->coreInstallerExtensionHelper->reSyncAndActivate();
            case 'categorize':
                return $this->adminCategoryHelper->categorize();
            case 'createblocks':
                return $this->blockHelper->createBlocks();
            case 'finalizeparameters':
                return $this->parameterHelper->finalizeParameters();
            case 'installassets':
                return $this->extensionHelper->installAssets();
            case 'upgrade_event':
                return $this->dispatchEvent(new CoreUpgradePreExtensionUpgrade($this->installed));
            case 'upgradeextensions':
                return $this->coreInstallerExtensionHelper->upgrade();
            case 'versionupgrade':
                return $this->coreInstallerExtensionHelper->executeCoreMetaUpgrade($this->installed);
        }

        return true;
    }

    /**
     * This is used only by CLI commands to execute the stages needed for install/upgrade
     */
    public function handleAjaxStage(StageInterface $ajaxStage, StyleInterface $io, bool $isInteractive = true)
    {
        $stages = $ajaxStage->getTemplateParams();
        foreach ($stages['stages'] as $key => $stage) {
            if ($isInteractive) {
                $io->text($stage[AjaxStageInterface::PRE]);
                $io->text('<fg=blue;options=bold>' . $stage[AjaxStageInterface::DURING] . '</fg=blue;options=bold>');
            }
            $isSuccessful = $this->executeStage($stage[AjaxStageInterface::NAME]);
            if ($isInteractive && $isSuccessful) {
                $io->success($stage[AjaxStageInterface::SUCCESS]);
            } elseif ($isInteractive && !$isSuccessful) {
                $io->error($stage[AjaxStageInterface::FAIL]);
            }
        }
    }

    private function createBundles(): bool
    {
        $this->bundlesSchemaHelper->load();
        $bundles = [];
        $this->persistedBundleHelper->getPersistedBundles($this->kernel, $bundles); // adds autoloaders

        return true;
    }

    private function dispatchEvent(CoreInstallerBundleEvent $event): bool
    {
        $this->eventDispatcher->dispatch($event);
        if ($event->isPropagationStopped()) {
            return false;
        }

        return true;
    }
}
