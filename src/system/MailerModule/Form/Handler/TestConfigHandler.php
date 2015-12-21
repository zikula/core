<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\MailerModule\Form\Handler;

use Zikula_Form_View;
use SecurityUtil;
use LogUtil;
use ModUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Form handler for the mailer modules testconfig form
 */
class TestConfigHandler extends \Zikula_Form_AbstractHandler
{
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

        $dumper = $this->view->getContainer()->get('zikula.dynamic_config_dumper');
        $params = $dumper->getConfiguration('swiftmailer');
        $paramHtml = $dumper->getConfigurationForHtml('swiftmailer');
        $view->assign('swiftmailerHtml', $paramHtml);

        $msgtype = $this->getVar('html') ? 'html' : 'text';
        $view->assign('msgtype', $msgtype);

        // assign all module vars
        $this->view->assign($params);

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
        switch ($args['commandName']) {
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
                $dumper = $this->view->getContainer()->get('zikula.dynamic_config_dumper');
                $swiftConfigHtml = "<h4>Swiftmailer Config:</h4>\n";
                $swiftConfigHtml .= $dumper->getConfigurationForHtml('swiftmailer');

                if ($html) {
                    $msgBody .= $swiftConfigHtml;
                    $altBody .= !empty($altBody) ? strip_tags($swiftConfigHtml) : '';
                } else {
                    $msgBody .= strip_tags($swiftConfigHtml);
                }

                // send the email
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
                }

                break;
        }

        return $view->redirect($view->getContainer()->get('router')->generate('zikulamailermodule_admin_testconfig', array(), RouterInterface::ABSOLUTE_URL));
    }
}
