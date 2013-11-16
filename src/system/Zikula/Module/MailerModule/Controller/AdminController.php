<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
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

/**
 * Administrative controllers for the mailer module
 */
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
     *
     * @return void
     */
    public function indexAction()
    {
        // Security check will be done in modifyconfig()
        $this->redirect(ModUtil::url('ZikulaMailerModule', 'admin', 'modifyconfig'));
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
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
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
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