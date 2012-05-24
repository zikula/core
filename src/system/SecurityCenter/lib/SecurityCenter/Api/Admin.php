<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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


class SecurityCenter_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Purge IDS Log.
     *
     * @param none
     *
     * @return bool true if successful, false otherwise.
     */
    public function purgeidslog($args)
    {
        if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_DELETE)) {
            return false;
        }

        if (!DBUtil::truncateTable('sc_intrusion')) {
                return false;
        }

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
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'allowedhtml'), 'text' => $this->__('Allowed HTML settings'), 'class' => 'z-icon-es-options');
            $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'viewidslog'),
                             'text' => $this->__('View IDS Log'),
                             'class' => 'z-icon-es-log',
                             'links' => array(
                                             array('url' => ModUtil::url('SecurityCenter', 'admin', 'viewidslog'),
                                                   'text' => $this->__('View IDS Log')),
                                             array('url' => ModUtil::url('SecurityCenter', 'admin', 'exportidslog'),
                                                   'text' => $this->__('Export IDS Log')),
                                             array('url' => ModUtil::url('SecurityCenter', 'admin', 'purgeidslog'),
                                                   'text' => $this->__('Purge IDS Log'))
                                               ));

            $outputfilter = System::getVar('outputfilter');
            if ($outputfilter == 1) {
                $links[] = array('url' => ModUtil::url('SecurityCenter', 'admin', 'purifierconfig'), 'text' => $this->__('HTMLPurifier settings'), 'class' => 'z-icon-es-options');
            }
        }

        return $links;
    }
}
