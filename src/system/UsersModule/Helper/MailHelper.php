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

namespace Zikula\UsersModule\Helper;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

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
     * @var string
     */
    private $registrationNotifyEmail;

    /**
     * @var string
     */
    private $adminEmail;

    /**
     * @var bool
     */
    private $mailLoggingEnabled;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $authenticationMappingRepository;

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
        PermissionApiInterface $permissionApi,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        SiteDefinitionInterface $site
    ) {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->logger = $mailLogger;
        $this->permissionApi = $permissionApi;
        $this->authenticationMappingRepository = $authenticationMappingRepository;
        $this->site = $site;
        $this->registrationNotifyEmail = $variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, '');
        $this->adminEmail = $variableApi->getSystemVar('adminmail');
        $this->mailLoggingEnabled = $variableApi->get('ZikulaMailerModule', 'enableLogging', false);
    }

    /**
     * Creates a new registration mail.
     *
     * @param UserEntity $userEntity
     * @param bool   $userNotification       Whether the user should be notified of the new registration or not; however
     *                                       if the user's password was created for him, then he will receive at
     *                                       least that mail without regard to this setting
     * @param bool   $adminNotification      Whether the configured administrator mail e-mail address should be
     *                                       sent mail of the new registration
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by an
     *                                       administrator (but not by the user himself)
     *
     * @return array of errors created from the mail process
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     * @throws RuntimeException Thrown if the registration couldn't be saved
     */
    public function createAndSendRegistrationMail(
        UserEntity $userEntity,
        bool $userNotification = true,
        bool $adminNotification = true,
        string $passwordCreatedForUser = ''
    ): array {
        $mailErrors = [];
        $rendererArgs = [];
        $rendererArgs['user'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['createdByAdmin'] = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);

        if (!empty($passwordCreatedForUser) || ($userNotification && $userEntity->isApproved())) {
            $mailSent = $this->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->translator->trans('Warning! The welcoming email for the new registration could not be sent.', [], 'mail');
            }
        }
        if ($adminNotification) {
            // mail notify email to inform admin about registration
            if (!empty($this->registrationNotifyEmail)) {
                $authMapping = $this->authenticationMappingRepository->getByZikulaId($userEntity->getUid());
                $rendererArgs['isVerified'] = null === $authMapping || $authMapping->isVerifiedEmail();

                $mailSent = $this->sendNotification($this->registrationNotifyEmail, 'regadminnotify', $rendererArgs);
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
     * @param UserEntity $userEntity
     * @param bool  $userNotification        Whether the user should be notified of the new registration or not;
     *                                       however if the user's password was created for him, then he will
     *                                       receive at least that mail without regard to this setting
     * @param bool $adminNotification        Whether the configured administrator mail e-mail address should
     *                                       be sent mail of the new registration
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by
     *                                       an administrator (but not by the user himself)
     *
     * @return array of mail errors
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     * @throws AccessDeniedException Thrown if the current user does not have overview access
     * @throws RuntimeException Thrown if the user couldn't be added to the relevant user groups or
     *                                  if the registration couldn't be saved
     */
    public function createAndSendUserMail(
        UserEntity $userEntity,
        bool $userNotification = true,
        bool $adminNotification = true,
        string $passwordCreatedForUser = ''
    ): array {
        $mailErrors = [];
        $rendererArgs = [];
        $rendererArgs['user'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['createdByAdmin'] = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);

        if ($userNotification || !empty($passwordCreatedForUser)) {
            $mailSent = $this->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->translator->trans('Warning! The welcoming email for the newly created user could not be sent.', [], 'mail');
            }
        }
        if ($adminNotification) {
            // mail notify email to inform admin about registration
            if (!empty($this->registrationNotifyEmail)) {
                $authMapping = $this->authenticationMappingRepository->getByZikulaId($userEntity->getUid());
                $rendererArgs['isVerified'] = null === $authMapping || $authMapping->isVerifiedEmail();

                $subject = $this->translator->trans('New registration: %userName%', ['%userName%' => $userEntity->getUname()]);
                $mailSent = $this->sendNotification($this->registrationNotifyEmail, 'regadminnotify', $rendererArgs, $subject);
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
    public function mailUsers(array $users, array $messageData): bool
    {
        $email = (new Email())
            ->from(new Address($messageData['replyto'], $messageData['from']))
            ->subject($messageData['subject'])
            ->html($messageData['message'])
        ;
        if (1 === count($users)) {
            $email->to(new Address($users[0]->getEmail(), $users[0]->getUname()));
        } else {
            $email->to(new Address($messageData['replyto'], $messageData['from']));
        }
        try {
            if (count($users) > 1) {
                $bcc = [];
                foreach ($users as $user) {
                    $bcc[] = $user->getEmail();
                    if (count($bcc) === $messageData['batchsize']) {
                        $email->bcc($bcc);
                        $this->mailer->send($email);
                        $bcc = [];
                    }
                }
                $email->bcc($bcc);
            }
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error($exception->getMessage(), [
                'in' => __METHOD__
            ]);

            return false;
        }
        if ($this->mailLoggingEnabled) {
            $this->logger->info(sprintf('Email sent to %s', 'multiple users'), [
                'in' => __METHOD__,
                'users' => array_reduce($users, function (UserEntity $user) { return $user->getEmail() . ','; }, 'emails: ')
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
     * @return bool
     */
    public function sendNotification(
        string $toAddress,
        string $notificationType = '',
        array $templateArgs = [],
        string $subject = ''
    ): bool {
        $templateName = "@ZikulaUsersModule/Email/{$notificationType}.html.twig";
        try {
            $html = true;
            $htmlBody = $this->twig->render($templateName, $templateArgs);
        } catch (LoaderError $e) {
            $html = false;
            $htmlBody = '';
        }

        $templateName = "@ZikulaUsersModule/Email/{$notificationType}.txt.twig";
        try {
            $textBody = $this->twig->render($templateName, $templateArgs);
        } catch (LoaderError $e) {
            $textBody = '';
        }

        if (empty($subject)) {
            $subject = $this->generateEmailSubject($notificationType, $templateArgs);
        }

        $siteName = $this->site->getName();

        $email = (new Email())
            ->from(new Address($this->adminEmail, $siteName))
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
                'type' => $notificationType
            ]);

            return false;
        }
        if ($this->mailLoggingEnabled) {
            $this->logger->info(sprintf('Email sent to %s', $toAddress), [
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
