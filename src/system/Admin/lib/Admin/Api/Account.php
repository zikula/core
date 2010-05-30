<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.com
 * @version $Id: pnadmin.php 23539 2008-01-14 18:30:48Z landseer $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Admin
 */

class Admin_Api_Account extends AbstractApi
{
    /**
     * Return an array of items to show in the your account panel.
     *
     * @param array $array The arguments to pass to the function.
     *
     * @return   array   indexed array of items.
     */
    function getall($args)
    {
        $items = array();

        if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
            $items['0'] = array('url' => ModUtil::url('Admin', 'admin', 'adminpanel'),
                    'module' => 'core',
                    'set' => 'icons/large',
                    'title' => $this->__('Site admin panel'),
                    'icon' => 'package_settings.gif');
        }

        // Return the items
        return $items;
    }
}