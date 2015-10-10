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

namespace Zikula\MailerModule;

use ZLanguage;
use HookUtil;

/**
 * Installation and upgrade routines for the mailer module
 */
class MailerModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * initialise the template module
     *
     * @return bool true if successful, false otherwise
     */
    public function install()
    {
        $this->setVars($this->getDefaults());

        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * @param  string $oldversion version number string to upgrade from
     *
     * @return bool|string  true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.3.1':
                $this->setVar('smtpsecuremethod', 'ssl');
            case '1.3.2':
                // clear old modvars
                // use manual method because getVars() is not available during system upgrade
                $modVarEntities = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionVarEntity')->findBy(array('modname' => $this->name));
                $modVars = array();
                foreach ($modVarEntities as $var) {
                    $modVars[$var['name']] = $var['value'];
                }
                $this->delVars();
                $this->setVarWithDefault('charset', $modVars['charset']);
                $this->setVarWithDefault('encoding', $modVars['encoding']);
                $this->setVarWithDefault('html', $modVars['html']);
                $this->setVarWithDefault('wordwrap', $modVars['wordwrap']);
                // new modvar for 1.4.0
                $this->setVarWithDefault('enableLogging', false);

                // write the config file
                $mailerTypeConversion = array(
                    1 => 'mail',
                    2 => 'sendmail',
                    3 => 'mail',
                    4 => 'smtp',
                    5 => 'mail',
                );
                $config = array(
                    'transport' => $mailerTypeConversion[$modVars['mailertype']],
                    'username' => $modVars['smtpusername'],
                    'password' => $modVars['smtppassword'],
                    'host' => $modVars['smtpserver'],
                    'port' => $modVars['smtpport'],
                    'encryption' => (isset($modVars['smtpsecuremethod']) && in_array($modVars['smtpsecuremethod'], array('ssl', 'tls')) ? $modVars['smtpsecuremethod'] : 'ssl'),
                    'auth_mode' => (!empty($modVars['auth'])) ? 'login' : null,
                    'spool' => array('type' => 'memory'),
                    'delivery_address' => null,
                    'disable_delivery' => $modVars['mailertype'] == 5,
                );
                $configDumper = $this->getContainer()->get('zikula.dynamic_config_dumper');
                $configDumper->setConfiguration('swiftmailer', $config);

            case '1.4.0':
                $configDumper = $this->getContainer()->get('zikula.dynamic_config_dumper');
                $config = $configDumper->getConfiguration('swiftmailer');
                // remove spool parameter
                unset($config['spool']);
                $configDumper->setConfiguration('swiftmailer', $config);
            case '1.4.1':
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
            case '1.4.2':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the Mailer module
     *
     * @return bool true if successful, false otherwise
     */
    public function uninstall()
    {
        // Delete any module variables
        $this->delVars();

        // Remove hooks
        HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());

        // Deletion successful
        return true;
    }

    /**
     * default module vars
     * @return array
     */
    private function getDefaults()
    {
        return array(
            'charset' => ZLanguage::getEncoding(),
            'encoding' => '8bit',
            'html' => false,
            'wordwrap' => 50,
            'enableLogging' => false,
        );
    }

    /**
     * set the module var but if it is not set, use the default instead.
     *
     * @param string $key
     * @param null $value
     */
    private function setVarWithDefault($key, $value = null)
    {
        if (isset($value)) {
            parent::setVar($key, $value);
        }
        $defaults = $this->getDefaults();
        parent::setVar($key, $defaults[$key]);
    }
}
