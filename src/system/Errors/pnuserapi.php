<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Errors
 * @license http://www.gnu.org/copyleft/gpl.html
*/

/**
 * Send E-mail
 * This function e-mails the site administrator with an error.
 * @author Brian Lindner
 * @author Brook Humphrey
 */
function errors_userapi_send_email()
{
    /* send error reporting email to admin */
    $adminmail = System::getVar('adminmail');
    $notify_from = System::getVar('notify_from');
    $sitename = System::getVar('sitename');
    $errortime = date("m/j/Y at g:i a");

    $doc = System::serverGetVar('REDIRECT_URL');
    $server = System::serverGetVar('HTTP_HOST');
    $doc = "http://$server$doc";
    $headers = "ATTN: Fatal Error at $doc (".System::serverGetVar('REDIRECT_STATUS').")\n";
    $headers .= "From: $sitename Error Tools $adminmail\n";
    $headers .= "X-Sender: <$notify_from>\n";
    $headers .= "X-Mailer: PHP Error Tools by WebMedic\n";
    $headers .= "X-Mailer-Version: ".System::VERSION_ID." ".System::VERSION_NUM."\n";
    $headers .= "X-Priority: 1\n";
    $headers .= "Get-Script-At: <http://www.zikula.org>\n";
    $body = "Webmaster, the following item was not found on your website:\n\n";
    $body .= " at ".$errortime;
    $body .= "WEBSITE\n-- ".System::serverGetVar('SERVER_NAME').':'.System::serverGetVar('SERVER_PORT')."\n\n";
    $body .= "REASON\n-- ".System::serverGetVar('$REDIRECT_ERRORSOR_NOTES')."\n\n";
    $body .= "PROBLEM URL\n-- $doc\n\n";
    $body .= "REFERRER\n-- ".System::serverGetVar('HTTP_REFERER')."\n\n";
    $body .= "REQUEST\n-- Host: ".System::serverGetVar('HTTP_HOST')."\n-- Query String: ".System::serverGetVar('REDIRECT_QUERY_STRING')."\n";
    $body .= "-- Method: ".System::serverGetVar('$REQUEST_METHOD')."\n\n";
    $body .= "USER\n-- Host: ".System::serverGetVar('REMOTE_HOST')."\n-- IP: ".System::serverGetVar('REMOTE_ADDR')."\n-- User: ".System::serverGetVar('REMOTE_USER')."\n-- Agent: ".System::serverGetVar('HTTP_USER_AGENT')."\n-- Cookies: ".System::serverGetVar('HTTP_COOKIE')."\n\n";
    $body .= "Envolution\n-- version: ".System::VERSION_NUM;

    // Send the mail message.
    pnMail($adminmail, $headers, $body);
}
