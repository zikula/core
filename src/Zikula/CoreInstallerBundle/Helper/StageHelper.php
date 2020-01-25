<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\CoreEvents;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\Bundle\CoreBundle\Helper\BundlesSchemaHelper;
use Zikula\Bundle\CoreBundle\Helper\PersistedBundleHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
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
     * @var BundlesSchemaHelper
     */
    private $bundlesSchemaHelper;

    /**
     * @var ModuleHelper
     */
    private $moduleHelper;

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
     * @var CacheHelper
     */
    private $cacheHelper;

    /**
     * @var ThemeHelper
     */
    private $themeHelper;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        BundlesSchemaHelper $bundlesSchemaHelper,
        ExtensionHelper $extensionHelper,
        EventDispatcherInterface $eventDispatcher,
        ModuleHelper $moduleHelper,
        BlockHelper $blockHelper,
        ParameterHelper $parameterHelper,
        SuperUserHelper $superUserHelper,
        CacheHelper $cacheHelper,
        ThemeHelper $themeHelper
    ) {
        $this->kernel = $kernel;
        $this->bundlesSchemaHelper = $bundlesSchemaHelper;
        $this->extensionHelper = $extensionHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->moduleHelper = $moduleHelper;
        $this->blockHelper = $blockHelper;
        $this->parameterHelper = $parameterHelper;
        $this->superUserHelper = $superUserHelper;
        $this->cacheHelper = $cacheHelper;
        $this->themeHelper = $themeHelper;
    }

    /**
     * Specific stages are assigned in Ajax(Installer|Upgrader)Stage
     *
     * @return bool
     * @throws \Exception
     */
    public function executeStage(string $stageName): bool
    {
        $currentVersion = $this->parameterHelper->getYamlHelper()->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
        switch ($stageName) {
            case 'bundles':
                return $this->createBundles();
            case 'install_event':
                return $this->fireEvent(CoreEvents::CORE_INSTALL_PRE_MODULE);
            case 'extensions':
                return $this->moduleHelper->installModule('ZikulaExtensionsModule');
            case 'settings':
                return $this->moduleHelper->installModule('ZikulaSettingsModule');
            case 'theme':
                return $this->moduleHelper->installModule('ZikulaThemeModule');
            case 'admin':
                return $this->moduleHelper->installModule('ZikulaAdminModule');
            case 'permissions':
                return $this->moduleHelper->installModule('ZikulaPermissionsModule');
            case 'groups':
                return $this->moduleHelper->installModule('ZikulaGroupsModule');
            case 'blocks':
                return $this->moduleHelper->installModule('ZikulaBlocksModule');
            case 'users':
                return $this->moduleHelper->installModule('ZikulaUsersModule');
            case 'zauth':
                return $this->moduleHelper->installModule('ZikulaZAuthModule');
            case 'security':
                return $this->moduleHelper->installModule('ZikulaSecurityCenterModule');
            case 'categories':
                return $this->moduleHelper->installModule('ZikulaCategoriesModule');
            case 'mailer':
                return $this->moduleHelper->installModule('ZikulaMailerModule');
            case 'search':
                return $this->moduleHelper->installModule('ZikulaSearchModule');
            case 'routes':
                return $this->moduleHelper->installModule('ZikulaRoutesModule');
            case 'menu':
                return $this->moduleHelper->installModule('ZikulaMenuModule');
            case 'updateadmin':
                return $this->superUserHelper->updateAdmin();
            case 'loginadmin':
                return $this->superUserHelper->loginAdmin();
            case 'activatemodules':
                return $this->moduleHelper->reSyncAndActivateModules();
            case 'categorize':
                return $this->moduleHelper->categorizeModules();
            case 'createblocks':
                return $this->blockHelper->createBlocks();
            case 'finalizeparameters':
                return $this->parameterHelper->finalizeParameters();
            case 'installassets':
                return $this->extensionHelper->installAssets();
            case 'protect':
                return $this->parameterHelper->protectFiles();
            case 'reinitparams':
                return $this->parameterHelper->reInitParameters();
            case 'upgrade_event':
                return $this->fireEvent(CoreEvents::CORE_UPGRADE_PRE_MODULE, ['currentVersion' => $currentVersion]);
            case 'upgrademodules':
                return $this->moduleHelper->upgradeModules();
            case 'regenthemes':
                return $this->themeHelper->regenerateThemes();
            case 'versionupgrade':
                return $this->moduleHelper->executeCoreMetaUpgrade($currentVersion);
            case 'clearcaches':
                return $this->cacheHelper->clearCaches();
        }

        return true;
    }

    /**
     * This is used only by CLI commands to execute the stages needed for install/upgrade
     */
    public function handleAjaxStage(StageInterface $ajaxStage, StyleInterface $io)
    {
        $stages = $ajaxStage->getTemplateParams();
        foreach ($stages['stages'] as $key => $stage) {
            $io->text($stage[AjaxStageInterface::PRE]);
            $io->text('<fg=blue;options=bold>' . $stage[AjaxStageInterface::DURING] . '</fg=blue;options=bold>');
            $status = $this->executeStage($stage[AjaxStageInterface::NAME]);
            if ($status) {
                $io->success($stage[AjaxStageInterface::SUCCESS]);
            } else {
                $io->error($stage[AjaxStageInterface::FAIL]);
            }
        }
    }

    private function createBundles(): bool
    {
        $this->bundlesSchemaHelper->load();
        $bundleHelper = new PersistedBundleHelper();
        $bundles = [];
        $bundleHelper->getPersistedBundles($this->kernel, $bundles); // adds autoloaders

        return true;
    }

    private function fireEvent(string $eventName, array $args = []): bool
    {
        $event = new GenericEvent();
        $event->setArguments($args);
        $this->eventDispatcher->dispatch($event, $eventName);
        if ($event->isPropagationStopped()) {
            return false;
        }

        return true;
    }
}
