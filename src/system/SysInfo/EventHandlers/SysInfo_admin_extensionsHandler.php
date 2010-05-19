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
 * MultiHook Event Handler class.
 */
class SysInfo_admin_extensionsHandler extends CustomEventHandler
{
    /**
     * Array of event names for this handler (usually just one).
     *
     * @var array
     */
    protected $names = array('controller.method_not_found');

    /**
     * Event handler here.
     *
     * @param Event $event
     */
    public function handler(Event $event)
    {
        /**
         * Show version information for installed Zikula modules
         * @return string HTML output string
         */

        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'extensions' && $subject instanceof SysInfo_admin)) {
            return;
        }

        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Zikula Modules and Themes versions
        $pnRender = Renderer::getInstance('SysInfo');
        Loader::loadClass('ModuleUtil');
        $pnRender->assign('mods', ModuleUtil::getModules());
        $pnRender->assign('themes', ThemeUtil::getAllThemes());


        $event->setData($pnRender->fetch('sysinfo_admin_extensions.htm'));
        $event->setNotified();
    }
}