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

    $doc = pnServerGetVar('REDIRECT_URL');
    $server = pnServerGetVar('HTTP_HOST');
    $doc = "http://$server$doc";
    $headers = "ATTN: Fatal Error at $doc (".pnServerGetVar('REDIRECT_STATUS').")\n";
    $headers .= "From: $sitename Error Tools $adminmail\n";
    $headers .= "X-Sender: <$notify_from>\n";
    $headers .= "X-Mailer: PHP Error Tools by WebMedic\n";
    $headers .= "X-Mailer-Version: ".System::VERSION_ID." ".System::VERSION_NUM."\n";
    $headers .= "X-Priority: 1\n";
    $headers .= "Get-Script-At: <http://www.zikula.org>\n";
    $body = "Webmaster, the following item was not found on your website:\n\n";
    $body .= " at ".$errortime;
    $body .= "WEBSITE\n-- ".pnServerGetVar('SERVER_NAME').':'.pnServerGetVar('SERVER_PORT')."\n\n";
    $body .= "REASON\n-- ".pnServerGetVar('$REDIRECT_ERRORSOR_NOTES')."\n\n";
    $body .= "PROBLEM URL\n-- $doc\n\n";
    $body .= "REFERRER\n-- ".pnServerGetVar('HTTP_REFERER')."\n\n";
    $body .= "REQUEST\n-- Host: ".pnServerGetVar('HTTP_HOST')."\n-- Query String: ".pnServerGetVar('REDIRECT_QUERY_STRING')."\n";
    $body .= "-- Method: ".pnServerGetVar('$REQUEST_METHOD')."\n\n";
    $body .= "USER\n-- Host: ".pnServerGetVar('REMOTE_HOST')."\n-- IP: ".pnServerGetVar('REMOTE_ADDR')."\n-- User: ".pnServerGetVar('REMOTE_USER')."\n-- Agent: ".pnServerGetVar('HTTP_USER_AGENT')."\n-- Cookies: ".pnServerGetVar('HTTP_COOKIE')."\n\n";
    $body .= "Envolution\n-- version: ".System::VERSION_NUM;

    // Send the mail message.
    pnMail($adminmail, $headers, $body);
}
