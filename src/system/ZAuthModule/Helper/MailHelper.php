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

namespace Zikula\ZAuthModule\Helper;

use Swift_Message;
use Twig\Environment;
use Twig\Error\LoaderError;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;

class MailHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var MailerApiInterface
     */
    private $mailerApi;

    /**
     * MailHelper constructor.
     * @param TranslatorInterface $translator
     * @param Environment $twig
     * @param VariableApiInterface $variableApi
     * @param MailerApiInterface $mailerApi
     */
    public function __construct(
        TranslatorInterface $translator,
        Environment $twig,
        VariableApiInterface $variableApi,
        MailerApiInterface $mailerApi
    ) {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->variableApi = $variableApi;
        $this->mailerApi = $mailerApi;
    }

    /**
     * Sends a notification e-mail of a specified type to a user or registrant.
     *
     * @param string $toAddress The destination e-mail address
     * @param string $notificationType The type of notification, converted to the name of a template
     *                                     in the form users_userapi_{type}mail.tpl and/or .txt
     * @param array $templateArgs One or more arguments to pass to the renderer for use in the template
     * @param string $subject The e-mail subject, overriding the template's subject
     * @return bool
     */
    public function sendNotification($toAddress, $notificationType = '', array $templateArgs = [], $subject = '')
    {
        $html = false;

        //Set translation domain to avoid problems when calling sendNotification from external modules
        $templateArgs['domain'] = 'zikula';
        $templateName = "@ZikulaZAuthModule/Email/{$notificationType}.html.twig";
        try {
            $html = true;
            $htmlBody = $this->twig->render($templateName, $templateArgs);
        } catch (LoaderError $e) {
            $htmlBody = '';
        }

        $templateName = "@ZikulaZAuthModule/Email/{$notificationType}.txt.twig";
        try {
            $textBody = $this->twig->render($templateName, $templateArgs);
        } catch (LoaderError $e) {
            $textBody = '';
        }

        if (empty($subject)) {
            $subject = $this->generateEmailSubject($notificationType);
        }

        $sitename = $this->variableApi->getSystemVar('sitename', $this->variableApi->getSystemVar('sitename_en'));

        $message = new Swift_Message($subject);
        $message->setFrom([$this->variableApi->getSystemVar('adminmail') => $sitename]);
        $message->setTo([$toAddress]);
        $body = $html ? $htmlBody : $textBody;
        $altBody = $html ? $textBody : '';

        return $this->mailerApi->sendMessage($message, null, $body, $altBody, $html);
    }

    /**
     * @param string $notificationType
     */
    private function generateEmailSubject($notificationType)
    {
        $siteName = $this->variableApi->getSystemVar('sitename');
        switch ($notificationType) {
            case 'importnotify':
                return $this->translator->__f('Welcome to %s!', ['%s' => $siteName], 'zikula');

            case 'lostpassword':
                return $this->translator->__f('Reset your password at \'%s\'', ['%s' => $siteName], 'zikula');

            case 'lostuname':
                return $this->translator->__f('\'%s\' account information', ['%s' => $siteName], 'zikula');

            case 'regverifyemail':
                return $this->translator->__f('Verify your e-mail address for %s.', ['%s' => $siteName], 'zikula');

            case 'userverifyemail':
                return $this->translator->__f('Verify your request to change your e-mail address at \'%s\'', ['%s' => $siteName], 'zikula');

            default:
                return $this->translator->__f('A message from %s.', ['%s' => $siteName], 'zikula');
        }
    }
}
