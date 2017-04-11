<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
     * @var \Twig_Environment
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

    /**
     * MailHelper constructor.
     * @param TranslatorInterface $translator
     * @param \Twig_Environment $twig
     * @param VariableApiInterface $variableApi
     * @param MailerApiInterface $mailerApi
     * @param PermissionApiInterface $permissionApi
     * @param AuthenticationMappingRepositoryInterface $authenticationMappingRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        \Twig_Environment $twig,
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
     * @throws \InvalidArgumentException Thrown if invalid parameters are received
     * @throws \RuntimeException Thrown if the registration couldn't be saved
     */
    public function createAndSendRegistrationMail(UserEntity $userEntity, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        $mailErrors = [];
        $rendererArgs = [];
        $rendererArgs['reginfo'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['createdByAdmin'] = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);

        if (($userNotification && $userEntity->isApproved()) || !empty($passwordCreatedForUser)) {
            $mailSent = $this->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->translator->__('Warning! The welcoming email for the new registration could not be sent.');
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
                    $mailErrors[] = $this->translator->__('Warning! The notification email for the new registration could not be sent.');
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
     * @throws \InvalidArgumentException Thrown if invalid parameters are received
     * @throws AccessDeniedException Thrown if the current user does not have overview access
     * @throws \RuntimeException Thrown if the user couldn't be added to the relevant user groups or
     *                                  if the registration couldn't be saved
     */
    public function createAndSendUserMail(UserEntity $userEntity, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        $mailErrors = [];
        $rendererArgs = [];
        $rendererArgs['reginfo'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['createdByAdmin'] = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);

        if ($userNotification || !empty($passwordCreatedForUser)) {
            $mailSent = $this->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->translator->__('Warning! The welcoming email for the newly created user could not be sent.');
            }
        }
        if ($adminNotification) {
            // mail notify email to inform admin about registration
            $notificationEmail = $this->variableApi->get('ZikulaUsersModule', 'reg_notifyemail', '');
            if (!empty($notificationEmail)) {
                $authMapping = $this->authenticationMappingRepository->getByZikulaId($userEntity->getUid());
                $rendererArgs['isVerified'] = null === $authMapping || $authMapping->isVerifiedEmail();

                $subject = $this->translator->__f('New registration: %s', ['%s' => $userEntity->getUname()]);
                $mailSent = $this->sendNotification($notificationEmail, 'regadminnotify', $rendererArgs, $subject);
                if (!$mailSent) {
                    $mailErrors[] = $this->translator->__('Warning! The notification email for the newly created user could not be sent.');
                }
            }
        }

        return $mailErrors;
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

        $sitename = $this->variableApi->getSystemVar('sitename', $this->variableApi->getSystemVar('sitename_en'));

        $message = \Swift_Message::newInstance();
        $message->setFrom([$this->variableApi->getSystemVar('adminmail') => $sitename]);
        $message->setTo([$toAddress]);
        $message->setSubject($subject);
        $message->setBody($html ? $htmlBody : $textBody);

        return $this->mailerApi->sendMessage($message, null, null, $textBody, $html);
    }

    private function generateEmailSubject($notificationType, array $templateArgs = [])
    {
        $siteName = $this->variableApi->getSystemVar('sitename');
        switch ($notificationType) {
            case 'regadminnotify':
                if (!$templateArgs['reginfo']->isApproved()) {
                    return $this->translator->__f('New registration pending approval: %s', ['%s' => $templateArgs['reginfo']['uname']]);
                } elseif (isset($templateArgs['isVerified']) && !$templateArgs['isVerified']) {
                    return $this->translator->__f('New registration pending e-mail verification: %s', ['%s' => $templateArgs['reginfo']['uname']]);
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
