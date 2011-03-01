<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package SwiftMailer_Plugin
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Mailer class.
 */
class SystemPlugins_SwiftMailer_Mailer
{
    /**
     * Swift Preferences container.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * The Mailer instance.
     * 
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     */
    public function __construct(Zikula_ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->mailer = $serviceManager->getService('mailer');
    }

    /**
     * Send mail.
     *
     * @param array  $from               From address array('john@doe.com' => 'John Doe').
     * @param array  $to                 To address 'receiver@domain.org' or array('other@domain.org' => 'A name').
     * @param string $subject            Subject.
     * @param string $body               Content body.
     * @param array  $contentType        Content type for body, default 'text/plain'.
     * @param array  $cc                 CC to, array('receiver@domain.org', 'other@domain.org' => 'A name').
     * @param array  $bcc                BCC to, array('receiver@domain.org', 'other@domain.org' => 'A name').
     * @param array  $replyTo            Reply to, array('receiver@domain.org', 'other@domain.org' => 'A name').
     * @param mixed  $altBody            Alternate body.
     * @param string $altBodyContentType Alternate content type default 'text/html'.
     * @param array  $header             Associative array of headers array('header1' => 'value1', 'header2' => 'value2').
     * @param array  &$failedRecipients  Array.
     * @param string $charset            Null means leave at default.
     * @param array  $attachments        Array of files.
     *
     * @return integet
     */
    function send(array $from, array $to, $subject, $body, $contentType = 'text/plain', array $cc=null, array $bcc=null, array $replyTo=null, $altBody = null, $altBodyContentType = 'text/html', array $header = array(), &$failedRecipients = array(), $charset=null, array $attachments=array())
    {
        $message = new Swift_Message($subject, $body, $contentType);
        $message->setTo($to);
        $message->setFrom($from);
        if ($attachments) {
            foreach ($attachments as $attachment) {
                $message->attach(Swift_Attachment::fromPath($attachment));
            }
        }
        
        if ($cc) {
            $message->setCc($cc);
        }

        if ($bcc) {
            $message->setBcc($bcc);
        }

        if ($replyTo) {
            $message->setReplyTo($replyTo);
        }

        if ($charset) {
            $message->setCharset($charset);
        }

        if ($altBody) {
            $message->addPart($altBody, $altBodyContentType);
        }

        if ($headers) {
            $headers = $message->getHeaders();
            foreach ($headers as $key => $value) {
                $headers->addTextHeader($key, $value);
            }
        }

        if ($this->serviceManager['swiftmailer.preferences.sendmethod'] == 'normal') {
            return $this->mailer->send($message, $failedRecipients);
        } else if ($this->serviceManager['swiftmailer.preferences.sendmethod'] == 'single_recipient') {
            return $this->mailer->sendBatch($message, $failedRecipients);
        }
    }
}