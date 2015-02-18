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

/**
 * Mailer_Installer class.
 */
class Mailer_Installer extends Zikula_AbstractInstaller
{

    /**
     * initialise the template module
     * This function is only ever called once during the lifetime of a particular
     * module instance
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

        // register ui_hook for HTML e-mail test
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param  string $oldVersion version number string to upgrade from
     * @return mixed  true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.3.1':
                $this->setVar('smtpsecuremethod', 'ssl');
            case '1.3.2':
                // register ui_hook for HTML e-mail test
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
            case '1.3.3':
                // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the Mailer module
     * This function is only ever called once during the lifetime of a particular
     * module instance
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
}
