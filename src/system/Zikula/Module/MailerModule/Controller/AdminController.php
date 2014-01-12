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

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula_View;
use ModUtil;
use SecurityUtil;
use FormUtil;
use System;
use Zikula\Module\MailerModule\Form\Handler\ModifyConfigHandler;
use Zikula\Module\MailerModule\Form\Handler\TestConfigHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in modifyconfig()
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function modifyconfigAction()
    {
        // security check
        if (!SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = FormUtil::newForm('ZikulaMailerModule', $this);

        return $form->execute('Admin/modifyconfig.tpl', new ModifyConfigHandler());
    }

    /**
     * This function displays a form to sent a test mail
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function testconfigAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = FormUtil::newForm('ZikulaMailerModule', $this);

        return $form->execute('Admin/testconfig.tpl', new TestConfigHandler());
    }
}