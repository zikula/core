<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class SystemListenersUtil
{
    public static function sessionLogging(Zikula_Event $event)
    {
        if ($event['stage'] == System::CORE_STAGES_SESSIONS) {
            // If enabled and logged in, save login name of user in Apache session variable for Apache logs
            if (isset($GLOBALS['ZConfig']['Log']['log_apache_uname']) && UserUtil::isLoggedIn()) {
                if (function_exists('apache_setenv')) {
                    apache_setenv('Zikula-Username', UserUtil::getVar('uname'));
                }
            }
        }
    }

    public static function systemHooks(Zikula_Event $event)
    {
        if (!System::isInstalling()) {
            // call system init hooks
            $systeminithooks = FormUtil::getPassedValue('systeminithooks', 'yes', 'GETPOST');
            if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN) && (isset($systeminithooks) && $systeminithooks == 'no')) {
                // omit system hooks if requested by an administrator
            } else {
                ModUtil::callHooks('zikula', 'systeminit', 0, array('module' => 'zikula'));

                // reset the render domain - system init hooks mess the translation domain for the core
                //889 $render = Renderer::getInstance();
                //889 $render->renderDomain = null;
            }
        }
    }

    public static function systemPlugins(Zikula_Event $event)
    {
        if (!System::isInstalling()) {
            PluginUtil::loadPlugins(realpath(dirname(__FILE__) . "/../../plugins"), "SystemPlugin");
        }
    }


}