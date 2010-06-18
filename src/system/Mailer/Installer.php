<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Mailer_Installer extends Zikula_Installer
{

    /**
     * initialise the template module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * @return bool true if successful, false otherwise
     */
    public function install()
    {
        ModUtil::setVar('Mailer', 'mailertype', 1);
        ModUtil::setVar('Mailer', 'charset', ZLanguage::getEncoding());
        ModUtil::setVar('Mailer', 'encoding', '8bit');
        ModUtil::setVar('Mailer', 'html', false);
        ModUtil::setVar('Mailer', 'wordwrap', 50);
        ModUtil::setVar('Mailer', 'msmailheaders', false);
        ModUtil::setVar('Mailer', 'sendmailpath', '/usr/sbin/sendmail');
        ModUtil::setVar('Mailer', 'smtpauth', false);
        ModUtil::setVar('Mailer', 'smtpserver', 'localhost');
        ModUtil::setVar('Mailer', 'smtpport', 25);
        ModUtil::setVar('Mailer', 'smtptimeout', 10);
        ModUtil::setVar('Mailer', 'smtpusername', '');
        ModUtil::setVar('Mailer', 'smtppassword', '');

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @author       Mark West
     * @param        string   $oldVersion   version number string to upgrade from
     * @return       mixed    true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion)
        {
            case '1.3':
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
        ModUtil::delVar('Mailer');

        // Deletion successful
        return true;
    }
}