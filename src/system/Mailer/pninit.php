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
    pnModSetVar('Mailer', 'mailertype', 1);
    pnModSetVar('Mailer', 'charset', ZLanguage::getEncoding());
    pnModSetVar('Mailer', 'encoding', '8bit');
    pnModSetVar('Mailer', 'html', false);
    pnModSetVar('Mailer', 'wordwrap', 50);
    pnModSetVar('Mailer', 'msmailheaders', false);
    pnModSetVar('Mailer', 'sendmailpath', '/usr/sbin/sendmail');
    pnModSetVar('Mailer', 'smtpauth', false);
    pnModSetVar('Mailer', 'smtpserver', 'localhost');
    pnModSetVar('Mailer', 'smtpport', 25);
    pnModSetVar('Mailer', 'smtptimeout', 10);
    pnModSetVar('Mailer', 'smtpusername', '');
    pnModSetVar('Mailer', 'smtppassword', '');

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
