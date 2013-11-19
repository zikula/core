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

namespace Zikula\Module\SettingsModule\Api;

use ZLanguage;
use ModUtil;
use SecurityUtil;
use System;
use Zikula_View_Theme;
use Zikula_View;

/**
 * API functions used by administrative controllers
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Get available admin panel links.
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaSettingsModule', 'admin', 'modifyconfig'), 'text' => $this->__('Main settings'), 'icon' => 'fa fa-wrench');
            $links[] = array('url' => ModUtil::url('ZikulaSettingsModule', 'admin', 'multilingual'), 'text' => $this->__('Localisation settings'), 'icon' => 'globe');
            $links[] = array('url' => ModUtil::url('ZikulaSettingsModule', 'admin', 'phpinfo'), 'text' => $this->__('PHP configuration'), 'icon' => 'info-circle');
        }

        return $links;
    }

    /**
     * Clear all compiled and cache directories.
     *
     * This function simply calls the theme and renderer modules to refresh the entire site.
     *
     * @return boolean true.
     */
    public function clearallcompiledcaches()
    {
        Zikula_View_Theme::getInstance()->clear_all_cache();
        Zikula_View_Theme::getInstance()->clear_compiled();
        Zikula_View_Theme::getInstance()->clear_cssjscombinecache();
        Zikula_View::getInstance()->clear_all_cache();
        Zikula_View::getInstance()->clear_compiled();

        return true;
    }

    /**
     * Resets the startmodule to static frontpage.
     *
     * @return boolean true.
     */
    public function resetStartModule()
    {
        System::setVar('startpage', '');
        System::setVar('starttype', '');
        System::setVar('startfunc', '');
        System::setVar('startargs', '');

        return true;
    }
}
