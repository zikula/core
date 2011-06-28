<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package ZikulaExamples
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Add a method to the Users module using Method not found Event Handler class.
 */
class Users_EventHandlers_Extensions extends Zikula_AbstractEventHandler
{
    /**
     * Setup handler definitions.
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('controller.method_not_found', 'handler');
    }

    /**
     * Event handler here.
     *
     * @param Zikula_Event $event Event handler.
     *
     * @return void
     */
    public function handler(Zikula_Event $event)
    {
        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'extensions' && $subject instanceof Users_Controller_Admin)) {
            return;
        }

        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Zikula Modules and Themes versions
        $view = Zikula_View::getInstance('Users');
        $view->assign('mods', ModuleUtil::getModules());
        $view->assign('themes', ThemeUtil::getAllThemes());

        $event->setData($view->fetch('users_admin_extensions.tpl'));
        $event->stop();
    }
}