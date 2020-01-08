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

namespace Zikula\UsersModule\Helper;

use InvalidArgumentException;
use RuntimeException;
use Swift_Message;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;
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
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var MailerApiInterface
     */
    private $mailerApi;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $authenticationMappingRepository;

    public function __construct(
        TranslatorInterface $translator,
        Environment $twig,
        VariableApiInterface $variableApi,
        MailerApiInterface $mailerApi,
        PermissionApiInterface $permissionApi,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository
    ) {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->variableApi = $variableApi;
        $this->mailerApi = $mailerApi;
        $this->permissionApi = $permissionApi;
        $this->authenticationMappingRepository = $authenticationMappingRepository;
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
        $rendererArgs['reginfo'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['createdByAdmin'] = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);

        if (!empty($passwordCreatedForUser) || ($userNotification && $userEntity->isApproved())) {
            $mailSent = $this->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->translator->trans('Warning! The welcoming email for the new registration could not be sent.');
            }
        }
        if ($adminNotification) {
            // mail notify email to inform admin about registration
            $notificationEmail = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, '');
            if (!empty($notificationEmail)) {
                $authMapping = $this->authenticationMappingRepository->getByZikulaId($userEntity->getUid());
                $rendererArgs['isVerified'] = null === $authMapping || $authMapping->isVerifiedEmail();

                $mailSent = $this->sendNotification($notificationEmail, 'regadminnotify', $rendererArgs);
                if (!$mailSent) {
                    $mailErrors[] = $this->translator->trans('Warning! The notification email for the new registration could not be sent.');
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
        $rendererArgs['reginfo'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['createdByAdmin'] = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);

        if ($userNotification || !empty($passwordCreatedForUser)) {
            $mailSent = $this->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->translator->trans('Warning! The welcoming email for the newly created user could not be sent.');
            }
        }
        if ($adminNotification) {
            // mail notify email to inform admin about registration
            $notificationEmail = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, '');
            if (!empty($notificationEmail)) {
                $authMapping = $this->authenticationMappingRepository->getByZikulaId($userEntity->getUid());
                $rendererArgs['isVerified'] = null === $authMapping || $authMapping->isVerifiedEmail();

                $subject = $this->translator->trans('New registration: %s', ['%s' => $userEntity->getUname()]);
                $mailSent = $this->sendNotification($notificationEmail, 'regadminnotify', $rendererArgs, $subject);
                if (!$mailSent) {
                    $mailErrors[] = $this->translator->trans('Warning! The notification email for the newly created user could not be sent.');
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
        $mailSent = true;
        $message = new Swift_Message($messageData['subject'], $messageData['message']);
        $message->setFrom([$messageData['replyto'] => $messageData['from']]);
        if (1 === count($users)) {
            $message->setTo([$users[0]->getEmail() => $users[0]->getUname()]);
        } else {
            $message->setTo([$messageData['replyto'] => $messageData['from']]);
        }
        if (count($users) > 1) {
            $bcc = [];
            foreach ($users as $user) {
                $bcc[] = $user->getEmail();
                if (count($bcc) === $messageData['batchsize']) {
                    $message->setBcc($bcc);
                    $mailSent = $mailSent && $this->mailerApi->sendMessage($message, null, null, '', 'html' === $messageData['format']);
                    $bcc = [];
                }
            }
            $message->setBcc($bcc);
        }
        $mailSent = $mailSent && $this->mailerApi->sendMessage($message, null, null, '', 'html' === $messageData['format']);

        return $mailSent;
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

        $sitename = $this->variableApi->getSystemVar('sitename', $this->variableApi->getSystemVar('sitename_en'));

        $message = new Swift_Message($subject);
        $message->setFrom([$this->variableApi->getSystemVar('adminmail') => $sitename]);
        $message->setTo([$toAddress]);
        $message->setBody($html ? $htmlBody : $textBody);

        return $this->mailerApi->sendMessage($message, null, null, $textBody, $html);
    }

    private function generateEmailSubject(string $notificationType, array $templateArgs = []): string
    {
        $siteName = $this->variableApi->getSystemVar('sitename');
        switch ($notificationType) {
            case 'regadminnotify':
                if (!$templateArgs['reginfo']->isApproved()) {
                    return $this->translator->trans('New registration pending approval: %s', ['%s' => $templateArgs['reginfo']['uname']]);
                }
                if (isset($templateArgs['isVerified']) && !$templateArgs['isVerified']) {
                    return $this->translator->trans('New registration pending e-mail verification: %s', ['%s' => $templateArgs['reginfo']['uname']]);
                }

                return $this->translator->trans('New user activated: %s', ['%s' => $templateArgs['reginfo']['uname']]);

            case 'regdeny':
                return $this->translator->trans('Your recent request at %s.', ['%s' => $siteName]);

            case 'welcome':
                return $this->translator->trans('Welcome to %1$s, %2$s!', ['%1$s' => $siteName, '%2$s' => $templateArgs['reginfo']['uname']]);

            default:
                return $this->translator->trans('A message from %s.', ['%s' => $siteName]);
        }
    }
}
