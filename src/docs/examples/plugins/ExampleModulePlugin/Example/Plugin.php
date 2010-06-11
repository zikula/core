<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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


class ModulePlugin_SysInfo_Example_Plugin extends Zikula_Plugin
{
    protected $version = '1.0.0';

    protected $eventNames = array('module.postexecute'          => 'addLinks',
                                  'controller.method_not_found' => 'anotherfunction');

    public function preInitialize()
    {
        $this->domain = ZLanguage::bindModulePluginDomain('SysInfo', 'Example');
    }

    /**
     * Event handler here.
     *
     * @param Event $event
     */
    public function addLinks(Zikula_Event $event)
    {
        // check if this is for this handler
        if (!($event->getSubject() instanceof SysInfo_Api_Admin && $event['modfunc'][1] == 'getlinks')) {
            return;
        }

        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $event->data[] = array('url' => ModUtil::url('SysInfo', 'admin', 'anotherfunction'), 'text' => $this->__('Here is another link'));
        }
    }

    /**
     * 'anotherfunction' Event handler .
     *
     * @param Event $event
     */
    public function anotherfunction(Zikula_Event $event)
    {
        /**
         * Show version information for installed Zikula modules
         * @return string HTML output string
         */

        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'anotherfunction' && $subject instanceof SysInfo_admin)) {
            return;
        }

        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Zikula Modules and Themes versions
        $view = Renderer::getModulePluginInstance('SysInfo', 'Example');

        $event->setData($view->fetch('anotherfunction.htm'));
        $event->setNotified();
    }
}
