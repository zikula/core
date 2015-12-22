<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\MailerModule\Api;

use LogUtil;
use Zikula;
use System;
use SecurityUtil;
use Swift_Message;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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
     *                                    to the mail or array of arrays in format
     *                                    array($path,$filename,$encoding,$type)
     *       @type array        $stringattachments array of arrays to treat as attachments, format array($string,$filename,$encoding,$type)
     *       @type array        $embeddedimages array of absolute filenames to image files to embed in the mail
     *                       }
     *
     * @throws \RuntimeException Thrown if there's an error sending the e-mail message
     *
     * @return bool true if successful
     */
    public function sendmessage($args)
    {
        $dumper = $this->getContainer()->get('zikula.dynamic_config_dumper');
        $params = $dumper->getConfiguration('swiftmailer');

        // Development mailer mode
        if ($params['transport'] == 'test') {
            $output = '<p>';
            foreach ($args as $key => $value) {
                if ($key == 'password') {
                    // do not expose the password (#2149)
                    continue;
                }
                $output .= '<strong>'.$key.'</strong>: '.$value.'<br />';
            }
            $output .= '</p>';
            LogUtil::registerStatus($output);

            return true;
        }

        // Allow other bundles to control mailer behavior
        $event = new \Zikula\Core\Event\GenericEvent($this, $args);
        $this->eventManager->dispatch('module.mailer.api.sendmessage', $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        // create new instance of mailer class
        $message = Swift_Message::newInstance();
        $message->setCharset($this->getVar('charset'));
        $message->setMaxLineLength($this->getVar('wordwrap'));
        $encoderKeys = array(
            '8bit' => '8bitcontentencoder',
            '7bit' => '7bitcontentencoder',
            'binary' => '8bitcontentencoder', // no comparable encoding in SwiftMailer AFAICS
            'base64' => 'base64contentencoder',
            'quoted-printable' => 'qpcontentencoder'
        );
        $encoderKey = $encoderKeys[$this->getVar('encoding')];
        $encoder = \Swift_DependencyContainer::getInstance()->lookup('mime.' . $encoderKey);
        $message->setEncoder($encoder);

        // set fromname and fromaddress, default to 'sitename' and 'adminmail' config vars
        $fromname = (isset($args['fromname']) && $args['fromname']) ? $args['fromname'] : System::getVar('sitename');
        $fromaddress = (isset($args['fromaddress'])) ? $args['fromaddress'] : System::getVar('adminmail');
        $message->setFrom($fromaddress, $fromname);

        // add any to addresses
        if (is_array($args['toaddress'])) {
            $toAdds = array();
            foreach ($args['toaddress'] as $key => $address) {
                $toAdds[] = array($address => isset($args['toname'][$key]) ? $args['toname'][$key] : $address);
            }
            $message->setTo($toAdds);
        } else {
            $message->setTo($args['toaddress'], isset($args['toname']) ? $args['toname'] : $args['toaddress']);
        }

        // if replytoname and replytoaddress have been provided us them else use the fromname and fromaddress we built earlier
        $args['replytoname'] = (!isset($args['replytoname']) || empty($args['replytoname'])) ? $fromname : $args['replytoname'];
        $args['replytoaddress'] = (!isset($args['replytoaddress'])  || empty($args['replytoaddress'])) ? $fromaddress : $args['replytoaddress'];
        $message->setReplyTo($args['replytoaddress'], $args['replytoname']);

        // add any cc addresses
        if (isset($args['cc']) && is_array($args['cc'])) {
            foreach ($args['cc'] as $email) {
                if (isset($email['name'])) {
                    $message->addCc($email['address'], $email['name']);
                } else {
                    $message->addCc($email['address']);
                }
            }
        }

        // add any bcc addresses
        if (isset($args['bcc']) && is_array($args['bcc'])) {
            foreach ($args['bcc'] as $email) {
                if (isset($email['name'])) {
                    $message->addBcc($email['address'], $email['name']);
                } else {
                    $message->addBcc($email['address']);
                }
            }
        }

        // add any custom headers
        if (isset($args['headers']) && is_string($args['headers'])) {
            $args['headers'] = explode("\n", $args['headers']);
        }
        if (isset($args['headers']) && is_array($args['headers'])) {
            $headers = $message->getHeaders();
            foreach ($args['headers'] as $header) {
                if (is_array($header)) {
                    $headers->addTextHeader($header[0], $header[1]);
                } else {
                    $headers->addTextHeader($header);
                }
            }
        }

        // add message subject
        $message->setSubject($args['subject']);

        // add body with formatting
        if ((!empty($args['altbody']))
            || ((isset($args['html']) && is_bool($args['html']) && $args['html'])
            || $this->getVar('html'))) {
            $bodyFormat = 'text/html';
        } else {
            $bodyFormat = 'text/plain';
        }
        $message->setBody($args['body']);
        $message->setContentType($bodyFormat);
        if (!empty($args['altbody'])) {
            $message->addPart($args['altbody'], 'text/plain');
        }

        // add attachments
        if (isset($args['attachments']) && !empty($args['attachments'])) {
            foreach ($args['attachments'] as $attachment) {
                if (is_array($attachment)) {
                    if (count($attachment) != 4) {
                        // skip invalid arrays
                        continue;
                    }
                    $message->attach(\Swift_Attachment::fromPath($attachment[0], $attachment[3])->setFilename($attachment[1]));
                } else {
                    $message->attach(\Swift_Attachment::fromPath($attachment));
                }
            }
        }

        // add string attachments
        if (isset($args['stringattachments']) && !empty($args['stringattachments'])) {
            foreach ($args['stringattachments'] as $attachment) {
                if (is_array($attachment) && count($attachment) == 4) {
                    $message->attach(\Swift_Attachment::fromPath($attachment[0], $attachment[3])->setFilename($attachment[1]));
                }
            }
        }

        // add embedded images
        if (isset($args['embeddedimages']) && !empty($args['embeddedimages'])) {
            foreach ($args['embeddedimages'] as $embeddedimage) {
                $message->attach(\Swift_Attachment::fromPath($embeddedimage['path'], $embeddedimage['type'])->setFilename($embeddedimage['name']));
            }
        }

        // send message
        /** @var $mailer \Swift_Mailer */
        $mailer = $this->get('mailer');
        if (!$mailer->send($message, $failedEmails)) {
            // message was not sent successfully
            $emailList = implode(', ', $failedEmails);
            $args['errorinfo'] = $this->__f('Error! Could not send mail to: %s.', $emailList);
            if ($this->getVar('enableLogging')) {
                // access the logging channel
                $logger = new Logger('mailer');
                $logger->pushHandler(new StreamHandler('app/logs/mailer.log', Logger::INFO));
                $logger->addError("Could not send message to: $emailList :: " . $message->toString());
            }
            LogUtil::log(__f('Error! A problem occurred while sending an e-mail message from \'%1$s\' (%2$s) to (%3$s) (%4$s) with the subject line \'%5$s\': %6$s', $args));
            if (SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
                throw new \RuntimeException($args['errorinfo']);
            } else {
                throw new \RuntimeException($this->__('Error! A problem occurred while sending the e-mail message.'));
            }
        }

        if ($this->getVar('enableLogging')) {
            // access the logging channel
            $logger = new Logger('mailer');
            $logger->pushHandler(new StreamHandler('app/logs/mailer.log', Logger::INFO));
            $logger->addInfo('Message Sent: ' . $message->toString());
        }

        return true; // message has been sent
    }
}
