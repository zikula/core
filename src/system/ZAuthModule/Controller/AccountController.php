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

namespace Zikula\ZAuthModule\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\HookBundle\Dispatcher\Exception\RuntimeException;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Collector\AuthenticationMethodCollector;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Helper\AccessHelper;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;
use Zikula\ZAuthModule\Form\Type\ChangeEmailType;
use Zikula\ZAuthModule\Form\Type\ChangePasswordType;
use Zikula\ZAuthModule\Form\Type\LostPasswordType;
use Zikula\ZAuthModule\Form\Type\LostUserNameType;
use Zikula\ZAuthModule\Helper\LostPasswordVerificationHelper;
use Zikula\ZAuthModule\Helper\MailHelper;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/lost-user-name")
     * @Template("@ZikulaZAuthModule/Account/lostUserName.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function lostUserNameAction(
        Request $request,
        RouterInterface $router,
        CurrentUserApiInterface $currentUserApi,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        VariableApiInterface $variableApi,
        MailHelper $mailHelper
    ) {
        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $form = $this->createForm(LostUserNameType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $mapping = $authenticationMappingRepository->findBy(['email' => $data['email']]);
            if (1 === count($mapping)) {
                // send email
                $sent = $mailHelper->sendNotification($mapping[0]->getEmail(), 'lostuname', [
                    'uname' => $mapping[0]->getUname(),
                    'requestedByAdmin' => false
                ]);
                if ($sent) {
                    $this->addFlash('status', $this->trans('Done! The account information for %email% has been sent via e-mail.', ['%email%' => $data['email']]));
                } else {
                    $this->addFlash('error', 'Unable to send email to the requested address. Please contact a site administrator for assistance.');
                }
            } elseif (1 < count($mapping)) {
                $this->addFlash('error', 'There are too many users registered with that address. Please contact a site administrator for assistance.');
            } else {
                $hasRegistration = $variableApi->get(UsersConstant::MODNAME, UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED);
                if ($hasRegistration) {
                    $this->addFlash('error', $this->trans('A user with this address does not exist at this site.') . ' ' . $this->trans('Do you want to <a href="%registerLink%">register</a>?', ['%registerLink%' => $router->generate('zikulausersmodule_registration_register')]));
                } else {
                    $this->addFlash('error', 'A user with this address does not exist at this site.');
                }
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/lost-password")
     * @Template("@ZikulaZAuthModule/Account/lostPassword.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function lostPasswordAction(
        Request $request,
        RouterInterface $router,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        LostPasswordVerificationHelper $lostPasswordVerificationHelper,
        VariableApiInterface $variableApi,
        MailHelper $mailHelper
    ) {
        $redirectToRoute = 'zikulausersmodule_account_menu';

        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute($redirectToRoute);
        }

        $form = $this->createForm(LostPasswordType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $redirectToRoute = '';
            $map = ['uname' => $this->trans('username'), 'email' => $this->trans('email address')];
            $data = $form->getData();
            $field = empty($data['uname']) ? 'email' : 'uname';
            $inverse = 'uname' === $field ? 'email' : 'uname';
            /** @var AuthenticationMappingEntity $mapping */
            $mapping = $authenticationMappingRepository->findBy([$field => $data[$field]]);
            if (1 === count($mapping)) {
                $mapping = $mapping[0];
                /** @var UserEntity $user */
                $user = $userRepository->find($mapping->getUid());
                switch ($user->getActivated()) {
                    case UsersConstant::ACTIVATED_ACTIVE:
                        $changePasswordExpireDays = $this->getVar(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD);
                        $lostPasswordId = $lostPasswordVerificationHelper->createLostPasswordId($mapping);
                        $sent = $mailHelper->sendNotification($mapping->getEmail(), 'lostpassword', [
                            'uname' => $mapping->getUname(),
                            'validDays' => $changePasswordExpireDays,
                            'lostPasswordId' => $lostPasswordId,
                            'requestedByAdmin' => false
                        ]);
                        if ($sent) {
                            $this->addFlash('status', $this->trans('Done! The confirmation link for %identifier% has been sent via e-mail.', ['%identifier%' => $data[$field]]));
                        } else {
                            $this->addFlash('error', $this->trans('Unable to send email to the requested %identifier%. Please try your %otherIdentifier% or contact a site administrator for assistance.', ['%identifier%' => $map[$field], '%otherIdentifier%' => $map[$inverse]]));
                        }
                        break;
                    case UsersConstant::ACTIVATED_INACTIVE:
                        if ($this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS)) {
                            $this->addFlash('error', 'Sorry! Your account is marked as inactive. Please contact a site administrator for more information.');
                        }
                        break;
                    case UsersConstant::ACTIVATED_PENDING_REG:
                        $displayPendingApproval = $this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS);
                        $displayPendingVerification = $this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
                        if ($displayPendingApproval || $displayPendingVerification) {
                            $this->addFlash('error', 'Sorry! Your account has not completed the registration process. Please contact a site administrator for more information.');
                        } else {
                            $this->addFlash('error', 'Sorry! An active account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.');
                        }
                        break;
                    default:
                        $this->addFlash('error', 'Sorry! An active account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.');
                }
            } elseif (1 < count($mapping)) {
                $this->addFlash('error', 'There are too many users registered with that address. Please contact a site administrator for assistance.');
            } else {
                $message = $this->trans('A user with this %property% does not exist at this site.', ['%property%' => $map[$field]]);

                $hasRegistration = $variableApi->get(UsersConstant::MODNAME, UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED);
                if ($hasRegistration) {
                    $message .= ' ' . $this->trans('Do you want to <a href="%registerLink%">register</a>?', ['%registerLink%' => $router->generate('zikulausersmodule_registration_register')]);
                }

                $this->addFlash('error', $message);
            }
            if (!empty($redirectToRoute)) {
                return $this->redirectToRoute($redirectToRoute);
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/lost-password/reset")
     * @Template("@ZikulaZAuthModule/Account/lostPasswordReset.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function lostPasswordResetAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        AuthenticationMethodCollector $authenticationMethodCollector,
        EncoderFactoryInterface $encoderFactory,
        LostPasswordVerificationHelper $lostPasswordVerificationHelper,
        AccessHelper $accessHelper
    ) {
        $redirectToRoute = 'zikulausersmodule_account_menu';

        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute($redirectToRoute);
        }

        if (!$request->query->has('id')) {
            $this->addFlash('error', 'Your request could not be processed due to missing arguments.');

            return $this->redirectToRoute($redirectToRoute);
        }

        try {
            $requestDetails = $lostPasswordVerificationHelper->decodeLostPasswordId($request->query->get('id'));
        } catch (Exception $exception) {
            $this->addFlash('error', $this->trans('Your request could not be processed.') . ' ' . $exception->getMessage());

            return $this->redirectToRoute($redirectToRoute);
        }

        if ('' === $requestDetails['userId'] || '' === $requestDetails['userName'] || '' === $requestDetails['emailAddress']) {
            $this->addFlash('error', 'Your request could not be processed due to invalid arguments.');

            return $this->redirectToRoute($redirectToRoute);
        }

        /** @var UserEntity $user */
        $user = $userRepository->find($requestDetails['userId']);
        if (null === $user) {
            $this->addFlash('error', 'User not found. Please contact a site administrator for assistance.');

            return $this->redirectToRoute($redirectToRoute);
        }

        if (!$lostPasswordVerificationHelper->checkConfirmationCode($user->getUid(), $requestDetails['confirmationCode'])) {
            $this->addFlash('error', 'Your request could not be processed due to invalid arguments. Maybe your link is expired?');

            return $this->redirectToRoute($redirectToRoute);
        }

        $form = $this->createForm(LostPasswordType::class, [], [
            'includeReset' => true
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // use authentication method to create zauth mapping if not already created
            $authenticationMethods = $authenticationMethodCollector->getActive();
            $authenticationMethod = array_shift($authenticationMethods);
            if (null === $authenticationMethod) {
                throw new RuntimeException($this->trans('There is no authentication method activated.'));
            }
            $authenticationMethod->authenticate([
                'uname' => $user->getUname(),
                'email' => $user->getEmail(),
                'pass' => '1234567890'
            ]);
            // will not authenticate with pass. clear the flashbag of errors.
            if ($request->hasSession() && ($session = $request->getSession())) {
                $session->getFlashBag()->clear();
            }
            // update password
            $mapping = $authenticationMappingRepository->getByZikulaId($user->getUid());
            $mapping->setPass($encoderFactory->getEncoder($mapping)->encodePassword($data['pass'], null));
            $authenticationMappingRepository->persistAndFlush($mapping);
            $accessHelper->login($user);
            $this->addFlash('success', 'Your change has been successfully saved. You are now logged in with your new password.');

            return $this->redirectToRoute($redirectToRoute);
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/change-email")
     * @Template("@ZikulaZAuthModule/Account/changeEmail.html.twig")
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user is not logged in
     */
    public function changeEmailAction(
        Request $request,
        RouterInterface $router,
        CurrentUserApiInterface $currentUserApi,
        UserVerificationRepositoryInterface $userVerificationRepository,
        EncoderFactoryInterface $encoderFactory,
        MailHelper $mailHelper
    ) {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ChangeEmailType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $code = bin2hex(random_bytes(8));
            $hashedCode = $encoderFactory->getEncoder(AuthenticationMappingEntity::class)->encodePassword($code, null);
            $currentUserId = (int)$currentUserApi->get('uid');
            $userVerificationRepository->setVerificationCode($currentUserId, ZAuthConstant::VERIFYCHGTYPE_EMAIL, $hashedCode, $data['email']);
            $templateArgs = [
                'uname'    => $currentUserApi->get('uname'),
                'email'    => $currentUserApi->get('email'),
                'newemail' => $data['email'],
                'url'      => $router->generate('zikulazauthmodule_account_confirmchangedemail', ['code' => $code], RouterInterface::ABSOLUTE_URL),
            ];
            $sent = $mailHelper->sendNotification($data['email'], 'userverifyemail', $templateArgs);
            if ($sent) {
                $this->addFlash('success', 'Done! You will receive an e-mail to your new e-mail address to confirm the change. You must follow the instructions in that message in order to verify your new address.');
            } else {
                $this->addFlash('error', 'Error! There was a problem saving your new e-mail address or sending you a verification message.');
            }

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/change-email-confirm/{code}")
     *
     * @throws AccessDeniedException Thrown if the user is not logged in
     */
    public function confirmChangedEmailAction(
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        UserVerificationRepositoryInterface $userVerificationRepository,
        EncoderFactoryInterface $encoderFactory,
        string $code = null
    ): Response {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        if (empty($code)) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        $emailExpireDays = $this->getVar(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL, ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL);
        $userVerificationRepository->purgeExpiredRecords($emailExpireDays, ZAuthConstant::VERIFYCHGTYPE_PWD, false);

        /** @var UserVerificationEntity $verificationRecord */
        $verificationRecord = $userVerificationRepository->findOneBy([
            'uid' => $currentUserApi->get('uid'),
            'changetype' => ZAuthConstant::VERIFYCHGTYPE_EMAIL
        ]);

        // check if verification record is already deleted
        if (null === $verificationRecord) {
            $this->addFlash('error', $this->trans('Error! Your e-mail has not been found. After your request you have %days% days to confirm the new e-mail address.', ['%days%' => $emailExpireDays]));

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $validCode = $encoderFactory->getEncoder(AuthenticationMappingEntity::class)->isPasswordValid($verificationRecord->getVerifycode(), $code, null);
        if (!$validCode) {
            $this->addFlash('error', $this->trans('Error! Your e-mail has not been found. After your request you have %days% days to confirm the new e-mail address.', ['%days%' => $emailExpireDays]));

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        /** @var AuthenticationMappingEntity $mapping */
        $mapping = $authenticationMappingRepository->findOneBy(['uid' => $currentUserApi->get('uid')]);
        $mapping->setEmail($verificationRecord->getNewemail());
        $authenticationMappingRepository->persistAndFlush($mapping);

        /** @var UserEntity $user */
        $user = $userRepository->find($currentUserApi->get('uid'));
        $user->setEmail($verificationRecord->getNewemail());
        $userRepository->persistAndFlush($user);

        $userVerificationRepository->resetVerifyChgFor($user->getUid(), [ZAuthConstant::VERIFYCHGTYPE_EMAIL]);
        $this->addFlash('success', 'Done! Changed your e-mail address.');

        return $this->redirectToRoute('zikulausersmodule_account_menu');
    }

    /**
     * @Route("/change-password")
     * @Template("@ZikulaZAuthModule/Account/changePassword.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function changePasswordAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        EncoderFactoryInterface $encoderFactory,
        VariableApiInterface $variableApi,
        AccessHelper $accessHelper
    ) {
        // Retrieve and delete any session variables being sent in before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $uid = $authenticationMethod = null;
        if ($request->hasSession() && ($session = $request->getSession())) {
            $uid = $session->get(UsersConstant::FORCE_PASSWORD_SESSION_UID_KEY);
            $authenticationMethod = $session->get('authenticationMethod');
            $session->remove(UsersConstant::FORCE_PASSWORD_SESSION_UID_KEY);
        }

        if (isset($uid)) {
            $login = true;
        } else {
            $login = false;
            $uid = $currentUserApi->get('uid');
        }

        $form = $this->createForm(ChangePasswordType::class, [
            'uid' => $uid,
            'login' => $login,
            'authenticationMethod' => $authenticationMethod
        ], [
            'minimumPasswordLength' => $variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH)
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            /** @var AuthenticationMappingEntity $mapping */
            $mapping = $authenticationMappingRepository->findOneBy(['uid' => $data['uid']]);
            $mapping->setPass($encoderFactory->getEncoder($mapping)->encodePassword($data['pass'], null));

            /** @var UserEntity $user */
            $user = $userRepository->find($mapping->getUid());
            $user->delAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY);

            $authenticationMappingRepository->persistAndFlush($mapping); // flushes entire manager

            $this->addFlash('success', 'Password successfully changed.');
            if ($data['login']) {
                $accessHelper->login($user);
            }

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        return [
            'login' => $login,
            'form' => $form->createView()
        ];
    }
}
