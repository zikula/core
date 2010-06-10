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


class SecurityCenter_Api_Admin extends Zikula_Api
{
    /**
     * delete a hacking attempt item
     * @param int $args['hid'] ID of the item
     * @return bool true on success, false on failure
     */
    public function delete($args)
    {
        // Argument check
        if ( !isset($args['hid']) || !is_numeric($args['hid']) ) {
            return LogUtil::registerArgsError();
        }

        // get the existing item
        $item = ModUtil::apiFunc('SecurityCenter', 'user', 'get', array('hid' => $args['hid']));

        if (!$item) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('SecurityCenter::', "$item[hid]::$item[hacktime]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        if (!DBUtil::deleteObjectByID('sc_anticracker', $args['hid'], 'hid')) {
            return LogUtil::registerError($this->__('Error! Could not perform the deletion.'));
        }

        // Let any hooks know that we have deleted an item.
        ModUtil::callHooks('item', 'delete', $args['hid'], array('module' => 'SecurityCenter'));

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * get available admin panel links
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'view'), 'text' => $this->__('Hacking attempts list'), 'class' => 'z-icon-es-list');
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'viewobj', array('ot' => 'log_event')), 'text' => $this->__('Logged events list'), 'class' => 'z-icon-es-list');
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'viewobj', array('ot' => 'intrusion')), 'text' => $this->__('View IDS Log'), 'class' => 'z-icon-es-locked');

            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'allowedhtml'), 'text' => $this->__('Allowed HTML settings'), 'class' => 'z-icon-es-config');

            $outputfilter = System::getVar('outputfilter');
            if ($outputfilter == 1) {
                $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'purifierconfig'), 'text' => $this->__('HTMLPurifier settings'), 'class' => 'z-icon-es-config');
            }
        }

        return $links;
    }
}