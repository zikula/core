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

class Mailer_Api_User extends Zikula_AbstractApi
{
    /**
     * API function to send e-mail message
     * @param string args['fromname'] name of the sender
     * @param string args['fromaddress'] address of the sender
     * @param string args['toname '] name to the recipient
     * @param string args['toaddress'] the address of the recipient
     * @param string args['replytoname '] name to reply to
     * @param string args['replytoaddress'] address to reply to
     * @param string args['subject'] message subject
     * @param string args['contenttype '] optional contenttype of the mail (default config)
     * @param string args['charset'] optional charset of the mail (default config)
     * @param string args['encoding'] optional mail encoding (default config)
     * @param string args['body'] message body, if altbody is provided then this
     *                  is the HTML version of the body
     * @param string args['altbody'] alternative plain-text message body, if
     *                  specified the e-mail will be sent as multipart/alternative
     * @param array  args['cc'] addresses to add to the cc list
     * @param array  args['bcc'] addresses to add to the bcc list
     * @param array|string args['headers'] custom headers to add
     * @param int args['html'] HTML flag, if altbody is not specified then this
     *                  indicates whether body contains HTML or not; if altbody is
     *                  specified, then this value is ignored, the body is assumed
     *                  to be HTML, and the altbody is assumed to be plain text
     * @param array args['attachments'] array of either absolute filenames to attach
     *                  to the mail or array of arays in format
     *                  array($string,$filename,$encoding,$type)
     * @param array args['stringattachments'] array of arrays to treat as attachments,
     *                  format array($string,$filename,$encoding,$type)
     * @param array args['embeddedimages'] array of absolute filenames to image files
     *                  to embed in the mail
     * @todo Loading of language file based on Zikula language
     * @return bool true if successful, false otherwise
     */
    public function sendmessage($args)
    {
        // Development mailer mode
        if ($this->getVar('mailertype') == 5) {
            $output = '';
            foreach ($args as $key => $value) {
                $output .= '<b>'.$key.'</b>: '.$value.'<br />';
            }
            LogUtil::registerStatus($output);

            return true;
        }
           
        // Check for installed advanced Mailer module
        $event = new Zikula_Event('module.mailer.api.sendmessage', $this, $args);
        $this->eventManager->notify($event);
        if ($event->isStopped()) {
            return $event->getData();
        }

        // include php mailer class file
        require_once 'system/Mailer/lib/vendor/PHPMailerAutoload.php';

        // create new instance of mailer class
        $mail = new PHPMailer();

        // set default message parameters
        $mail->PluginDir = "system/Mailer/lib/vendor/";
        $mail->ClearAllRecipients();
        $mail->ContentType = isset($args['contenttype']) ? $args['contenttype'] : $this->getVar('contenttype');
        $mail->CharSet     = isset($args['charset'])     ? $args['charset']     : $this->getVar('charset');
        $mail->Encoding    = isset($args['encoding'])    ? $args['encoding']    : $this->getVar('encoding');
        $mail->WordWrap    = $this->getVar('wordwrap');

        // load the language file
        $mail->SetLanguage('en', $mail->PluginDir . 'language/');

        // get MTA configuration
        if ($this->getVar('mailertype') == 4) {
            $mail->IsSMTP();  // set mailer to use SMTP
            $mail->Host = $this->getVar('smtpserver');  // specify server
            $mail->Port = $this->getVar('smtpport');    // specify port
        } elseif ($this->getVar('mailertype') == 3) {
            $mail->IsQMail();  // set mailer to use QMail
        } elseif ($this->getVar('mailertype') == 2) {
            ini_set("sendmail_from", $args['fromaddress']);
            $mail->IsSendMail();  // set mailer to use SendMail
            $mail->Sendmail = $this->getVar('sendmailpath'); // specify Sendmail path
        } else {
            $mail->IsMail();  // set mailer to use php mail
        }

        // set authentication paramters if required
        if ($this->getVar('smtpauth') == 1) {
            $mail->SMTPAuth = true; // turn on SMTP authentication
            $mail->SMTPSecure =  $this->getVar('smtpsecuremethod'); // SSL or TLS
            $mail->Username = $this->getVar('smtpusername');  // SMTP username
            $mail->Password = $this->getVar('smtppassword');  // SMTP password
        }

        // set HTML mail if required
        if (isset($args['html']) && is_bool($args['html'])) {
            $mail->IsHTML($args['html']); // set email format to HTML
        } else {
            $mail->IsHTML($this->getVar('html')); // set email format to the default
        }

        // set fromname and fromaddress, default to 'sitename' and 'adminmail' config vars
        $mail->FromName = (isset($args['fromname']) && $args['fromname']) ? $args['fromname'] : System::getVar('sitename');
        $mail->From     = (isset($args['fromaddress']) && $args['fromaddress']) ? $args['fromaddress'] : System::getVar('adminmail');

        // add any to addresses
        if (is_array($args['toaddress'])) {
            $i = 0;
            foreach ($args['toaddress'] as $toadd) {
                isset($args['toname'][$i]) ? $toname = $args['toname'][$i] : $toname = $toadd;
                $mail->AddAddress($toadd, $toname);
                $i++;
            }
        } else {
            // $toaddress is not an array -> old logic
            $toname = '';
            if (isset($args['toname'])) {
                $toname = $args['toname'];
            }
            // process multiple names entered in a single field separated by commas (#262)
            foreach (explode(',', $args['toaddress']) as $toadd) {
                $mail->AddAddress($toadd, ($toname == '') ? $toadd : $toname);
            }
        }

        // if replytoname and replytoaddress have been provided us them
        // otherwise take the fromaddress, fromname we build earlier
        if (!isset($args['replytoname']) || empty($args['replytoname'])) {
            $args['replytoname'] = $mail->FromName;
        }
        if (!isset($args['replytoaddress'])  || empty($args['replytoaddress'])) {
            $args['replytoaddress'] = $mail->From;
        }
        $mail->AddReplyTo($args['replytoaddress'], $args['replytoname']);

        // add any cc addresses
        if (isset($args['cc']) && is_array($args['cc'])) {
            foreach ($args['cc'] as $email) {
                if (isset($email['name'])) {
                    $mail->AddCC($email['address'], $email['name']);
                } else {
                    $mail->AddCC($email['address']);
                }
            }
        }

        // add any bcc addresses
        if (isset($args['bcc']) && is_array($args['bcc'])) {
            foreach ($args['bcc'] as $email) {
                if (isset($email['name'])) {
                    $mail->AddBCC($email['address'], $email['name']);
                } else {
                    $mail->AddBCC($email['address']);
                }
            }
        }

        // add any custom headers
        if (isset($args['headers']) && is_string($args['headers'])) {
            $args['headers'] = explode ("\n", $args['headers']);
        }
        if (isset($args['headers']) && is_array($args['headers'])) {
            foreach ($args['headers'] as $header) {
                $mail->AddCustomHeader($header);
            }
        }

        // add message subject and body
        $mail->Subject = $args['subject'];
        $mail->Body    = $args['body'];
        if (isset($args['altbody']) && !empty($args['altbody'])) {
            $mail->AltBody = $args['altbody'];
        }

        // add attachments
        if (isset($args['attachments']) && !empty($args['attachments'])) {
            foreach ($args['attachments'] as $attachment) {
                if (is_array($attachment)) {
                    if (count($attachment) != 4) {
                        // skip invalid arrays
                        continue;
                    }
                    $mail->AddAttachment($attachment[0], $attachment[1], $attachment[2], $attachment[3]);
                } else {
                    $mail->AddAttachment($attachment);
                }
            }
        }

        // add string attachments.
        if (isset($args['stringattachments']) && !empty($args['stringattachments'])) {
            foreach ($args['stringattachments'] as $attachment) {
                if (is_array($attachment) && count($attachment) == 4) {
                    $mail->AddStringAttachment($attachment[0], $attachment[1], $attachment[2], $attachment[3]);
                }
            }
        }

        // add embedded images
        if (isset($args['embeddedimages']) && !empty($args['embeddedimages'])) {
            foreach ($args['embeddedimages'] as $embeddedimage) {
                $ret = $mail->AddEmbeddedImage($embeddedimage['path'],
                        $embeddedimage['cid'],
                        $embeddedimage['name'],
                        $embeddedimage['encoding'],
                        $embeddedimage['type']);
            }
        }

        // send message
        if (!$mail->Send()) {
            // message not send
            $args['errorinfo'] = ($mail->IsError()) ? $mail->ErrorInfo : __('Error! An unidentified problem occurred while sending the e-mail message.');
            LogUtil::log(__f('Error! A problem occurred while sending an e-mail message from \'%1$s\' (%2$s) to (%3$s) (%4$s) with the subject line \'%5$s\': %6$s', $args));
            if (SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerError($args['errorinfo']);
            } else {
                return LogUtil::registerError(__('Error! A problem occurred while sending the e-mail message.'));
            }
        }

        return true; // message sent
    }
}
