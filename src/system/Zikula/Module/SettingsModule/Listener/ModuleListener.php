<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\SettingsModule\Listener;

use LogUtil;
use ModUtil;
use System;
use Zikula_Event;
use ZLanguage;

/**
 * EventHandlers class.
 */
class ModuleListener
{
    /**
     * Handle module deactivated event "installer.module.deactivated".
     * Receives $modinfo as $args
     *
     * @param Zikula_Event $event
     *
     * @return void
     */
    public static function moduleDeactivated(Zikula_Event $event)
    {
        $modname = $event['name'];
        $dom = ZLanguage::getModuleDomain('ZikulaSettingsModule');

        if ($modname == System::getVar('startpage')) {
            ModUtil::apiFunc('ZikulaSettingsModule', 'admin', 'resetStartModule');
            LogUtil::registerStatus(__('The start module was resetted to a static frontpage.', $dom));
        }
    }
}
