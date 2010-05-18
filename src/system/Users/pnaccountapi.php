<?php
/**
 * Zikula Application Framework
 *
 * @copyright 2001 Zikula Development Team
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 * @version $Id$
 * @link http://www.zikula.org
 */

/**
 * Return an array of items to show in the the user's account panel.
 *
 * @param mixed $args Not used.
 *
 * @return array Indexed array of items.
 */
function Users_accountapi_getall($args)
{
    $items = array();

    $modvars = ModUtil::getVar('Users');

    if ($modvars['changepassword'] == 1) {
        // show edit password link
        $items['1'] = array('url' => ModUtil::url('Users', 'user', 'changepassword'),
                            'module' => 'core',
                            'set' => 'icons/large',
                            'title' => __('Password changer'),
                            'icon' => 'password.gif');
    }

    if ($modvars['changeemail'] == 1) {
        // show edit email link
        $items['2'] = array('url' => ModUtil::url('Users', 'user', 'changeemail'),
                            'module' => 'Users',
                            'title' => __('E-mail address manager'),
                            'icon' => 'changemail.gif');
    }

    // check if the users block exists
    $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall');
    $mid = ModUtil::getIdFromName('Users');
    $found = false;
    foreach ($blocks as $block) {
        if ($block['mid'] == $mid && $block['bkey'] == 'user') {
            $found = true;
            break;
        }
    }

    if ($found) {
        $items['3'] = array('url' => ModUtil::url('Users', 'user', 'usersblock'),
                            'module' => 'core',
                            'set' => 'icons/large',
                            'title' => __('Personal custom block'),
                            'icon' => 'folder_home.gif');
    }

    if (pnConfigGetVar('multilingual')) {
        $items['4'] = array('url' => ModUtil::url('Users', 'user', 'changelang'),
                            'module' => 'core',
                            'set' => 'icons/large',
                            'title' => __('Language switcher'),
                            'icon' => 'fonts.gif');
    }

    $items['5'] = array('url' => ModUtil::url('Users', 'user', 'logout'),
                        'module' => 'core',
                        'set' => 'icons/large',
                        'title' => __('Log out'),
                        'icon' => 'exit.gif');

    // Return the items
    return $items;
}
