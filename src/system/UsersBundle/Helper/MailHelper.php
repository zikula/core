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

namespace Zikula\UsersBundle\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Zikula\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\UsersBundle\Entity\User;

class MailHelper
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Environment $twig,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $mailLogger, // $mailLogger var name auto-injects the mail channel handler
        private readonly Security $security,
        private readonly SiteDefinitionInterface $site,
        private readonly ?string $registrationNotificationEmail,
        private readonly bool $mailLoggingEnabled
    ) {
    }

    /**
     * Creates a new registration mail.
     *
     * @param bool   $userNotification       Whether the user should be notified of the new registration or not; however
     *                                       if the user's password was created for him, then he will receive at
     *                                       least that mail without regard to this setting
     * @param bool   $adminNotification      Whether the configured administrator mail e-mail address should be
     *                                       sent mail of the new registration
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by an
     *                                       administrator (but not by the user himself)
     *
     * @return array of errors created from the mail process
     */
    public function createAndSendRegistrationMail(
        User $userEntity,
        bool $userNotification = true,
        bool $adminNotification = true,
        string $passwordCreatedForUser = ''
    ): array {
        $mailErrors = [];
        $rendererArgs = [];
        $rendererArgs['user'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['createdByAdmin'] = $this->security->isGranted('ROLE_ADMIN');

        if (!empty($passwordCreatedForUser) || ($userNotification && $userEntity->isApproved())) {
            $mailSent = $this->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->translator->trans('Warning! The welcoming email for the new registration could not be sent.', [], 'mail');
            }
        }
        if ($adminNotification) {
            // send notification email to inform admin about registration
            if (!empty($this->registrationNotificationEmail)) {
                $rendererArgs['isVerified'] = false; // TODO replace ZAuth data
                $mailSent = $this->sendNotification($this->registrationNotificationEmail, 'regadminnotify', $rendererArgs);
                if (!$mailSent) {
                    $mailErrors[] = $this->translator->trans('Warning! The notification email for the new registration could not be sent.', [], 'mail');
                }
            }
        }

        return $mailErrors;
    }

    /**
     * Creates a new users mail.
     *
     * @param bool  $userNotification        Whether the user should be notified of the new registration or not;
     *                                       however if the user's password was created for him, then he will
     *                                       receive at least that mail without regard to this setting
     * @param bool $adminNotification        Whether the configured administrator mail e-mail address should
     *                                       be sent mail of the new registration
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by
     *                                       an administrator (but not by the user himself)
     *
     * @return array of mail errors
     */
    public function createAndSendUserMail(
        User $userEntity,
        bool $userNotification = true,
        bool $adminNotification = true,
        string $passwordCreatedForUser = ''
    ): array {
        $mailErrors = [];
        $rendererArgs = [];
        $rendererArgs['user'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['createdByAdmin'] = $this->security->isGranted('ROLE_ADMIN');

        if ($userNotification || !empty($passwordCreatedForUser)) {
            $mailSent = $this->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->translator->trans('Warning! The welcoming email for the newly created user could not be sent.', [], 'mail');
            }
        }
        if ($adminNotification) {
            // send notification email to inform admin about registration
            if (!empty($this->registrationNotificationEmail)) {
                $authMapping = $this->authenticationMappingRepository->getByZikulaId($userEntity->getUid());
                $rendererArgs['isVerified'] = null === $authMapping || $authMapping->isVerifiedEmail();

                $subject = $this->translator->trans('New registration: %userName%', ['%userName%' => $userEntity->getUname()]);
                $mailSent = $this->sendNotification($this->registrationNotificationEmail, 'regadminnotify', $rendererArgs, $subject);
                if (!$mailSent) {
                    $mailErrors[] = $this->translator->trans('Warning! The notification email for the newly created user could not be sent.', [], 'mail');
                }
            }
        }

        return $mailErrors;
    }

    /**
     * Send same mail to selected user(s). If more than one user, BCC and batch size are used.
     *
     * @param User[] $users
     * @param array $messageData
     *  required keys
     *      'replyto'
     *      'from'
     *      'message'
     *      'subject'
     *      'batchsize'
     *      'format'
     */
    public function mailUsers(array $users, array $messageData): bool
    {
        $sender = new Address($messageData['replyto'], $messageData['from']);
        $email = (new Email())
            ->from($sender)
            ->subject($messageData['subject'])
            ->html($messageData['message'])
        ;
        if (1 === count($users)) {
            $email->to(new Address($users[0]->getEmail(), $users[0]->getUname()));
        } else {
            $email->to($sender);
        }
        try {
            if (1 < count($users)) {
                $bcc = [];
                foreach ($users as $user) {
                    if (!$user->getEmail()) {
                        continue;
                    }
                    $bcc[] = new Address($user->getEmail(), $user->getUname());
                    if (count($bcc) === $messageData['batchsize']) {
                        $email->bcc(...$bcc);
                        $this->mailer->send($email);
                        $bcc = [];
                    }
                }
                $email->bcc(...$bcc);
            }
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->mailLogger->error($exception->getMessage(), [
                'in' => __METHOD__
            ]);

            return false;
        }
        if ($this->mailLoggingEnabled) {
            $this->mailLogger->info(sprintf('Email sent to %s', 'multiple users'), [
                'in' => __METHOD__,
                'users' => array_reduce($users, function ($result, User $user) { return $result . $user->getEmail() . ','; }, 'emails: ')
            ]);
        }

        return true;
    }

    /**
     * Sends a notification e-mail of a specified type to a user or registrant.
     *
     * @param string $toAddress The destination e-mail address
     * @param string $notificationType The type of notification, converted to the name of a template
     *                                     in the form users_userapi_{type}mail.tpl and/or .txt
     * @param array $templateArgs One or more arguments to pass to the renderer for use in the template
     * @param string $subject The e-mail subject, overriding the template's subject
     */
    public function sendNotification(
        string $toAddress,
        string $notificationType = '',
        array $templateArgs = [],
        string $subject = ''
    ): bool {
        $templateName = "@ZikulaUsersBundle/Email/{$notificationType}.html.twig";
        try {
            $html = true;
            $htmlBody = $this->twig->render($templateName, $templateArgs);
        } catch (LoaderError $e) {
            $html = false;
            $htmlBody = '';
        }

        $templateName = "@ZikulaUsersBundle/Email/{$notificationType}.txt.twig";
        try {
            $textBody = $this->twig->render($templateName, $templateArgs);
        } catch (LoaderError $e) {
            $textBody = '';
        }

        if (empty($subject)) {
            $subject = $this->generateEmailSubject($notificationType, $templateArgs);
        }

        $email = (new Email())
            ->from(new Address($this->site->getAdminMail(), $this->site->getName()))
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
            $this->mailLogger->error($exception->getMessage(), [
                'in' => __METHOD__,
                'type' => $notificationType
            ]);

            return false;
        }
        if ($this->mailLoggingEnabled) {
            $this->mailLogger->info(sprintf('Email sent to %s', $toAddress), [
                'in' => __METHOD__,
                'type' => $notificationType
            ]);
        }

        return true;
    }

    private function generateEmailSubject(string $notificationType, array $templateArgs = []): string
    {
        $siteName = $this->site->getName();
        switch ($notificationType) {
            case 'regadminnotify':
                if (!$templateArgs['user']->isApproved()) {
                    return $this->translator->trans('New registration pending approval: %userName%', ['%userName%' => $templateArgs['user']['uname']], 'mail');
                }
                if (isset($templateArgs['isVerified']) && !$templateArgs['isVerified']) {
                    return $this->translator->trans('New registration pending email verification: %userName%', ['%userName%' => $templateArgs['user']['uname']], 'mail');
                }

                return $this->translator->trans('New user activated: %userName%', ['%userName%' => $templateArgs['user']['uname']], 'mail');

            case 'regdeny':
                return $this->translator->trans('Your recent request at %siteName%.', ['%siteName%' => $siteName], 'mail');

            case 'welcome':
                return $this->translator->trans('Welcome to %siteName%, %userName%!', ['%siteName%' => $siteName, '%userName%' => $templateArgs['user']['uname']], 'mail');

            default:
                return $this->translator->trans('A message from %siteName%.', ['%siteName%' => $siteName], 'mail');
        }
    }
}
