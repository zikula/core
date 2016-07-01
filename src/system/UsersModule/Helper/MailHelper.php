<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\MailerModule\Api\MailerApi;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;

class MailHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var MailerApi
     */
    private $mailerApi;

    /**
     * MailHelper constructor.
     * @param TranslatorInterface $translator
     * @param \Twig_Environment $twig
     * @param VariableApi $variableApi
     * @param MailerApi $mailerApi
     */
    public function __construct(TranslatorInterface $translator, \Twig_Environment $twig, VariableApi $variableApi, MailerApi $mailerApi)
    {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->variableApi = $variableApi;
        $this->mailerApi = $mailerApi;
    }

    /**
     * Send same mail to selected user(s). If more than one user, BCC and batchsize used.
     * @param UserEntity[] $users
     * @param array $messageData
     *  required keys
     *      'replyto'
     *      'from'
     *      'message'
     *      'subject'
     *      'batchsize'
     *      'format'
     * @return bool
     */
    public function mailUsers(array $users, array $messageData)
    {
        $mailSent = false;
        $message = \Swift_Message::newInstance();
        $message->setFrom([$messageData['replyto'] => $messageData['from']]);
        if (count($users) == 1) {
            $message->setTo([$users[0]->getEmail() => $users[0]->getUname()]);
        } else {
            $message->setTo([$messageData['replyto'] => $messageData['from']]);
        }
        $message->setSubject($messageData['subject']);
        $message->setBody($messageData['message']);
        if (count($users) > 1) {
            $bcc = [];
            foreach ($users as $user) {
                $bcc[] = $user->getEmail();
                if (count($bcc) == $messageData['batchsize']) {
                    $message->setBcc($bcc);
                    $mailSent = $mailSent && $this->mailerApi->sendMessage($message, null, null, '', $messageData['format'] == 'html');
                    $bcc = [];
                }
            }
            $message->setBcc($bcc);
        }
        $mailSent = $mailSent && $this->mailerApi->sendMessage($message, null, null, '', $messageData['format'] == 'html');

        return $mailSent;
    }

    /**
     * Sends a notification e-mail of a specified type to a user or registrant.
     *
     * @param string $toAddress The destination e-mail address.
     * @param string $notificationType The type of notification, converted to the name of a template
     *                                     in the form users_userapi_{type}mail.tpl and/or .txt.
     * @param array $templateArgs One or more arguments to pass to the renderer for use in the template.
     * @param string $subject The e-mail subject, overriding the template's subject.
     * @return bool
     */
    public function sendNotification($toAddress, $notificationType = '', array $templateArgs = [], $subject = '')
    {
        $html = false;

        $templateName = "@ZikulaUsersModule/Email/{$notificationType}.html.twig";
        try {
            $html = true;
            $htmlBody = $this->twig->render($templateName, $templateArgs);
        } catch (\Twig_Error_Loader $e) {
            $htmlBody = '';
        }

        $templateName = "@ZikulaUsersModule/Email/{$notificationType}.txt.twig";
        try {
            $textBody = $this->twig->render($templateName, $templateArgs);
        } catch (\Twig_Error_Loader $e) {
            $textBody = '';
        }

        if (empty($subject)) {
            $subject = $this->generateEmailSubject($notificationType, $templateArgs);
        }

        $message = \Swift_Message::newInstance();
        $message->setFrom([$this->variableApi->get(VariableApi::CONFIG, 'adminmail') => $this->variableApi->get(VariableApi::CONFIG, 'sitename_' . \ZLanguage::getLanguageCode())]);
        $message->setTo([$toAddress]);
        $message->setSubject($subject);
        $message->setBody($html ? $htmlBody : $textBody);

        return $this->mailerApi->sendMessage($message, null, null, $textBody, $html);
    }

    private function generateEmailSubject($notificationType, array $templateArgs = [])
    {
        $siteName = $this->variableApi->get(VariableApi::CONFIG, 'sitename');
        switch ($notificationType) {
            case 'regadminnotify':
                if ($templateArgs['reginfo']['isapproved']) {
                    return $this->translator->__f('New registration pending approval: %s', ['%s' => $templateArgs['reginfo']['uname']]);
                } else {
                    return $this->translator->__f('New user activated: %s', ['%s' => $templateArgs['reginfo']['uname']]);
                }
                break;
            case 'regdeny':
                return $this->translator->__f('Your recent request at %s.', ['%s' => $siteName]);
                break;
            case 'welcome':
                return $this->translator->__f('Welcome to %1$s, %2$s!', ['%1$s' => $siteName, '%2$s' => $templateArgs['reginfo']['uname']]);
                break;
            default:
                return $this->translator->__f('A message from %s.', ['%s' => $siteName]);
        }
    }
}
