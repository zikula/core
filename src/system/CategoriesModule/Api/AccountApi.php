<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Api;

use System;
use SessionUtil;
use SecurityUtil;

/**
 * Account profile additions from the categories module
 */
class AccountApi extends \Zikula_AbstractApi
{
    /**
     * Return an array of items to show in the your account panel.
     *
     * @return array indexed array of items
     */
    public function getall()
    {
        $items = array();

        // Create an array of links to return
        if (SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT) && $this->getVar('allowusercatedit')) {
            $referer = System::serverGetVar('HTTP_REFERER');
            if (strpos($referer, 'module=ZikulaCategoriesModule') === false) {
                //$this->request->getSession()->set('categories_referer', $referer);
                SessionUtil::setVar('categories_referer', $referer);
            }
            $items['0'] = array(
                'url' => $this->get('router')->generate('zikulacategoriesmodule_user_edituser'),
                'module' => 'ZikulaCategoriesModule',
                'title' => $this->__('Categories manager'),
                'icon' => 'admin.png');
        }

        // Return the items
        return $items;
    }
}
