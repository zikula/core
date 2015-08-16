<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\AdminModule\Api;

use ModUtil;
use SecurityUtil;

/**
 * Account profile additions from the admin module
 */
class AccountApi extends \Zikula_AbstractApi
{
    /**
     * Return an array of items to show in the your account panel.
     *
     * @return array indexed array of items.
     */
    public function getall()
    {
        $items = array();

        if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
            $items['0'] = array(
                'url' => $this->get('router')->generate('zikulaadminmodule_admin_adminpanel'),
                'module' => 'ZikulaAdminModule',
                'title' => $this->__('Administration panel'),
                'icon' => 'admin.png');
        }

        // Return the items
        return $items;
    }
}
