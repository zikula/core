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

namespace Zikula\Module\MailerModule;

use ZLanguage;

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
        $this->setVar('charset', ZLanguage::getEncoding());
        $this->setVar('encoding', '8bit');
        $this->setVar('html', false);
        $this->setVar('wordwrap', 50);
        $this->setVar('enableLogging', false);

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
                $modVars = $this->getVars();
                $this->delVars();
                $this->setVar('charset', $modVars['charset']);
                $this->setVar('encoding', $modVars['encoding']);
                $this->setVar('html', $modVars['html']);
                $this->setVar('wordwrap', $modVars['wordwrap']);
                // new modvar for 1.4.0
                $this->setVar('enableLogging', false);

                // write the config file
                $mailerTypeConversion = array(
                    1 => 'mail',
                    2 => 'sendmail',
                    3 => 'mail',
                    4 => 'smtp',
                    5 => 'test',
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
                    'disable_delivery' => false,
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

        // Deletion successful
        return true;
    }
}
