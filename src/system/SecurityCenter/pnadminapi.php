<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage SecurityCenter
 */

/**
 * delete a hacking attempt item
 * @param int $args['hid'] ID of the item
 * @return bool true on success, false on failure
 */
function securitycenter_adminapi_delete($args)
{
    // Argument check
    if ( !isset($args['hid']) || !is_numeric($args['hid']) ) {
        return LogUtil::registerArgsError();
    }

    // get the existing item
    $item = pnModAPIFunc('SecurityCenter', 'user', 'get', array('hid' => $args['hid']));

    if (!$item) {
        return LogUtil::registerError(__('Sorry! No such item found.'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', "$item[hid]::$item[hacktime]", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    if (!DBUtil::deleteObjectByID('sc_anticracker', $args['hid'], 'hid')) {
        return LogUtil::registerError(__('Error! Could not perform the deletion.'));
    }

    // Let any hooks know that we have deleted an item.
    pnModCallHooks('item', 'delete', $args['hid'], array('module' => 'SecurityCenter'));

    // Let the calling process know that we have finished successfully
    return true;
}

/**
 * get available admin panel links
 * @return array array of admin links
 */
function securitycenter_adminapi_getlinks()
{
    $links = array();

    if (SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_ADMIN)) {
        $args = array();
        $args['ot'] = 'log_event';

        $links[] = array('url' => pnModURL('SecurityCenter', 'admin', 'view'), 'text' => __('Hacking attempts list'));
        $links[] = array('url' => pnModURL('SecurityCenter', 'admin', 'viewobj', $args), 'text' => __('Logged events list'));
        $links[] = array('url' => pnModURL('SecurityCenter', 'admin', 'modifyconfig'), 'text' => __('Settings'));

        $args['ot'] = 'intrusion';
        $links[] = array('url' => pnModURL('SecurityCenter', 'admin', 'viewobj', $args), 'text' => __('View IDS Log'));
        $links[] = array('url' => pnModURL('SecurityCenter', 'admin', 'allowedhtml'), 'text' => __('HTML settings'));
    }

    return $links;
}
