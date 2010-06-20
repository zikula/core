<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This is the User controller class providing navigation and interaction functionality.
 */
class ExampleDoctrine_User extends Zikula_Controller
{
    /**
     * This method is the default function.
     *
     * Called whenever the module's Admin area is called without defining arguments.
     *
     * @param array $args Array.
     *
     * @return string|boolean Output.
     */
    public function main($args)
    {
        return $this->view();
    }

    /**
     * This method provides a generic item list overview.
     *
     * @return string|boolean Output.
     */
    public function view()
    {
        if (!SecurityUtil::checkPermission('ExampleDoctrine::', '::', ACCESS_READ)) {
            return LogUtil::registerPermissionError(ModUtil::url('ExampleDoctrine', 'user', 'main'));
        }

        $view = Renderer::getInstance('ExampleDoctrine');

        $users = Doctrine_Core::getTable('ExampleDoctrine_Model_User')->findAll();
        $view->assign('users', $users);

        // fetch and return the appropriate template
        return $view->fetch('exampledoctrine_user_view.htm');
    }

    /**
     * Add record.
     *
     * @return string|boolean Output.
     */
    public function add()
    {
        if (!SecurityUtil::checkPermission('ExampleDoctrine::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(ModUtil::url('ExampleDoctrine', 'user', 'main'));
        }

        $user = new ExampleDoctrine_Model_User();
        $arrayObj = FormUtil::getPassedValue('user', null, 'POST');
        $user->merge($arrayObj);
        $user->save();

        return $this->view();
    }
}