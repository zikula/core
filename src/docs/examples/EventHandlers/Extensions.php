<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Add a method to the SysInfo module using Method not found Event Handler class.
 */
class SysInfo_EventHandlers_Extensions extends Zikula_EventHandler
{
    /**
     * Array of event names for this handler (usually just one).
     *
     * @var array
     */
    protected $eventNames = array('controller.method_not_found' => 'handler');

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
        if (!($event['method'] == 'extensions' && $subject instanceof SysInfo_admin)) {
            return;
        }

        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Zikula Modules and Themes versions
        $renderer = Renderer::getInstance('SysInfo');
        $renderer->assign('mods', ModuleUtil::getModules());
        $renderer->assign('themes', ThemeUtil::getAllThemes());

        $event->setData($renderer->fetch('sysinfo_admin_extensions.tpl'));
        $event->setNotified();
    }
}