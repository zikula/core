<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Api;

use ModUtil;

/**
 * Account profile additions from the admin module
 */
class AccountApi extends \Zikula_AbstractApi
{
    /**
     * Return an array of items to show in the your account panel
     *
     * @return array indexed array of items
     */
    public function getall()
    {
        $items = [];

        // Check if there is at least one group to show
        $groups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getallgroups');

        if ($groups) {
            // create an array of links to return
            $items['0'] = [
                'url'    => ModUtil::url('ZikulaGroupsModule', 'user', 'index'),
                'module' => 'ZikulaGroupsModule',
                'title'  => $this->__('Groups manager'),
                'icon'   => 'admin.png'
            ];
        }

        // Return the items
        return $items;
    }
}
