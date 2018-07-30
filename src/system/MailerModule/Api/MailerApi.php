<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Api;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swift_Attachment;
use Swift_DependencyContainer;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;
use Zikula\MailerModule\MailerEvents;

/**
 * Class MailerApi.
 *
 * This class manages the sending of mails using SwiftMailer.
 * It should be used instead of the old sendmessage() method in user api.
 */
class MailerApi implements MailerApiInterface
{
    use TranslatorTrait;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var bool
     */
    private $isInstalled;

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
     * MailerApi constructor.
     *
     * @param bool $isInstalled Installed flag
     * @param ZikulaHttpKernelInterface $kernel Kernel service instance
     * @param TranslatorInterface $translator Translator service instance
     * @param EventDispatcherInterface $eventDispatcher EventDispatcher service instance
     * @param DynamicConfigDumper $configDumper Configuration dumper for retrieving SwiftMailer configuration parameters
     * @param VariableApiInterface $variableApi VariableApi service instance
     * @param Swift_Mailer $mailer
     */
    public function __construct(
        $isInstalled,
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        DynamicConfigDumper $configDumper,
        VariableApiInterface $variableApi,
        Swift_Mailer $mailer
    ) {
        $this->isInstalled = $isInstalled;
        $this->kernel = $kernel;
        $this->setTranslator($translator);
        $this->eventDispatcher = $eventDispatcher;
        $this->mailer = $mailer;

        if (!$this->isInstalled) {
            return;
        }

        $mailerParams = $configDumper->getConfiguration('swiftmailer');
        $modVars = $variableApi->getAll('ZikulaMailerModule');
        $this->dataValues = array_merge($mailerParams, $modVars);
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage(Swift_Message $message, $subject = null, $body = null, $altBody = '', $html = false, array $headers = [], array $attachments = [], array $stringAttachments = [], array $embeddedImages = [])
    {
        if (!$this->isInstalled) {
            return;
        }

        $this->message = $message;

        $event = new GenericEvent($this->message);
        $this->eventDispatcher->dispatch(MailerEvents::SEND_MESSAGE_START, $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        $this->setTechnicalParameters();

        // add any custom headers
        if (count($headers)) {
            foreach ($headers as $header => $value) {
                if (is_numeric($header)) {
                    $header = $value;
                    $value = null;
                }
                $this->message->getHeaders()->addTextHeader($header, $value);
            }
        }

        // add message subject
        if (isset($subject)) {
            $this->message->setSubject($subject);
        } else {
            if ('' == $message->getSubject() || null === $message->getSubject()) {
                throw new \RuntimeException('There is no subject set.');
            }
        }

        // add body with formatting
        $bodyFormat = 'text/plain';
        if (!empty($altBody) || ((bool)$html) || $this->dataValues['html']) {
            $bodyFormat = 'text/html';
        }
        if (isset($body)) {
            $this->message->setBody($body);
        } else {
            if ('' == $message->getBody() || null === $message->getBody()) {
                throw new \RuntimeException('There is no message body set.');
            }
        }

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
        return $this->performSending();
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
     * @param array $attachments List of attachments to add
     */
    private function addAttachments(array $attachments)
    {
        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                if (4 != count($attachment)) {
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
     * @param array $attachments List of string attachments to add
     */
    private function addStringAttachments(array $attachments)
    {
        foreach ($attachments as $attachment) {
            if (is_array($attachment) && 4 == count($attachment)) {
                $this->message->attach(Swift_Attachment::fromPath($attachment[0], $attachment[3])->setFilename($attachment[1]));
            }
        }
    }

    /**
     * Adds given embedded images to the current message object.
     *
     * @param array $embeddedImages List of embedded images to add
     */
    private function addEmbeddedImages(array $embeddedImages)
    {
        foreach ($embeddedImages as $embeddedImage) {
            $this->message->attach(Swift_Attachment::fromPath($embeddedImage['path'], $embeddedImage['type'])->setFilename($embeddedImage['name']));
        }
    }

    /**
     * Does the actual sending of the current message.
     *
     * @return bool true if successful
     */
    private function performSending()
    {
        $logFile = $this->kernel->getLogDir() . '/mailer.log';
        $event = new GenericEvent($this->message);

        $failedEmails = [];
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

            //throw new \RuntimeException($this->__('Error! A problem occurred while sending the e-mail message.'));

            return false;
        }

        if ($this->dataValues['enableLogging']) {
            // access the logging channel
            $logger = new Logger('mailer');
            $logger->pushHandler(new StreamHandler($logFile, Logger::INFO));
            $logger->addInfo('Message sent: ' . $this->message->toString());
        }

        $this->eventDispatcher->dispatch(MailerEvents::SEND_MESSAGE_SUCCESS, $event);

        return true;
    }
}
