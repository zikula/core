<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Settings
 */

/**
 * Get available admin panel links.
 *
 * @return array array of admin links
 */
function Settings_adminapi_getlinks()
{
    $links = array();

    $domain = ZLanguage::getModuleDomain('settings');
    if (SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => ModUtil::url('Settings', 'admin', 'modifyconfig'), 'text' => __('Main settings'), 'class' => 'z-icon-es-home');
        $links[] = array('url' => ModUtil::url('Settings', 'admin', 'multilingual'), 'text' => __('Localisation settings'), 'class' => 'z-icon-es-world');
        $links[] = array('url' => ModUtil::url('Settings', 'admin', 'errorhandling'), 'text' => __('Error settings'), 'class' => 'z-icon-es-error');
    }

    return $links;
}

/**
 * Clear all compiled and cache directories.
 *
 * This function simply calls the theme and pnrender modules to refresh the entire site.
 *
 * @return boolean true.
 */
function settings_adminapi_clearallcompiledcaches()
{
    Theme::getInstance()->clear_all_cache();
    Theme::getInstance()->clear_compiled();
    Theme::getInstance()->clear_cssjscombinecache();
    Renderer::getInstance()->clear_all_cache();
    Renderer::getInstance()->clear_compiled();
    return true;
}