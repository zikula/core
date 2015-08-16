<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\AdminModule\Listener;

use ModUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;

class ModuleEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            CoreEvents::MODULE_INSTALL => array('moduleInstall'),
        );
    }

    /**
     * Handle module install event.
     *
     * @param ModuleStateEvent $event
     *
     * @return void
     */
    public function moduleInstall(ModuleStateEvent $event)
    {
        $module = $event->getModule();
        if ($module) {
            $modName = $module->getName();
        } else {
            // Legacy for non Symfony-styled modules.
            $modInfo = $event->modinfo;
            $modName = $modInfo['name'];
        }

        if (!\System::isInstalling()) {
            $category = ModUtil::getVar('ZikulaAdminModule', 'defaultcategory');
            ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', array('module' => $modName, 'category' => $category));
        }
    }
}
