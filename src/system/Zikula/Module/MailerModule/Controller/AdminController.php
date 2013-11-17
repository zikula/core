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

namespace Zikula\Module\MailerModule\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Zikula_View;
use ModUtil;
use SecurityUtil;
use FormUtil;
use Zikula\Module\MailerModule\Form\Handler\ModifyConfigHandler;
use Zikula\Module\MailerModule\Form\Handler\TestConfigHandler;

class AdminController extends \Zikula_AbstractController
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
    public function indexAction()
    {
        // Security check will be done in modifyconfig()
        $this->redirect(ModUtil::url('ZikulaMailerModule', 'admin', 'modifyconfig'));
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     * @return string HTML string
     */
    public function modifyconfigAction()
    {
        // security check
        if (!SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $form = FormUtil::newForm('ZikulaMailerModule', $this);

        return $form->execute('Admin/modifyconfig.tpl', new ModifyConfigHandler());
    }

    /**
     * This function displays a form to sent a test mail
     * @return string HTML string
     */
    public function testconfigAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $form = FormUtil::newForm('ZikulaMailerModule', $this);

        return $form->execute('Admin/testconfig.tpl', new TestConfigHandler());
    }
}