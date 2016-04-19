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
use Symfony\Component\HttpFoundation\Session\Session;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * Class MailerApi.
 * @package Zikula\MailerModule\Api
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
     * @var Session
     */
    protected $session;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * MailerApi constructor.
     *
     * @param TranslatorInterface      $translator      Translator service instance.
     * @param EventDispatcherInterface $eventDispatcher EventDispatcher service instance.
     * @param DynamicConfigDumper      $configDumper    DynamicConfigDumper service instance.
     * @param VariableApi              $variableApi     VariableApi service instance.
     * @param Session                  $session         Session service instance.
     * @param PermissionApi            $permissionApi   PermissionApi service instance.
     */
    public function __construct(TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher, DynamicConfigDumper $configDumper, VariableApi $variableApi, Swift_Mailer $mailer, Session $session, PermissionApi $permissionApi)
    {
        $this->setTranslator($translator);
        $this->eventDispatcher = $eventDispatcher;
        $this->mailer = $mailer;
        $this->session = $session;
        $this->permissionApi = $permissionApi;

        $params = $configDumper->getConfiguration('swiftmailer');
        $modVars = $variableApi->getAll('ZikulaMailerModule');
        $this->dataValues = array_merge($params, $modVars);
        $this->dataValues['sitename'] = $variableApi->get('ZConfig', 'sitename_' . \ZLanguage::getLanguageCode(), $variableApi->get('ZConfig', 'sitename_en'));
        $this->dataValues['adminmail'] = $variableApi->get('ZConfig', 'adminmail');
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
    public function sendMessage($args)
    {
        // Development mailer mode
        if ($this->dataValues['transport'] == 'test') {
            $output = '<p>';
            foreach ($args as $key => $value) {
                if ($key == 'password') {
                    // do not expose the password (#2149)
                    continue;
                }
                $output .= '<strong>' . $key . '</strong>: ' . $value . '<br />';
            }
            $output .= '</p>';

            $this->session->getFlashBag()->add('status', $output);

            return true;
        }

        // Allow other bundles to control mailer behavior
        $event = new \Zikula\Core\Event\GenericEvent($this, $args);
        $this->eventDispatcher->dispatch('module.mailer.api.sendmessage', $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        // create new instance of mailer class
        $this->message = Swift_Message::newInstance();
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

        // set fromname and fromaddress, default to 'sitename' and 'adminmail' config vars
        $fromname = (isset($args['fromname']) && $args['fromname']) ? $args['fromname'] : $this->dataValues['sitename'];
        $fromaddress = (isset($args['fromaddress'])) ? $args['fromaddress'] : $this->dataValues['adminmail'];
        $this->message->setFrom($fromaddress, $fromname);

        // add any to addresses
        if (is_array($args['toaddress'])) {
            $toAdds = [];
            foreach ($args['toaddress'] as $key => $address) {
                $toAdds[] = [$address => isset($args['toname'][$key]) ? $args['toname'][$key] : $address];
            }
            $this->message->setTo($toAdds);
        } else {
            $this->message->setTo($args['toaddress'], isset($args['toname']) ? $args['toname'] : $args['toaddress']);
        }

        // if replytoname and replytoaddress have been provided us them else use the fromname and fromaddress we built earlier
        $args['replytoname'] = (!isset($args['replytoname']) || empty($args['replytoname'])) ? $fromname : $args['replytoname'];
        $args['replytoaddress'] = (!isset($args['replytoaddress'])  || empty($args['replytoaddress'])) ? $fromaddress : $args['replytoaddress'];
        $this->message->setReplyTo($args['replytoaddress'], $args['replytoname']);

        // add any cc addresses
        if (isset($args['cc']) && is_array($args['cc'])) {
            foreach ($args['cc'] as $email) {
                if (isset($email['name'])) {
                    $this->message->addCc($email['address'], $email['name']);
                } else {
                    $this->message->addCc($email['address']);
                }
            }
        }

        // add any bcc addresses
        if (isset($args['bcc']) && is_array($args['bcc'])) {
            foreach ($args['bcc'] as $email) {
                if (isset($email['name'])) {
                    $this->message->addBcc($email['address'], $email['name']);
                } else {
                    $this->message->addBcc($email['address']);
                }
            }
        }

        // add any custom headers
        if (isset($args['headers']) && is_string($args['headers'])) {
            $args['headers'] = explode("\n", $args['headers']);
        }
        if (isset($args['headers']) && is_array($args['headers'])) {
            $headers = $this->message->getHeaders();
            foreach ($args['headers'] as $header) {
                if (is_array($header)) {
                    $headers->addTextHeader($header[0], $header[1]);
                } else {
                    $headers->addTextHeader($header);
                }
            }
        }

        // add message subject
        $this->message->setSubject($args['subject']);

        // add body with formatting
        if ((!empty($args['altbody']))
            || ((isset($args['html']) && is_bool($args['html']) && $args['html'])
            || $this->dataValues['html'])) {
            $bodyFormat = 'text/html';
        } else {
            $bodyFormat = 'text/plain';
        }
        $this->message->setBody($args['body']);
        $this->message->setContentType($bodyFormat);
        if (!empty($args['altbody'])) {
            $this->message->addPart($args['altbody'], 'text/plain');
        }

        $this->addAttachments($args);

        // send message
        $this->performSending($args);

        return true; // message has been sent
    }

    /**
     * Adds given attachments to the current message object.
     *
     * @param mixed[] $args
     */
    private function addAttachments($args)
    {
        // add attachments
        if (isset($args['attachments']) && !empty($args['attachments'])) {
            foreach ($args['attachments'] as $attachment) {
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

        // add string attachments
        if (isset($args['stringattachments']) && !empty($args['stringattachments'])) {
            foreach ($args['stringattachments'] as $attachment) {
                if (is_array($attachment) && count($attachment) == 4) {
                    $this->message->attach(Swift_Attachment::fromPath($attachment[0], $attachment[3])->setFilename($attachment[1]));
                }
            }
        }

        // add embedded images
        if (isset($args['embeddedimages']) && !empty($args['embeddedimages'])) {
            foreach ($args['embeddedimages'] as $embeddedimage) {
                $this->message->attach(Swift_Attachment::fromPath($embeddedimage['path'], $embeddedimage['type'])->setFilename($embeddedimage['name']));
            }
        }
    }

    /**
     * Does the actual sending of the current message.
     *
     * @param mixed[] $args
     */
    private function performSending($args)
    {
        $logFile = 'app/logs/mailer.log';

        if (!$this->mailer->send($this->message, $failedEmails)) {
            // message was not sent successfully
            $emailList = implode(', ', $failedEmails);
            $args['errorinfo'] = $this->__f('Error! Could not send mail to: %s.', ['%s' => $emailList]);

            if ($this->dataValues['enableLogging']) {
                // access the logging channel
                $logger = new Logger('mailer');
                $logger->pushHandler(new StreamHandler($logFile, Logger::INFO));
                $logger->addError("Could not send message to: $emailList :: " . $this->message->toString());
            }

            if ($this->permissionApi->hasPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
                throw new \RuntimeException($args['errorinfo']);
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
    }
}
