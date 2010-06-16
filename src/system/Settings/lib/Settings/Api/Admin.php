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

class Settings_Api_Admin extends Zikula_Api
{
    /**
     * Get available admin panel links.
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        $domain = ZLanguage::getModuleDomain('settings');
        if (SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Settings', 'admin', 'modifyconfig'), 'text' => $this->__('Main settings'), 'class' => 'z-icon-es-home');
            $links[] = array('url' => ModUtil::url('Settings', 'admin', 'multilingual'), 'text' => $this->__('Localisation settings'), 'class' => 'z-icon-es-world');
            $links[] = array('url' => ModUtil::url('Settings', 'admin', 'errorhandling'), 'text' => $this->__('Error settings'), 'class' => 'z-icon-es-error');
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
        Theme::getInstance()->clear_all_cache();
        Theme::getInstance()->clear_compiled();
        Theme::getInstance()->clear_cssjscombinecache();
        Renderer::getInstance()->clear_all_cache();
        Renderer::getInstance()->clear_compiled();
        return true;
    }
}
