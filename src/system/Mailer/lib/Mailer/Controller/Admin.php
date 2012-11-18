<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Mailer_Controller_Admin extends Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * the main administration function
     * This function is the default function, and is called whenever the
     * module is initiated without defining arguments.  As such it can
     * be used for a number of things, but most commonly it either just
     * shows the module menu and returns or calls whatever the module
     * designer feels should be the default function (often this is the
     * view() function)
     * @return string HTML string
     */
    public function main()
    {
        // Security check will be done in modifyconfig()
        $this->redirect(ModUtil::url('Mailer', 'admin', 'modifyconfig'));
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     * @return string HTML string
     */
    public function modifyconfig()
    {
        // security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN));

        $form = FormUtil::newForm('Mailer', $this);

        return $form->execute('mailer_admin_modifyconfig.tpl', new Mailer_Form_Handler_ModifyConfig());
    }

    /**
     * This function displays a form to sent a test mail
     * @return string HTML string
     */
    public function testconfig()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN));

        $form = FormUtil::newForm('Mailer', $this);

        return $form->execute('mailer_admin_testconfig.tpl', new Mailer_Form_Handler_TestConfig());
    }
}
