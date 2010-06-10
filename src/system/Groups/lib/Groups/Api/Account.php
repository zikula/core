<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 * @license http://www.gnu.org/copyleft/gpl.html
 */

class Groups_Api_Account extends Zikula_Api
{
    /**
     * Return an array of items to show in the your account panel
     *
     * @return   array   indexed array of items
     */
    function getall($args)
    {
        $items = array();

        // Check if there is at least one group to show
        $result = ModUtil::apiFunc('Groups', 'user', 'getallgroups', array());

        if ($result <> false) {
            // create an array of links to return
            $items['0'] = array('url'    => ModUtil::url('Groups', 'user'),
                    'module' => 'Groups',
                    'title'  => $this->__('Groups manager'),
                    'icon'   => 'admin.gif');
        }

        // Return the items
        return $items;
    }
}