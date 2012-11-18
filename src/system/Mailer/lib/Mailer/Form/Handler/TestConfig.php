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

class Mailer_Form_Handler_TestConfig extends Zikula_Form_AbstractHandler
{
    private $formValues;

    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        $msgtype = $this->getVar('html') ? 'html' : 'text';
        $view->assign('msgtype', $msgtype);

        // assign all module vars
        $this->view->assign($this->getVars());

        return true;
    }

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

                // set the email
                $result = ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
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
                    // Failiure
                    LogUtil::registerError($this->__f('Error! Could not send message. %s', ''));
                } else {
                    // Failiure with error
                    LogUtil::registerError($this->__f('Error! Could not send message. %s', $result));
                }

                break;
        }

        return $view->redirect(ModUtil::url('Mailer', 'admin', 'testconfig'));
    }
}
