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

class Admin_Api_Account extends Zikula_AbstractApi
{
    /**
     * Return an array of items to show in the your account panel.
     *
     * @param array $array The arguments to pass to the function.
     *
     * @return array indexed array of items.
     */
    public function getall($args)
    {
        $items = array();

        if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
            $items['0'] = array('url' => ModUtil::url('Admin', 'admin', 'adminpanel'),
                    'module' => 'Admin',
                    'title' => $this->__('Administration panel'),
                    'icon' => 'admin.png');
        }

        // Return the items
        return $items;
    }
}
