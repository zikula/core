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

namespace Zikula\ZAuthBundle\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Collector\AuthenticationMethodCollector;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Helper\AccessHelper;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;
use Zikula\ZAuthBundle\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthBundle\Entity\UserVerificationEntity;
use Zikula\ZAuthBundle\Form\Type\ChangeEmailType;
use Zikula\ZAuthBundle\Form\Type\ChangePasswordType;
use Zikula\ZAuthBundle\Form\Type\LostPasswordType;
use Zikula\ZAuthBundle\Form\Type\LostUserNameType;
use Zikula\ZAuthBundle\Helper\LostPasswordVerificationHelper;
use Zikula\ZAuthBundle\Helper\MailHelper;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthBundle\Repository\UserVerificationRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

#[Route('/account')]
class AccountController extends AbstractController
{
    use TranslatorTrait;

    public function __construct(
        TranslatorInterface $translator,
        private readonly RegistrationHelper $registrationHelper,
        private readonly int $minimumPasswordLength,
        private readonly bool $usePasswordStrengthMeter,
        private readonly int $changeEmailExpireDays,
        private readonly int $changePasswordExpireDays,
        private readonly bool $loginDisplayInactiveStatus,
        private readonly bool $loginDisplayPendingStatus
    ) {
        $this->setTranslator($translator);
    }

    #[Route('/lost-user-name', name: 'zikulazauthbundle_account_lostusername')]
    public function lostUserName(
        Request $request,
        RouterInterface $router,
        CurrentUserApiInterface $currentUserApi,
        RateLimiterFactory $lostCredentialsLimiter,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        MailHelper $mailHelper
    ): Response {
        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }

        $form = $this->createForm(LostUserNameType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $limiter = $lostCredentialsLimiter->create($request->getClientIp());
            if (false === $limiter->consume(1)->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }

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
                $message = $this->trans('A user with this address does not exist at this site.');
                if ($this->registrationHelper->isRegistrationEnabled()) {
                    $message .= ' ' . $this->trans('Do you want to <a href="%registerLink%">register</a>?', ['%registerLink%' => $router->generate('zikulausersbundle_registration_register')]);
                }
                $this->addFlash('error', $message);
            }
        }

        return $this->render('@ZikulaZAuth/Account/lostUserName.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/lost-password', name: 'zikulazauthbundle_account_lostpassword')]
    public function lostPassword(
        Request $request,
        RouterInterface $router,
        CurrentUserApiInterface $currentUserApi,
        RateLimiterFactory $lostCredentialsLimiter,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        LostPasswordVerificationHelper $lostPasswordVerificationHelper,
        MailHelper $mailHelper
    ): Response {
        $redirectToRoute = 'zikulausersbundle_account_menu';

        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute($redirectToRoute);
        }

        $form = $this->createForm(LostPasswordType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $limiter = $lostCredentialsLimiter->create($request->getClientIp());
            if (false === $limiter->consume(1)->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }

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
                        $lostPasswordId = $lostPasswordVerificationHelper->createLostPasswordId($mapping);
                        $sent = $mailHelper->sendNotification($mapping->getEmail(), 'lostpassword', [
                            'uname' => $mapping->getUname(),
                            'validDays' => $this->changePasswordExpireDays,
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
                        if ($this->loginDisplayInactiveStatus) {
                            $this->addFlash('error', 'Sorry! Your account is marked as inactive. Please contact a site administrator for more information.');
                        }
                        break;
                    case UsersConstant::ACTIVATED_PENDING_REG:
                        if ($this->loginDisplayPendingStatus) {
                            $this->addFlash('error', 'Sorry! An active account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.');
                        } else {
                            $this->addFlash('error', 'Sorry! Your account has not completed the registration process. Please contact a site administrator for more information.');
                        }
                        break;
                    default:
                        $this->addFlash('error', 'Sorry! An active account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.');
                }
            } elseif (1 < count($mapping)) {
                $this->addFlash('error', 'There are too many users registered with that address. Please contact a site administrator for assistance.');
            } else {
                $message = $this->trans('A user with this %property% does not exist at this site.', ['%property%' => $map[$field]]);
                if ($this->registrationHelper->isRegistrationEnabled()) {
                    $message .= ' ' . $this->trans('Do you want to <a href="%registerLink%">register</a>?', ['%registerLink%' => $router->generate('zikulausersbundle_registration_register')]);
                }
                $this->addFlash('error', $message);
            }
            if (!empty($redirectToRoute)) {
                return $this->redirectToRoute($redirectToRoute);
            }
        }

        return $this->render('@ZikulaZAuth/Account/lostPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/lost-password/reset', name: 'zikulazauthbundle_account_lostpasswordreset')]
    public function lostPasswordReset(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        AuthenticationMethodCollector $authenticationMethodCollector,
        EncoderFactoryInterface $encoderFactory,
        LostPasswordVerificationHelper $lostPasswordVerificationHelper,
        AccessHelper $accessHelper
    ): Response {
        $redirectToRoute = 'zikulausersbundle_account_menu';

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
            $this->addFlash('error', 'If an account exists with that email or username, a password reset will be sent to it.');

            return $this->redirectToRoute($redirectToRoute);
        }

        if (!$lostPasswordVerificationHelper->checkConfirmationCode($user->getUid(), $requestDetails['confirmationCode'])) {
            $this->addFlash('error', 'Your request could not be processed due to invalid arguments. Maybe your link is expired?');

            return $this->redirectToRoute($redirectToRoute);
        }

        $form = $this->createForm(LostPasswordType::class, [], [
            'includeReset' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // use authentication method to create zauth mapping if not already created
            $authenticationMethods = $authenticationMethodCollector->getActive();
            $authenticationMethod = array_shift($authenticationMethods);
            if (null === $authenticationMethod) {
                throw new \RuntimeException($this->trans('There is no authentication method activated.'));
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

        return $this->render('@ZikulaZAuth/Account/lostPasswordReset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws AccessDeniedException Thrown if the user is not logged in
     */
    #[Route('/change-email', name: 'zikulazauthbundle_account_changeemail')]
    public function changeEmail(
        Request $request,
        RouterInterface $router,
        CurrentUserApiInterface $currentUserApi,
        UserVerificationRepositoryInterface $userVerificationRepository,
        EncoderFactoryInterface $encoderFactory,
        MailHelper $mailHelper
    ): Response {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ChangeEmailType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $code = bin2hex(random_bytes(8));
            $hashedCode = $encoderFactory->getEncoder(AuthenticationMappingEntity::class)->encodePassword($code, null);
            $currentUserId = (int) $currentUserApi->get('uid');
            $userVerificationRepository->setVerificationCode($currentUserId, ZAuthConstant::VERIFYCHGTYPE_EMAIL, $hashedCode, $data['email']);
            $templateArgs = [
                'uname' => $currentUserApi->get('uname'),
                'email' => $currentUserApi->get('email'),
                'newemail' => $data['email'],
                'changeEmailExpireDays' => $this->changeEmailExpireDays,
                'url' => $router->generate('zikulazauthbundle_account_confirmchangedemail', ['code' => $code], RouterInterface::ABSOLUTE_URL),
            ];
            $sent = $mailHelper->sendNotification($data['email'], 'userverifyemail', $templateArgs);
            if ($sent) {
                $this->addFlash('success', 'Done! You will receive an e-mail to your new e-mail address to confirm the change. You must follow the instructions in that message in order to verify your new address.');
            } else {
                $this->addFlash('error', 'Error! There was a problem saving your new e-mail address or sending you a verification message.');
            }

            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }

        return $this->render('@ZikulaZAuth/Account/changeEmail.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws AccessDeniedException Thrown if the user is not logged in
     */
    #[Route('/change-email-confirm/{code}', name: 'zikulazauthbundle_account_confirmchangedemail')]
    public function confirmChangedEmail(
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
            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }

        /** @var UserVerificationEntity $verificationRecord */
        $verificationRecord = $userVerificationRepository->findOneBy([
            'uid' => $currentUserApi->get('uid'),
            'changetype' => ZAuthConstant::VERIFYCHGTYPE_EMAIL
        ]);

        // check if verification record is already deleted
        if (null === $verificationRecord) {
            $this->addFlash('error', $this->trans('Error! Your e-mail has not been found. After your request you have %days% days to confirm the new e-mail address.', ['%days%' => $this->changeEmailExpireDays]));

            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }

        $validCode = $encoderFactory->getEncoder(AuthenticationMappingEntity::class)->isPasswordValid($verificationRecord->getVerifycode(), $code, null);
        if (!$validCode) {
            $this->addFlash('error', $this->trans('Error! Your e-mail has not been found. After your request you have %days% days to confirm the new e-mail address.', ['%days%' => $this->changeEmailExpireDays]));

            return $this->redirectToRoute('zikulausersbundle_account_menu');
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

        return $this->redirectToRoute('zikulausersbundle_account_menu');
    }

    #[Route('/change-password', name: 'zikulazauthbundle_account_changepassword')]
    public function changePassword(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        EncoderFactoryInterface $encoderFactory,
        AccessHelper $accessHelper
    ): Response {
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
            'authenticationMethod' => $authenticationMethod,
        ], [
            'minimumPasswordLength' => $this->minimumPasswordLength,
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

            $authenticationMappingRepository->persistAndFlush($mapping);

            $this->addFlash('success', 'Password successfully changed.');
            if ($data['login']) {
                $accessHelper->login($user);
            }

            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }

        return $this->render('@ZikulaZAuth/Account/changePassword.html.twig', [
            'login' => $login,
            'form' => $form->createView(),
            'usePasswordStrengthMeter' => $this->usePasswordStrengthMeter,
        ]);
    }
}
