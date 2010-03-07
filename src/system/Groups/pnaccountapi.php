<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 * @license http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Return an array of items to show in the your account panel
 *
 * @return   array   indexed array of items
 */
function Groups_accountapi_getall($args)
{
    $items = array();

    // Check if there is at least one group to show
    $result = pnModAPIFunc('Groups', 'user', 'getallgroups', array());

    if ($result <> false) {
        // create an array of links to return
        $items['0'] = array('url'    => pnModURL('Groups', 'user'),
                            'module' => 'Groups',
                            'title'  => __('Groups manager'),
                            'icon'   => 'admin.gif');
    }

    // Return the items
    return $items;
}
