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

namespace Zikula\Module\MailerModule\Form\Handler;

use Zikula_Form_View;
use SecurityUtil;
use LogUtil;
use DataUtil;
use ZLanguage;
use ModUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Form handler for the mailer modules modifyconfig form
 */
class ModifyConfigHandler extends \Zikula_Form_AbstractHandler
{
    /**
     * @var array values for this form
     */
    private $formValues;

    /**
     * initialise the form
     *
     * @param \Zikula_Form_view $view view object
     *
     * @return bool true if succcessful
     (
     * @throws \AccessDeniedHttpException Thrown if the user doesn't have admin access to the module
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // assign the module mail agent types
        $view->assign('mailertypeItems', array(
            array('value' => 1, 'text' => DataUtil::formatForDisplay($this->__("Internal PHP `mail()` function"))),
            array('value' => 2, 'text' => DataUtil::formatForDisplay($this->__('Sendmail message transfer agent'))),
            array('value' => 3, 'text' => DataUtil::formatForDisplay($this->__('QMail message transfer agent'))),
            array('value' => 4, 'text' => DataUtil::formatForDisplay($this->__('SMTP mail transfer protocol'))),
            array('value' => 5, 'text' => DataUtil::formatForDisplay($this->__('Development/debug mode (Redirect e-mails to LogUtil)')))
        ));

        $view->assign('encodingItems', array(
            array('value' => '8bit', 'text' => '8bit'),
            array('value' => '7bit', 'text' => '7bit'),
            array('value' => 'binary', 'text' => 'binary'),
            array('value' => 'base64', 'text' => 'base64'),
            array('value' => 'quoted-printable', 'text' => 'quoted-printable')
        ));

        $view->assign('smtpsecuremethodItems', array(
            array('value' => '', 'text' => 'None'),
            array('value' => 'ssl', 'text' => 'SSL'),
            array('value' => 'tls', 'text' => 'TLS')
        ));

        // assign all module vars
        $this->view->assign($this->getVars());

        return true;
    }

    /**
     * Handle form commands
     *
     * @param \Zikula_Form_View $view view object
     * @param array $args
     *
     * @return void|bool false if the form to be saved isn't valid, void otherwise
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        switch($args['commandName']) {
            case 'cancel':
                break;
            case 'save':
                if (!$view->isValid()) {
                    return false;
                }
                $this->formValues = $view->getValues();

                // set our new module variable values
                $vars = array();
                $vars['mailertype'] = (int)$this->getFormValue('mailertype', 1);

                $vars['charset'] = (string)$this->getFormValue('charset', ZLanguage::getEncoding());

                $vars['encoding'] = (string)$this->getFormValue('encoding', '8bit');

                $vars['html'] = (bool)$this->getFormValue('html', false);

                $vars['wordwrap'] = (int)$this->getFormValue('wordwrap', 50);

                $vars['msmailheaders'] = (bool)$this->getFormValue('msmailheaders', false);

                $vars['sendmailpath'] = (string)$this->getFormValue('sendmailpath', '/usr/sbin/sendmail');

                $vars['smtpauth'] = (bool)$this->getFormValue('smtpauth', false);

                $vars['smtpserver'] = (string)$this->getFormValue('smtpserver', 'localhost');

                $vars['smtpport'] = (int)$this->getFormValue('smtpport', 25);

                $vars['smtptimeout'] = (int)$this->getFormValue('smtptimeout', 10);

                $vars['smtpusername'] = (string)$this->getFormValue('smtpusername', '');

                $vars['smtppassword'] = (string)$this->getFormValue('smtppassword', '');

                $vars['smtpsecuremethod'] = (string)$this->getFormValue('smtpsecuremethod', '');

                $this->setVars($vars);

                // the module configuration has been updated successfuly
                LogUtil::registerStatus($this->__('Done! Saved module configuration.'));
                break;
        }

        return $view->redirect(ModUtil::url('ZikulaMailerModule', 'admin', 'modifyconfig'));
    }

    /**
     * Get the value of a form field
     *
     * @param string $key     the field key to query
     * @param string $default the default value for the query
     *
     * @return mixed the form value (or default otherwise)
     */
    private function getFormValue($key, $default)
    {
        return isset($this->formValues[$key]) ? $this->formValues[$key] : $default;
    }
}
