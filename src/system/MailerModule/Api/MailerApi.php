<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Api;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Swift_Attachment;
use Swift_DependencyContainer;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\MailerModule\MailerEvents;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * Class MailerApi.
 *
 * This class manages the sending of mails using SwiftMailer.
 * It should be used instead of the old sendmessage() method in user api.
 */
class MailerApi
{
    use TranslatorTrait;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Swift_Message
     */
    private $message;

    /**
     * @var array
     */
    protected $dataValues;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * MailerApi constructor.
     *
     * @param TranslatorInterface      $translator      Translator service instance.
     * @param EventDispatcherInterface $eventDispatcher EventDispatcher service instance.
     * @param DynamicConfigDumper      $configDumper    Configuration dumper for retrieving SwiftMailer configuration parameters.
     * @param VariableApi              $variableApi     VariableApi service instance.
     * @param PermissionApi            $permissionApi   PermissionApi service instance.
     */
    public function __construct(
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        DynamicConfigDumper $configDumper,
        VariableApi $variableApi,
        Swift_Mailer $mailer,
        PermissionApi $permissionApi)
    {
        $this->setTranslator($translator);
        $this->eventDispatcher = $eventDispatcher;
        $this->mailer = $mailer;
        $this->permissionApi = $permissionApi;

        $mailerParams = $configDumper->getConfiguration('swiftmailer');
        $modVars = $variableApi->getAll('ZikulaMailerModule');
        $this->dataValues = array_merge($mailerParams, $modVars);
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance.
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * API function to send e-mail message.
     * It is assumed that basic parameters for sender and recipient(s) have already been set.
     *
     * @param Swift_Message $message The message object.
     * @param string        $subject message subject
     * @param string        $body message body, if altbody is provided then
     *                            this is the HTML version of the body
     * @param string        $altBody alternative plain-text message body, if specified the
     *                               e-mail will be sent as multipart/alternative
     * @param bool          $html HTML flag, if altbody is not specified then this
     *                            indicates whether body contains HTML or not; if altbody is
     *                            specified, then this value is ignored, the body is assumed
     *                            to be HTML, and the altbody is assumed to be plain text
     * @param array         $headers custom headers to add
     * @param array         $attachments array of either absolute filenames to attach
     *                                   to the mail or array of arrays in format
     *                                   [$path, $filename, $encoding, $type]
     * @param array         $stringAttachments array of arrays to treat as attachments, format [$string, $filename, $encoding, $type]
     * @param array         $embeddedImages array of absolute filenames to image files to embed in the mail
     *
     * @throws \RuntimeException Thrown if there's an error sending the e-mail message
     *
     * @return bool true if successful
     */
    public function sendMessage(Swift_Message $message, $subject, $body, $altBody, $html, array $headers = [], array $attachments = [], array $stringAttachments = [], array $embeddedImages = [])
    {
        $this->message = $message;

        $event = new GenericEvent($this->message);
        $this->eventDispatcher->dispatch(MailerEvents::SEND_MESSAGE_START, $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        $this->setTechnicalParameters();

        // add any custom headers
        if (count($headers)) {
            $headers = $this->message->getHeaders();
            foreach ($headers as $header) {
                if (is_array($header)) {
                    $headers->addTextHeader($header[0], $header[1]);
                } else {
                    $headers->addTextHeader($header);
                }
            }
        }

        // add message subject
        $this->message->setSubject($subject);

        // add body with formatting
        $bodyFormat = 'text/plain';
        if (!empty($altBody) || ((bool) $html) || $this->dataValues['html']) {
            $bodyFormat = 'text/html';
        }
        $this->message->setBody($body);
        $this->message->setContentType($bodyFormat);
        if (!empty($altBody)) {
            $this->message->addPart($altBody, 'text/plain');
        }

        if (count($attachments)) {
            $this->addAttachments($attachments);
        }
        if (count($stringAttachments)) {
            $this->addStringAttachments($stringAttachments);
        }
        if (count($embeddedImages)) {
            $this->addEmbeddedImages($embeddedImages);
        }

        $event = new GenericEvent($this->message);
        $this->eventDispatcher->dispatch(MailerEvents::SEND_MESSAGE_PERFORM, $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        // send message
        $this->performSending();

        return true; // message has been sent
    }

    /**
     * Defines technical parameters for the current message.
     */
    private function setTechnicalParameters()
    {
        $this->message->setCharset($this->dataValues['charset']);
        $this->message->setMaxLineLength($this->dataValues['wordwrap']);

        $encoderKeys = [
            '8bit' => '8bitcontentencoder',
            '7bit' => '7bitcontentencoder',
            'binary' => '8bitcontentencoder', // no comparable encoding in SwiftMailer AFAICS
            'base64' => 'base64contentencoder',
            'quoted-printable' => 'qpcontentencoder'
        ];
        $encoderKey = $encoderKeys[$this->dataValues['encoding']];
        $encoder = Swift_DependencyContainer::getInstance()->lookup('mime.' . $encoderKey);
        $this->message->setEncoder($encoder);
    }

    /**
     * Adds given attachments to the current message object.
     *
     * @param array $attachments List of attachments to add.
     */
    private function addAttachments(array $attachments)
    {
        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                if (count($attachment) != 4) {
                    // skip invalid arrays
                    continue;
                }
                $this->message->attach(Swift_Attachment::fromPath($attachment[0], $attachment[3])->setFilename($attachment[1]));
            } else {
                $this->message->attach(Swift_Attachment::fromPath($attachment));
            }
        }
    }

    /**
     * Adds given string attachments to the current message object.
     *
     * @param array $attachments List of string attachments to add.
     */
    private function addStringAttachments(array $attachments)
    {
        foreach ($attachments as $attachment) {
            if (is_array($attachment) && count($attachment) == 4) {
                $this->message->attach(Swift_Attachment::fromPath($attachment[0], $attachment[3])->setFilename($attachment[1]));
            }
        }
    }

    /**
     * Adds given embedded images to the current message object.
     *
     * @param array $embeddedImages List of embedded images to add.
     */
    private function addEmbeddedImages(array $embeddedImages)
    {
        foreach ($embeddedImages as $embeddedImage) {
            $this->message->attach(Swift_Attachment::fromPath($embeddedImage['path'], $embeddedImage['type'])->setFilename($embeddedImage['name']));
        }
    }

    /**
     * Does the actual sending of the current message.
     */
    private function performSending()
    {
        $logFile = 'app/logs/mailer.log';
        $event = new GenericEvent($this->message);

        if (!$this->mailer->send($this->message, $failedEmails)) {
            // message was not sent successfully, do error handling

            $emailList = implode(', ', $failedEmails);

            if ($this->dataValues['enableLogging']) {
                // access the logging channel
                $logger = new Logger('mailer');
                $logger->pushHandler(new StreamHandler($logFile, Logger::INFO));
                $logger->addError("Could not send message to: $emailList :: " . $this->message->toString());
            }

            $this->eventDispatcher->dispatch(MailerEvents::SEND_MESSAGE_FAILURE, $event);

            if ($this->permissionApi->hasPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
                throw new \RuntimeException($this->__f('Error! Could not send mail to: %s.', ['%s' => $emailList]));
            } else {
                throw new \RuntimeException($this->__('Error! A problem occurred while sending the e-mail message.'));
            }
        }

        if ($this->dataValues['enableLogging']) {
            // access the logging channel
            $logger = new Logger('mailer');
            $logger->pushHandler(new StreamHandler($logFile, Logger::INFO));
            $logger->addInfo('Message sent: ' . $this->message->toString());
        }

        $this->eventDispatcher->dispatch(MailerEvents::SEND_MESSAGE_SUCCESS, $event);
    }
}
