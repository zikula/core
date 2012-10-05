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

namespace MailerModule\Controller;

use Zikula_View, SecurityUtil, ModUtil, DataUtil, ZLanguage, LogUtil;

class AdminController extends \Zikula\Framework\Controller\AbstractController
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
        return $this->redirect(ModUtil::url('Mailer', 'admin', 'modifyconfig'));
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     * @return string HTML string
     */
    public function modifyconfigAction()
    {
        // security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN));

        // assign the module mail agent types
        $this->view->assign('mailertypes', array(1 => DataUtil::formatForDisplay($this->__("Internal PHP `mail()` function")),
                                                 2 => DataUtil::formatForDisplay($this->__('Sendmail message transfer agent')),
                                                 3 => DataUtil::formatForDisplay($this->__('QMail message transfer agent')),
                                                 4 => DataUtil::formatForDisplay($this->__('SMTP mail transfer protocol')),
                                                 5 => DataUtil::formatForDisplay($this->__('Development/debug mode (Redirect e-mails to LogUtil)'))));
        $this->view->assign('smtpsecuremethod', $this->getVar('securemethod'));

        // assign all module vars
        $this->view->assign($this->getVars());

        return $this->response($this->view->fetch('Admin/modifyconfig.tpl'));
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     * @see Mailer_admin_updateconfig()
     * @param int mailertype Mail transport agent
     * @param string charset default character set of the message
     * @param string encoding default encoding
     * @param bool html send html e-mails by default
     * @param int wordwrap word wrap column
     * @param int msmailheaders include MS mail headers
     * @param string sendmailpath path to sendmail
     * @param int smtpauth enable SMTPAuth
     * @param string smtpserver ip address of SMTP server
     * @param int smtpport port number of SMTP server
     * @param int smtptimeout SMTP timeout
     * @param string smtpusername SMTP username
     * @param string smtppassword SMTP password
     * @return bool true if update successful
     */
    public function updateconfigAction()
    {
        $this->checkCsrfToken();

        // security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN));

        // set our new module variable values
        $mailertype = (int)$this->request->request->get('mailertype', 1);
        $this->setVar('mailertype', $mailertype);

        $charset = (string)$this->request->request->get('charset', ZLanguage::getEncoding());
        $this->setVar('charset', $charset);

        $encoding = (string)$this->request->request->get('encoding', '8bit');
        $this->setVar('encoding', $encoding);

        $html = (bool)$this->request->request->get('html', false);
        $this->setVar('html', $html);

        $wordwrap = (int)$this->request->request->get('wordwrap', 50);
        $this->setVar('wordwrap', $wordwrap);

        $msmailheaders = (bool)$this->request->request->get('msmailheaders', false);
        $this->setVar('msmailheaders', $msmailheaders);

        $sendmailpath = (string)$this->request->request->get('sendmailpath', '/usr/sbin/sendmail');
        $this->setVar('sendmailpath', $sendmailpath);

        $smtpauth = (bool)$this->request->request->get('smtpauth', false);
        $this->setVar('smtpauth', $smtpauth);

        $smtpserver = (string)$this->request->request->get('smtpserver', 'localhost');
        $this->setVar('smtpserver', $smtpserver);

        $smtpport = (int)$this->request->request->get('smtpport', 25);
        $this->setVar('smtpport', $smtpport);

        $smtptimeout = (int)$this->request->request->get('smtptimeout', 10);
        $this->setVar('smtptimeout', $smtptimeout);

        $smtpusername = (string)$this->request->request->get('smtpusername', '');
        $this->setVar('smtpusername', $smtpusername);

        $smtppassword = (string)$this->request->request->get('smtppassword', '');
        $this->setVar('smtppassword', $smtppassword);

        $smtpsecuremethod = (string)$this->request->request->get('smtpsecuremethod', '');
        $this->setVar('smtpsecuremethod', $smtpsecuremethod);

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        return $this->redirect(ModUtil::url('Mailer', 'admin', 'modifyconfig'));
    }

    /**
     * This function displays a form to sent a test mail
     * @return string HTML string
     */
    public function testconfigAction()
    {
        // security check
        if (!SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('Admin/estconfig.tpl'));
    }

    /**
     * This function processes the results of the test form
     * @param string args['toname '] name to the recipient
     * @param string args['toaddress'] the address of the recipient
     * @param string args['subject'] message subject
     * @param string args['body'] message body
     * @param int args['html'] HTML flag
     * @return bool true
     */
    public function sendmessageAction(array $args = array())
    {
        $this->checkCsrfToken();

        // security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN));

        $toname = (string)$this->request->request->get('toname');
        $toaddress = (string)$this->request->request->get('toaddress');
        $subject = (string)$this->request->request->get('subject');
        $body = (string)$this->request->request->get('body');
        $altBody = (string)$this->request->request->get('altbody', false);
        $html = (bool)$this->request->request->get('html', false);

        // set the email
        $result = ModUtil::apiFunc('MailerModule', 'user', 'sendmessage',
                    array('toname' => $toname,
                    'toaddress' => $toaddress,
                    'subject' => $subject,
                    'body' => $body,
                    'altbody' => $altBody,
                    'html' => $html));

        // check our result and return the correct error code
        if ($result === true) {
            // Success
            LogUtil::registerStatus($this->__('Done! Message sent.'));
        } elseif ($result === false) {
            // Failiure
            LogUtil::registerError($this->__f('Error! Could not send message. %s', ''));
        } else {
            // Failiure with error
            LogUtil::registerError($this->__f('Error! Could not send message. %s', $result));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        return $this->redirect(ModUtil::url('Mailer', 'admin', 'testconfig'));
    }
}
