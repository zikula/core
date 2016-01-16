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

namespace Zikula\MailerModule\Form\Handler;

use Zikula_Form_View;
use SecurityUtil;
use LogUtil;
use DataUtil;
use ZLanguage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;

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
     * @return bool true if successful
     (
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // assign the mail transport types
        $view->assign('transportItems', array(
            array('value' => 'mail', 'text' => DataUtil::formatForDisplay($this->__("Internal PHP `mail()` function"))),
            array('value' => 'sendmail', 'text' => DataUtil::formatForDisplay($this->__('Sendmail message transfer agent'))),
            array('value' => 'gmail', 'text' => DataUtil::formatForDisplay($this->__('Google gmail'))),
            array('value' => 'smtp', 'text' => DataUtil::formatForDisplay($this->__('SMTP mail transfer protocol'))),
            array('value' => 'test'/*'null'*/, 'text' => DataUtil::formatForDisplay($this->__('Development/debug mode (Do not send any email)')))
        ));

        $view->assign('encodingItems', array(
            array('value' => '8bit', 'text' => '8bit'),
            array('value' => '7bit', 'text' => '7bit'),
            array('value' => 'binary', 'text' => 'binary'),
            array('value' => 'base64', 'text' => 'base64'),
            array('value' => 'quoted-printable', 'text' => 'quoted-printable')
        ));

        $view->assign('encryptionItems', array(
            array('value' => null, 'text' => 'None'),
            array('value' => 'ssl', 'text' => 'SSL'),
            array('value' => 'tls', 'text' => 'TLS')
        ));

        $view->assign('auth_modeItems', array(
            array('value' => null, 'text' => 'None'),
            array('value' => 'plain', 'text' => 'Plain'),
            array('value' => 'login', 'text' => 'Login'),
            array('value' => 'cram-md5', 'text' => 'Cram-MD5'),
        ));

        $dumper = $this->view->getContainer()->get('zikula.dynamic_config_dumper');
        $params = $dumper->getConfiguration('swiftmailer');

        // assign all config vars
        $this->view->assign($params);
        $this->view->assign($this->getVars());

        return true;
    }

    /**
     * Handle form commands
     *
     * @param \Zikula_Form_View $view view object
     * @param array $args
     *
     * @return bool|void false if the form to be saved isn't valid, void otherwise
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
                $this->formValues = $view->getValues();

                // set new module variable values
                $vars = array();
                $vars['charset'] = (string)$this->getFormValue('charset', ZLanguage::getEncoding());
                $vars['encoding'] = (string)$this->getFormValue('encoding', '8bit');
                $vars['html'] = (bool)$this->getFormValue('html', false);
                $vars['wordwrap'] = (int)$this->getFormValue('wordwrap', 50);
                $vars['enableLogging'] = (bool)$this->getFormValue('enableLogging', false);
                $this->setVars($vars);

                // fetch different username and password fields depending on the transport type
                $transport = (string)$this->getFormValue('transport', 'mail');
                $credentialsSuffix = $transport == 'gmail' ? 'Gmail' : '';

                // write the config file
                // http://symfony.com/doc/current/reference/configuration/swiftmailer.html
                $configDumper = $this->view->getContainer()->get('zikula.dynamic_config_dumper');
                $currentConfig = $configDumper->getConfiguration('swiftmailer');
                $config = array(
                    'transport' => (string)$this->getFormValue('transport', 'mail'),
                    'username' => $this->getFormValue('username' . $credentialsSuffix, null),
                    'password' => $this->getFormValue('password' . $credentialsSuffix, null),
                    'host' => (string)$this->getFormValue('host', 'localhost'),
                    'port' => (int)$this->getFormValue('port', 25),
                    'encryption' => $this->getFormValue('encryption', null),
                    'auth_mode' => $this->getFormValue('auth_mode', null),
                    // the items below can be configured by modifying the app/config/dynamic/generated.yml file
//                    'spool' => !empty($currentConfig['spool']) ? $currentConfig['spool'] : array('type' => 'memory'),
                    'delivery_addresses' => !empty($currentConfig['delivery_addresses'])
                        ? $currentConfig['delivery_addresses']
                        : (!empty($currentConfig['delivery_address']) ? [$currentConfig['delivery_address']] : []),
                    'disable_delivery' => !empty($currentConfig['disable_delivery']) ? $currentConfig['disable_delivery'] : false,
                );
                $configDumper->setConfiguration('swiftmailer', $config);

                // the module configuration has been updated successfully
                LogUtil::registerStatus($this->__('Done! Saved module configuration.'));
                break;
        }

        return $view->redirect($view->getContainer()->get('router')->generate('zikulamailermodule_admin_modifyconfig', array(), RouterInterface::ABSOLUTE_URL));
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
