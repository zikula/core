<?php
/**
 * Copyright 2016 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;

/**
 * Event handler to call legacy module installer events using Core-2.0 events.
 * @deprecated remove at Core-2.0
 */
class LegacyModuleInstallerListeners extends Zikula_AbstractEventHandler
{
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition(CoreEvents::MODULE_DISABLE, 'dispatchModuleDeactivated');
        $this->addHandlerDefinition(CoreEvents::MODULE_ENABLE, 'dispatchModuleActivated');
        $this->addHandlerDefinition(CoreEvents::MODULE_INSTALL, 'dispatchModuleInstalled');
        $this->addHandlerDefinition(CoreEvents::MODULE_UPGRADE, 'dispatchModuleUpgraded');
        $this->addHandlerDefinition(CoreEvents::MODULE_REMOVE, 'dispatchModuleRemoved');
    }

    public function dispatchModuleDeactivated(ModuleStateEvent $event)
    {
        $event = new GenericEvent(null, $this->getInfoFromEvent($event));
        $this->eventManager->dispatch('installer.module.deactivated', $event);
    }

    public function dispatchModuleActivated(ModuleStateEvent $event)
    {
        $event = new GenericEvent(null, $this->getInfoFromEvent($event));
        $this->eventManager->dispatch('installer.module.activated', $event);
    }

    public function dispatchModuleInstalled(ModuleStateEvent $event)
    {
        $event = new GenericEvent(null, $this->getInfoFromEvent($event));
        $this->eventManager->dispatch('installer.module.installed', $event);
    }

    public function dispatchModuleUpgraded(ModuleStateEvent $event)
    {
        $event = new GenericEvent(null, $this->getInfoFromEvent($event));
        $this->eventManager->dispatch('installer.module.upgraded', $event);
    }

    public function dispatchModuleRemoved(ModuleStateEvent $event)
    {
        $event = new GenericEvent(null, $this->getInfoFromEvent($event));
        $this->eventManager->dispatch('installer.module.uninstalled', $event);
    }

    private function getInfoFromEvent(ModuleStateEvent $event)
    {
        $moduleBundle = $event->getModule();
        $modInfo = $event->getModInfo();
        if (!empty($moduleBundle) && empty($modInfo)) {
            $info = \ServiceUtil::get('zikula_extensions_module.extension_repository')->get($moduleBundle->getName())->toArray();
        } elseif (!empty($modInfo)) {
            $info = $modInfo;
        } else {
            $info = [];
        }

        return $info;
    }
}
