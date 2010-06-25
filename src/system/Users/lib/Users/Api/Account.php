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
 * The Account API provides links for modules on the "user account page"; this
 * class provides those links for the Users module.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Api_Account extends Zikula_Api
{
    /**
     * Return an array of items to show in the the user's account panel.
     *
     * @param mixed $args Not used.
     *
     * @return array Indexed array of items.
     */
    public function getAll($args)
    {
        $items = array();

        $modvars = $this->getVars();

        if ($modvars['changepassword'] == 1) {
            // show edit password link
            $items['1'] = array('url' => ModUtil::url('Users', 'user', 'changePassword'),
                                'module' => 'core',
                                'set' => 'icons/large',
                                'title' => $this->__('Password changer'),
                                'icon' => 'password.gif');
        }

        if ($modvars['changeemail'] == 1) {
            // show edit email link
            $items['2'] = array('url' => ModUtil::url('Users', 'user', 'changeEmail'),
                                'module' => 'Users',
                                'title' => $this->__('E-mail address manager'),
                                'icon' => 'changemail.gif');
        }

        // check if the users block exists
        $blocks = ModUtil::apiFunc('Blocks', 'user', 'getAll');
        $mid = ModUtil::getIdFromName('Users');
        $found = false;
        foreach ($blocks as $block) {
            if ($block['mid'] == $mid && $block['bkey'] == 'user') {
                $found = true;
                break;
            }
        }

        if ($found) {
            $items['3'] = array('url' => ModUtil::url('Users', 'user', 'usersBlock'),
                                'module' => 'core',
                                'set' => 'icons/large',
                                'title' => $this->__('Personal custom block'),
                                'icon' => 'folder_home.gif');
        }

        if (System::getVar('multilingual')) {
            $items['4'] = array('url' => ModUtil::url('Users', 'user', 'changeLang'),
                                'module' => 'core',
                                'set' => 'icons/large',
                                'title' => $this->__('Language switcher'),
                                'icon' => 'fonts.gif');
        }

        $items['5'] = array('url' => ModUtil::url('Users', 'user', 'logout'),
                            'module' => 'core',
                            'set' => 'icons/large',
                            'title' => $this->__('Log out'),
                            'icon' => 'exit.gif');

        // Return the items
        return $items;
    }
}
