<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 */

/**
 * Controllers provide users access to actions that they can perform on the system;
 * this class provides access to (non-administrative) user-initiated actions for the Users module.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Controller_Auth extends Zikula_Controller
{
    /**
     * Post initialise.
     *
     * Run after construction.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // Set caching to false by default.
        $this->view->setCaching(false);
    }

    public function loginBlockFields()
    {
        return $this->view->assign('loginviaoption', ModUtil::getVar('Users', 'loginviaoption', 0))
            ->fetch('users_auth_loginblockfields.tpl');
    }

    public function loginBlockIcon()
    {
        return $this->view->fetch('users_auth_loginblockicon.tpl');
    }

}
