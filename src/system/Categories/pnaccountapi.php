<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pnaccountapi.php 22138 2007-06-01 10:19:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Categories
 * @author Mark West
 */

/**
 * Return an array of items to show in the your account panel
 *
 * @return   array   indexed array of items
 */
function categories_accountapi_getall($args)
{
    $items = array();

    // Create an array of links to return
    if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT) && pnModGetVar('Categories', 'allowusercatedit')) {
        $referer = pnServerGetVar('HTTP_REFERER');
        if (strpos($referer, 'module=Categories') === false) {
            SessionUtil::setVar('categories_referer', $referer);
        }
        $items['0'] = array('url'     => pnModURL('Categories', 'user', 'edituser'),
                            'module'  => 'core',
                            'set'     => 'icons/large',
                            'title'   => __('Categories manager'),
                            'icon'    => 'mydocuments.gif');
    }

    // Return the items
    return $items;
}
