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

namespace Zikula\Module\MailerModule\Api;

use LogUtil;
use Zikula;
use PHPMailer;
use System;
use SecurityUtil;
use Swift_Message;

/**
 * API functions used by user controllers
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * API function to send e-mail message
     *
     * @param mixed[] $args {
     *       @type string       $fromname name of the sender
     *       @type string       $fromaddress address of the sender
     *       @type string       $toname name to the recipient
     *       @type string       $toaddress the address of the recipient
     *       @type string       $replytoname name to reply to
     *       @type string       $replytoaddress address to reply to
     *       @type string       $subject message subject
     *       @type string       $contenttype optional contenttype of the mail (default config)
     *       @type string       $charset optional charset of the mail (default config)
     *       @type string       $encoding optional mail encoding (default config)
     *       @type string       $body message body, if altbody is provided then
     *                                    this is the HTML version of the body
     *       @type string       $altbody alternative plain-text message body, if specified the 
     *                                    e-mail will be sent as multipart/alternative
     *       @type array        $cc addresses to add to the cc list
     *       @type array        $bcc addresses to add to the bcc list
     *       @type array|string $headers custom headers to add
     *       @type int          $html HTML flag, if altbody is not specified then this
     *                                    indicates whether body contains HTML or not; if altbody is
     *                                    specified, then this value is ignored, the body is assumed
     *                                    to be HTML, and the altbody is assumed to be plain text
     *       @type array        $attachments array of either absolute filenames to attach
     *                                    to the mail or array of arays in format
     *                                    array($string,$filename,$encoding,$type)
     *       @type array        $stringattachments array of arrays to treat as attachments, format array($string,$filename,$encoding,$type)
     *       @type array        $embeddedimages array of absolute filenames to image files to embed in the mail
     *                       }
     *
     * @throws \RuntimeException Thrown if there's an error sending the e-mail message
     *
     * @return bool true if successful, false otherwise
     */
    public function sendmessage($args)
    {
        // Development mailer mode
        if ($this->getVar('mailertype') == 'test') {
            $output = '';
            foreach ($args as $key => $value) {
                $output .= '<b>'.$key.'</b>: '.$value.'<br />';
            }
            LogUtil::registerStatus($output);
            return true;
        }

        if (!isset($args['fromaddress'])) {
            $args['fromaddress'] = \System::getVar('adminmail');
        }
           
        // Check for installed advanced Mailer module
        $event = new \Zikula\Core\Event\GenericEvent($this, $args);
        $this->eventManager->dispatch('module.mailer.api.sendmessage', $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        // include php mailer class file
        require_once "system/Zikula/Module/MailerModule/vendor/class.phpmailer.php";

        // create new instance of mailer class
        $mail = new PHPMailer();

        // set default message parameters
        $mail->PluginDir = "system/Zikula/Module/MailerModule/vendor/";
        $mail->ClearAllRecipients();
        $mail->ContentType = isset($args['contenttype']) ? $args['contenttype'] : $this->getVar('contenttype');
        $mail->CharSet     = isset($args['charset'])     ? $args['charset']     : $this->getVar('charset');
        $mail->Encoding    = isset($args['encoding'])    ? $args['encoding']    : $this->getVar('encoding');
        $mail->WordWrap    = $this->getVar('wordwrap');

        // load the language file
        $mail->SetLanguage('en', $mail->PluginDir . 'language/');

        // get MTA configuration
        if ($this->getVar('mailertype') == 'smtp') {
            $mail->IsSMTP();  // set mailer to use SMTP
            $mail->Host = $this->getVar('smtpserver');  // specify server
            $mail->Port = $this->getVar('smtpport');    // specify port
        } elseif ($this->getVar('mailertype') == 'gmail') {
            $mail->IsQMail();  // set mailer to use QMail
        } elseif ($this->getVar('mailertype') == 'sendmail') {
            ini_set("sendmail_from", $args['fromaddress']);
            $mail->IsSendMail();  // set mailer to use SendMail
            $mail->Sendmail = $this->getVar('sendmailpath'); // specify Sendmail path
        } else {
            $mail->IsMail();  // set mailer to use php mail
        }

        // set authentication parameters if required
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
        $mail->From     = $args['fromaddress'];

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
            if (SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
                throw new \RuntimeException($args['errorinfo']);
            } else {
                throw new \RuntimeException(__('Error! A problem occurred while sending the e-mail message.'));
            }
        }

        // send message using Swiftmailer
        $message = Swift_Message::newInstance()
            ->setSubject($args['subject'])
            ->setFrom(System::getVar('adminmail'))
            ->setTo($args['toaddress'], $args['toname'])
            ->setBody($args['body']);
        \ServiceUtil::get('mailer')->send($message);

        return true; // message sent
    }
}
