<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Mailer
 */

/**
 * initialise the template module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function Mailer_init()
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
function Mailer_upgrade($oldversion)
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
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function Mailer_delete()
{
    // Delete any module variables
    pnModDelVar('Mailer');

    // Deletion successful
    return true;
}
