<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * System listeners util.
 */
class SystemListenersUtil
{
    /**
     * If enabled and logged in, save login name of user in Apache session variable for Apache logs.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public static function sessionLogging(Zikula_Event $event)
    {
        if ($event['stage'] & System::CORE_STAGES_SESSIONS) {
            // If enabled and logged in, save login name of user in Apache session variable for Apache logs
            if (isset($GLOBALS['ZConfig']['Log']['log_apache_uname']) && UserUtil::isLoggedIn()) {
                if (function_exists('apache_setenv')) {
                    apache_setenv('Zikula-Username', UserUtil::getVar('uname'));
                }
            }
        }
    }

    /**
     * Call system hooks.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public static function systemHooks(Zikula_Event $event)
    {
        if (!System::isInstalling()) {
            // call system init hooks
            $systeminithooks = FormUtil::getPassedValue('systeminithooks', 'yes', 'GETPOST');
            if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN) && (isset($systeminithooks) && $systeminithooks == 'no')) {
                // omit system hooks if requested by an administrator
            } else {
                ModUtil::callHooks('zikula', 'systeminit', 0, array('module' => 'zikula'));
            }
        }
    }

    /**
     * Load system plugins.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public static function systemPlugins(Zikula_Event $event)
    {
        if ($event['stage'] & System::CORE_STAGES_LANGS) {
            if (!System::isInstalling()) {
                PluginUtil::loadPlugins(realpath(dirname(__FILE__) . "/../../plugins"), "SystemPlugin");
            }
        }
    }

    /**
     * Setup default error reporting.
     *
     * @param Zikula_Event $event The event.
     *
     * @return void
     */
    public static function defaultErrorReporting(Zikula_Event $event)
    {
        $sm = ServiceUtil::getManager();
        if ($sm->hasService('system.errorreporting')) {
            $sm->detachService('system.errorreporting');
        }

        if ($event['stage'] & System::CORE_STAGES_AJAX) {
            $handlerMethod = 'ajaxHandler';
        } else {
            $handlerMethod = 'standardHandler';
        }
        
        $errorHandler = new Zikula_ErrorHandler($sm, EventUtil::getManager());
        $sm->attachService('system.errorreporting', $errorHandler);
        set_error_handler(array($errorHandler, $handlerMethod));
        $event->setNotified();
    }
}
