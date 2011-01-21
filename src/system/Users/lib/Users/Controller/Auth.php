<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Access to user-initiated authentication actions for the Users module.
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

    /**
     * Renders the template that displays the input fields for the authmodule in the Users module's login block.
     *
     * @return string The rendered template.
     */
    public function loginBlockFields()
    {
        return $this->view->assign('loginviaoption', ModUtil::getVar('Users', 'loginviaoption', 0))
                            ->fetch('users_auth_loginblockfields.tpl');
    }

    /**
     * Renders the template that displays the authmodule's icon in the Users module's login block.
     *
     * @return string The rendered template.
     */
    public function loginBlockIcon()
    {
        $loginViaOption = $this->getVar('loginviaoption', 0);
        return $this->view
                ->assign('loginviaoption', $loginViaOption)
                ->fetch('users_auth_loginblockicon.tpl');
    }

}
