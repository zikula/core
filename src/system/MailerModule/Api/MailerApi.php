<?php

declare(strict_types=1);

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
use RuntimeException;
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
 * Mailer Api class managing the sending of mails using SwiftMailer.
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
    private $installed;

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

    public function __construct(
        bool $installed,
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        DynamicConfigDumper $configDumper,
        VariableApiInterface $variableApi,
        Swift_Mailer $mailer
    ) {
        $this->installed = $installed;
        $this->kernel = $kernel;
        $this->setTranslator($translator);
        $this->eventDispatcher = $eventDispatcher;
        $this->mailer = $mailer;

        if (!$this->installed) {
            return;
        }

        $mailerParams = $configDumper->getConfiguration('swiftmailer');
        $modVars = $variableApi->getAll('ZikulaMailerModule');
        $this->dataValues = array_merge($mailerParams, $modVars);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function sendMessage(
        Swift_Message $message,
        string $subject = null,
        string $body = null,
        string $altBody = '',
        bool $html = false,
        array $headers = [],
        array $attachments = [],
        array $stringAttachments = [],
        array $embeddedImages = []
    ): bool {
        if (!$this->installed) {
            return false;
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
        }
        if (empty($message->getSubject())) {
            throw new RuntimeException('There is no subject set.');
        }

        // add body with formatting
        $bodyFormat = 'text/plain';
        if (!empty($altBody) || $html || $this->dataValues['html']) {
            $bodyFormat = 'text/html';
        }
        if (isset($body)) {
            $this->message->setBody($body);
        }
        if (empty($message->getBody())) {
            throw new RuntimeException('There is no message body set.');
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
    private function setTechnicalParameters(): void
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
     */
    private function addAttachments(array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                if (4 !== count($attachment)) {
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
     */
    private function addStringAttachments(array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            if (is_array($attachment) && 4 === count($attachment)) {
                $this->message->attach(Swift_Attachment::fromPath($attachment[0], $attachment[3])->setFilename($attachment[1]));
            }
        }
    }

    /**
     * Adds given embedded images to the current message object.
     */
    private function addEmbeddedImages(array $embeddedImages = []): void
    {
        foreach ($embeddedImages as $embeddedImage) {
            $this->message->attach(Swift_Attachment::fromPath($embeddedImage['path'], $embeddedImage['type'])->setFilename($embeddedImage['name']));
        }
    }

    /**
     * Does the actual sending of the current message.
     */
    private function performSending(): bool
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
                $logger->addError("Could not send message to: ${emailList} :: " . $this->message->toString());
            }

            $this->eventDispatcher->dispatch(MailerEvents::SEND_MESSAGE_FAILURE, $event);

            //throw new RuntimeException($this->__('Error! A problem occurred while sending the e-mail message.'));

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
