<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Theme
 */

class Theme_Api_Account extends Zikula_Api
{
    /**
     * Return an array of items to show in the your account panel.
     *
     * @param array $array The arguments to pass to the function.
     *
     * @return   array   indexed array of items
     */
    function getall($args)
    {
        $items = array();

        // check if theme switching is allowed
        if (System::getVar('theme_change')) {
            // create an array of links to return
            $items['0'] = array('url' => ModUtil::url('Theme', 'user'),
                    'module' => 'Theme',
                    'title' => $this->__('Theme switcher'),
                    'icon' => 'admin.png');
        }

        // Return the items
        return $items;
    }
}