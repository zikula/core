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

namespace Zikula\ThemeModule\Api;

use ModUtil;
use System;

/**
 * Account profile additions from the theme module
 */
class AccountApi extends \Zikula_AbstractApi
{
    /**
     * Return an array of items to show in the your account panel.
     *
     * @param array $args The arguments to pass to the function.
     *
     * @return array indexed array of items
     */
    public function getall($args)
    {
        $items = array();

        // check if theme switching is allowed
        if (System::getVar('theme_change')) {
            // create an array of links to return
            $items['0'] = array('url' => $this->get('router')->generate('zikulathememodule_user_index'),
                    'module' => 'ZikulaThemeModule',
                    'title' => $this->__('Theme switcher'),
                    'icon' => 'admin.png');
        }

        // Return the items
        return $items;
    }
}
