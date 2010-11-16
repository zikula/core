<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * HooksUI class.
 */
class Modules_HookUI
{
    public static function hookproviders(Zikula_Event $event)
    {
        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'hookproviders' && $subject instanceof Modules_Controller_Admin)) {
            return;
        }

        if (!SecurityUtil::checkPermission("Modules::", '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $view = $subject->getView();
        $currentModule = ModUtil::getName();
        $view->attach('currentmodule', $currentModule);
        
        $event->setData($view->fetch('modules_hookui_providers.tpl'));
        $event->setNotified();
    }

    public static function hooksubscribers(Zikula_Event $event)
    {
        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'hooksubscribers' && $subject instanceof Modules_Controller_Admin)) {
           return;
        }

        if (!SecurityUtil::checkPermission("Modules::", '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $view = $subject->getView();
        $currentModule = ModUtil::getName();
        $view->attach('currentmodule', $currentModule);

        $event->setData($view->fetch('modules_hookui_subscribers.tpl'));
        $event->setNotified();
    }
}
