<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Theme
 */

/**
 * Return an array of items to show in the your account panel.
 *
 * @return   array   indexed array of items
 */
function Theme_accountapi_getall($args)
{
    $items = array();

    // check if theme switching is allowed
    if (pnConfigGetVar('theme_change')) {
        // create an array of links to return
        $items['0'] = array('url' => pnModURL('Theme', 'user'),
                            'module' => 'core',
                            'set' => 'icons/large',
                            'title' => __('Theme switcher'),
                            'icon' => 'package_graphics.gif');
    }

    // Return the items
    return $items;
}
