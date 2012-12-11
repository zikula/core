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
 * Errors_Api_User class.
 */
class Errors_Api_User extends Zikula_AbstractApi
{
    /**
     * This function e-mails the site administrator with an error.
     */
    public function send_email()
    {
        /* send error reporting email to admin */
        $adminmail = System::getVar('adminmail');
        $notify_from = System::getVar('notify_from');
        $sitename = System::getVar('sitename');
        $errortime = date("m/j/Y at g:i a");

        $doc = System::serverGetVar('REDIRECT_URL');
        $server = System::serverGetVar('HTTP_HOST');
        $doc = "http://$server$doc";
        $headers = "ATTN: Fatal Error at $doc (" . System::serverGetVar('REDIRECT_STATUS') . ")\n";
        $headers .= "From: $sitename Error Tools $adminmail\n";
        $headers .= "X-Sender: <$notify_from>\n";
        $headers .= "X-Mailer-Version: " . Zikula_Core::VERSION_ID . " " . Zikula_Core::VERSION_NUM . "\n";
        $headers .= "X-Priority: 1\n";
        $body = "Webmaster, the following item was not found on your website:\n\n";
        $body .= " at " . $errortime;
        $body .= "WEBSITE\n-- " . System::serverGetVar('SERVER_NAME') . ':' . System::serverGetVar('SERVER_PORT') . "\n\n";
        $body .= "REASON\n-- " . System::serverGetVar('$REDIRECT_ERRORSOR_NOTES') . "\n\n";
        $body .= "PROBLEM URL\n-- $doc\n\n";
        $body .= "REFERRER\n-- " . System::serverGetVar('HTTP_REFERER') . "\n\n";
        $body .= "REQUEST\n-- Host: " . System::serverGetVar('HTTP_HOST') . "\n-- Query String: " . System::serverGetVar('REDIRECT_QUERY_STRING') . "\n";
        $body .= "-- Method: " . System::serverGetVar('$REQUEST_METHOD') . "\n\n";
        $body .= "USER\n-- Host: " . System::serverGetVar('REMOTE_HOST') . "\n-- IP: " . System::serverGetVar('REMOTE_ADDR') . "\n-- User: " . System::serverGetVar('REMOTE_USER') . "\n-- Agent: " . System::serverGetVar('HTTP_USER_AGENT') . "\n-- Cookies: " . System::serverGetVar('HTTP_COOKIE') . "\n\n";
        $body .= "Envolution\n-- version: " . Zikula_Core::VERSION_NUM;

        // Send the mail message.
        System::mail($adminmail, $headers, $body);
    }

}
