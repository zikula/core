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
use ModUtil;
use System;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\ModUrl;
use ZLanguage;
use Swift_Message;

/**
 * Form handler for the mailer modules testconfig form
 */
class TestConfigHandler extends \Zikula_Form_AbstractHandler
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
     * @return bool true if successful
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $msgtype = $this->getVar('html') ? 'html' : 'text';
        $view->assign('msgtype', $msgtype);

        // assign all module vars
        $this->view->assign($this->getVars());

        return true;
    }

    /**
     * handle commands the form
     *
     * @param \Zikula_Form_view $view view object
     * @param array[] $args {
     *      @type string $commandName the command to execute
     *                      }
     *
     * @return boolean|void
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
                $formValues = $view->getValues();
                $toname = (string)$formValues['toname'];
                $toaddress = (string)$formValues['toaddress'];
                $subject = (string)$formValues['subject'];
                $msgtype = (string)$formValues['msgtype'];
                $textBody = (string)$formValues['mailer_textbody'];
                $htmlBody = (string)$formValues['mailer_body'];

                $html = in_array($msgtype, array('html', 'multipart')) ? true : false;
                if ($html) {
                    $msgBody = $htmlBody;
                    $altBody = $textBody;
                } else {
                    $msgBody = $textBody;
                    $altBody = '';
                }

                // add swiftmailer config to message for testing
                $msgBody .= "Swiftmailer Config:<br />\n";
                $dumper = $this->view->getContainer()->get('zikula.dynamic_config_dumper');
                $params =  $dumper->getConfiguration('swiftmailer');
                foreach ($params as $k => $v) {
                    if (!is_array($v)) {
                        $msgBody .= "$k: $v<br />\n";
                    } else {
                        $msgBody .= "$k:<br />\n";
                        foreach ($v as $k2 => $v2) {
                            $msgBody .= "  $k2: $v2<br />\n";
                        }
                    }
                }

                // set the email
                $result = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendmessage', array(
                    'toname' => $toname,
                    'toaddress' => $toaddress,
                    'subject' => $subject,
                    'body' => $msgBody,
                    'altbody' => $altBody,
                    'html' => $html)
                );

                // check our result and return the correct error code
                if ($result === true) {
                    // Success
                    LogUtil::registerStatus($this->__('Done! Message sent.'));
                } elseif ($result === false) {
                    // Failure
                    LogUtil::registerError($this->__f('Error! Could not send message. %s', ''));
                } else {
                    // Failure with error
                    LogUtil::registerError($this->__f('Error! Could not send message. %s', $result));
                }

                // test Swiftmailer
                $message = Swift_Message::newInstance()
                    ->setSubject('SWIFTMAILER: ' . $subject)
                    ->setFrom(System::getVar('adminmail'))
                    ->setTo($toaddress)
                    ->setBody($msgBody);
                \ServiceUtil::get('mailer')->send($message);

                break;
        }

        return $view->redirect(new ModUrl($this->name, 'admin', 'testconfig', ZLanguage::getLanguageCode()));
    }
}
