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
        $this->setVar('mailertype', 1);
        $this->setVar('charset', ZLanguage::getEncoding());
        $this->setVar('encoding', '8bit');
        $this->setVar('html', false);
        $this->setVar('wordwrap', 50);
        $this->setVar('msmailheaders', false);
        $this->setVar('sendmailpath', '/usr/sbin/sendmail');
        $this->setVar('smtpauth', false);
        $this->setVar('smtpserver', 'localhost');
        $this->setVar('smtpport', 25);
        $this->setVar('smtptimeout', 10);
        $this->setVar('smtpusername', '');
        $this->setVar('smtppassword', '');
        $this->setVar('smtpsecuremethod', 'ssl');

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