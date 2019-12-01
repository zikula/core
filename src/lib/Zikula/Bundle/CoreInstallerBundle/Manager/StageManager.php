<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Bundle\Bootstrap as CoreBundleBootstrap;
use Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;

class StageManager
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
     * @var BootstrapHelper
     */
    private $bootstrapHelper;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var BlockManager
     */
    private $blockManager;

    /**
     * @var ParameterManager
     */
    private $parameterManager;

    /**
     * @var SuperUserManager
     */
    private $superUserManager;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        BootstrapHelper $bootstrapHelper,
        ExtensionHelper $extensionHelper,
        EventDispatcherInterface $eventDispatcher,
        ModuleManager $moduleManager,
        BlockManager $blockManager,
        ParameterManager $parameterManager,
        SuperUserManager $superUserManager
    ) {
        $this->kernel = $kernel;
        $this->bootstrapHelper = $bootstrapHelper;
        $this->extensionHelper = $extensionHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->moduleManager = $moduleManager;
        $this->blockManager = $blockManager;
        $this->parameterManager = $parameterManager;
        $this->superUserManager = $superUserManager;
    }

    public function executeStage($stageName): bool
    {
        switch ($stageName) {
            case 'bundles':
                return $this->createBundles();
            case 'install_event':
                return $this->fireEvent(CoreEvents::CORE_INSTALL_PRE_MODULE);
            case 'extensions':
                return $this->moduleManager->installModule('ZikulaExtensionsModule');
            case 'settings':
                return $this->moduleManager->installModule('ZikulaSettingsModule');
            case 'theme':
                return $this->moduleManager->installModule('ZikulaThemeModule');
            case 'admin':
                return $this->moduleManager->installModule('ZikulaAdminModule');
            case 'permissions':
                return $this->moduleManager->installModule('ZikulaPermissionsModule');
            case 'groups':
                return $this->moduleManager->installModule('ZikulaGroupsModule');
            case 'blocks':
                return $this->moduleManager->installModule('ZikulaBlocksModule');
            case 'users':
                return $this->moduleManager->installModule('ZikulaUsersModule');
            case 'zauth':
                return $this->moduleManager->installModule('ZikulaZAuthModule');
            case 'security':
                return $this->moduleManager->installModule('ZikulaSecurityCenterModule');
            case 'categories':
                return $this->moduleManager->installModule('ZikulaCategoriesModule');
            case 'mailer':
                return $this->moduleManager->installModule('ZikulaMailerModule');
            case 'search':
                return $this->moduleManager->installModule('ZikulaSearchModule');
            case 'routes':
                return $this->moduleManager->installModule('ZikulaRoutesModule');
            case 'menu':
                return $this->moduleManager->installModule('ZikulaMenuModule');
            case 'updateadmin':
                return $this->superUserManager->updateAdmin();
            case 'loginadmin':
                return $this->superUserManager->loginAdmin();
            case 'activatemodules':
                return $this->moduleManager->reSyncAndActivateModules();
            case 'categorize':
                return $this->moduleManager->categorizeModules();
            case 'createblocks':
                return $this->blockManager->createBlocks();
            case 'finalizeparameters':
                return $this->parameterManager->finalizeParameters();
            case 'installassets':
                return $this->extensionHelper->installAssets();
            case 'protect':
                return $this->parameterManager->protectFiles();
        }

        return true;
    }

    private function createBundles(): bool
    {
        $this->bootstrapHelper->createSchema();
        $this->bootstrapHelper->load();
        $boot = new CoreBundleBootstrap();
        $bundles = [];
        $boot->getPersistedBundles($this->kernel, $bundles);

        return true;
    }

    private function fireEvent(string $eventName, array $args = []): bool
    {
        $event = new GenericEvent();
        $event->setArguments($args);
        $this->eventDispatcher->dispatch($event);
        if ($event->isPropagationStopped()) {
            return false;
        }

        return true;
    }
}
