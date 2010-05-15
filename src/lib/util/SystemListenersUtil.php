<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class SystemListenersUtil
{
    public static function init(Event $event)
    {
        if ($event['stages'] && PN_CORE_SESSIONS) {
            // If enabled and logged in, save login name of user in Apache session variable for Apache logs
            if (isset($GLOBALS['ZConfig']['Log']['log_apache_uname']) && pnUserLoggedIn()) {
                if (function_exists('apache_setenv')) {
                    apache_setenv('Zikula-Username', pnUserGetVar('uname'));
                }
            }
        }
    }

    public static function loadCustomListeners(Event $event)
    {
        if (!$event->hasArg('listeners')) {
            throw new InvalidArgumentException(sprintf('Invalid event call, must have argument "listeners", array of $name => $handler in %s', $event->getName));
        }

        foreach ($event['listeners'] as $listener) {
            if (!array_key_exists('name', $listener) || !array_key_exists('handler', $listener)) {
                throw new InvalidArgumentException('Listener definition array must have key name and handler.');
            }
            EventManagerUtil::attach($listener['name'], $listener['handler']);
        }
    }

    public static function systemHooks(Event $event)
    {
        if (!defined('_ZINSTALLVER')) {
            // call system init hooks
            $systeminithooks = FormUtil::getPassedValue('systeminithooks', 'yes', 'GETPOST');
            if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN) && (isset($systeminithooks) && $systeminithooks == 'no')) {
                // omit system hooks if requested by an administrator
            } else {
                pnModCallHooks('zikula', 'systeminit', 0, array('module' => 'zikula'));

                // reset the render domain - system init hooks mess the translation domain for the core
                $render = Renderer::getInstance();
                $render->renderDomain = null;
            }
        }
    }
}