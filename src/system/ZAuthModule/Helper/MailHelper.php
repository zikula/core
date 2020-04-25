<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

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
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $loggingEnabled;

    /**
     * @var SiteDefinitionInterface
     */
    private $site;

    public function __construct(
        TranslatorInterface $translator,
        Environment $twig,
        VariableApiInterface $variableApi,
        MailerInterface $mailer,
        LoggerInterface $mailLogger, // $mailLogger var name auto-injects the mail channel handler
        SiteDefinitionInterface $site
    ) {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->variableApi = $variableApi;
        $this->mailer = $mailer;
        $this->logger = $mailLogger;
        $this->site = $site;
        $this->loggingEnabled = $variableApi->get('ZikulaMailerModule', 'enableLogging', false);
    }

    /**
     * Sends a notification e-mail of a specified type to a user or registrant.
     */
    public function sendNotification(
        string $toAddress,
        string $notificationType = '',
        array $templateArgs = [],
        string $subject = ''
    ): bool {
        //Set translation domain to avoid problems when calling sendNotification from external modules
        $templateName = '@ZikulaZAuthModule/Email/' . $notificationType . '.html.twig';
        try {
            $html = true;
            $htmlBody = $this->twig->render($templateName, $templateArgs);
        } catch (LoaderError $exception) {
            $html = false;
            $htmlBody = '';
        }

        $templateName = '@ZikulaZAuthModule/Email/' . $notificationType . '.txt.twig';
        try {
            $textBody = $this->twig->render($templateName, $templateArgs);
        } catch (LoaderError $exception) {
            $textBody = '';
        }

        if (empty($subject)) {
            $subject = $this->generateEmailSubject($notificationType);
        }

        $siteName = $this->site->getName();

        $email = (new Email())
            ->from(new Address($this->variableApi->getSystemVar('adminmail'), $siteName))
            ->to($toAddress)
            ->subject($subject)
            ->text($textBody)
        ;
        if ($html) {
            $email->html($htmlBody);
        }
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error($exception->getMessage(), [
                'in' => __METHOD__,
            ]);

            return false;
        }
        if ($this->loggingEnabled) {
            $this->logger->info(sprintf('Email sent to %', $toAddress), [
                'in' => __METHOD__,
            ]);
        }

        return true;
    }

    private function generateEmailSubject(string $notificationType): string
    {
        $siteName = $this->site->getName();
        switch ($notificationType) {
            case 'importnotify':
                return $this->translator->trans('Welcome to %siteName%!', ['%siteName%' => $siteName], 'mail');

            case 'lostpassword':
                return $this->translator->trans('Reset your password at %siteName%', ['%siteName%' => $siteName], 'mail');

            case 'lostuname':
                return $this->translator->trans('\'%siteName%\' account information', ['%siteName%' => $siteName], 'mail');

            case 'regverifyemail':
                return $this->translator->trans('Verify your e-mail address for %siteName%.', ['%siteName%' => $siteName], 'mail');

            case 'userverifyemail':
                return $this->translator->trans('Verify your request to change your e-mail address at %siteName%', ['%siteName%' => $siteName], 'mail');

            default:
                return $this->translator->trans('A message from %siteName%.', ['%siteName%' => $siteName], 'mail');
        }
    }
}
