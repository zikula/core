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
 * get available admin panel links
 *
 * @return array array of admin links
 */
function Settings_adminapi_getlinks()
{
    $links = array();

    $domain = ZLanguage::getModuleDomain('settings');
    if (SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('Settings', 'admin', 'modifyconfig'), 'text' => __('Owner settings'), 'class' => 'z-icon-es-home');
        $links[] = array('url' => pnModURL('Settings', 'admin', 'multilingual'), 'text' => __('Localisation settings'), 'class' => 'z-icon-es-world');
        $links[] = array('url' => pnModURL('Settings', 'admin', 'errorhandling'), 'text' => __('Error settings'), 'class' => 'z-icon-es-error');
    }

    return $links;
}

/**
 * clear all compiled and cache directories
 *
 * This function simply calls the theme and pnrender modules to refresh the entire site
 */
function settings_adminapi_clearallcompiledcaches()
{
    pnModAPIFunc('pnRender', 'user', 'clear_compiled');
    pnModAPIFunc('pnRender', 'user', 'clear_cache');
    pnModAPIFunc('Theme', 'user', 'clear_compiled');
    pnModAPIFunc('Theme', 'user', 'clear_cache');
    return true;
}
