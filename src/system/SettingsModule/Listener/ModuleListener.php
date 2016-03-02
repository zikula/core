<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
  *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SettingsModule\Listener;

use LogUtil;
use ModUtil;
use System;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ModuleListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'installer.module.deactivated' => array('moduleDeactivated'),
        );
    }

    /**
     * Handle module deactivated event "installer.module.deactivated".
     * Receives $modinfo as $args
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public static function moduleDeactivated(GenericEvent $event)
    {
        $modname = $event['name'];

        if ($modname == System::getVar('startpage')) {
            ModUtil::apiFunc('ZikulaSettingsModule', 'admin', 'resetStartModule');
            LogUtil::registerStatus(__('The start module was reset to a static frontpage.'));
        }
    }
}
