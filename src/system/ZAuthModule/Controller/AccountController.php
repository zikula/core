<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Dispatcher\Exception\RuntimeException;
use Zikula\Core\Controller\AbstractController;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;
use Zikula\ZAuthModule\Form\Type\ChangeEmailType;
use Zikula\ZAuthModule\Form\Type\ChangePasswordType;
use Zikula\ZAuthModule\Form\Type\LostPasswordType;
use Zikula\ZAuthModule\Form\Type\LostUserNameType;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/lost-user-name")
     * @Template("ZikulaZAuthModule:Account:lostUserName.html.twig")
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function lostUserNameAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $form = $this->createForm(LostUserNameType::class, [], [
            'translator' => $this->get('translator.default')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $mapping = $this->get('zikula_zauth_module.authentication_mapping_repository')->findBy(['email' => $data['email']]);
            if (count($mapping) == 1) {
                // send email
                $sent = $this->get('zikula_zauth_module.helper.mail_helper')->sendNotification($mapping[0]->getEmail(), 'lostuname', [
                    'uname' => $mapping[0]->getUname(),
                    'requestedByAdmin' => false,
                ]);
                if ($sent) {
                    $this->addFlash('status', $this->__f('Done! The account information for %s has been sent via e-mail.', ['%s' => $data['email']]));
                } else {
                    $this->addFlash('error', $this->__('Unable to send email to the requested address. Please contact the system administrator for assistance.'));
                }
            } elseif (count($mapping) > 1) {
                $this->addFlash('error', $this->__('There are too many users registered with that address. Please contact the system administrator for assistance.'));
            } else {
                $this->addFlash('error', $this->__('Unable to send email to the requested address. Please contact the system administrator for assistance.'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/lost-password")
     * @Template("ZikulaZAuthModule:Account:lostPassword.html.twig")
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function lostPasswordAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $form = $this->createForm(LostPasswordType::class, [], [
            'translator' => $this->get('translator.default')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $redirectToRoute = '';
            $map = ['uname' => $this->__('username'), 'email' => $this->__('email address')];
            $data = $form->getData();
            $field = empty($data['uname']) ? 'email' : 'uname';
            $inverse = $field == 'uname' ? 'email' : 'uname';
            $mapping = $this->get('zikula_zauth_module.authentication_mapping_repository')->findBy([$field => $data[$field]]);
            if (count($mapping) == 1) {
                $mapping = $mapping[0];
                $user = $this->get('zikula_users_module.user_repository')->find($mapping->getUid());
                switch ($user->getActivated()) {
                    case UsersConstant::ACTIVATED_ACTIVE:
                        $changePasswordExpireDays = $this->getVar(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD);
                        $lostPasswordId = $this->get('zikula_zauth_module.helper.lost_password_verification_helper')->createLostPasswordId($mapping);
                        $sent = $this->get('zikula_zauth_module.helper.mail_helper')->sendNotification($mapping->getEmail(), 'lostpassword', [
                            'uname' => $mapping->getUname(),
                            'validDays' => $changePasswordExpireDays,
                            'lostPasswordId' => $lostPasswordId,
                            'requestedByAdmin' => false,
                        ]);
                        if ($sent) {
                            $this->addFlash('status', $this->__f('Done! The confirmation link for %s has been sent via e-mail.', ['%s' => $data[$field]]));
                            $redirectToRoute = 'zikulausersmodule_account_menu';
                        } else {
                            $this->addFlash('error', $this->__f('Unable to send email to the requested %s. Please try your %o or contact the system administrator for assistance.', ['%s' => $map[$field], '%o' => $map[$inverse]]));
                        }
                        break;
                    case UsersConstant::ACTIVATED_INACTIVE:
                        if ($this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS)) {
                            $this->addFlash('error', $this->__('Sorry! Your account is marked as inactive. Please contact a site administrator for more information.'));
                        }
                        $redirectToRoute = 'zikulausersmodule_account_menu';
                        break;
                    case UsersConstant::ACTIVATED_PENDING_REG:
                        $displayPendingApproval = $this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS);
                        $displayPendingVerification = $this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
                        if ($displayPendingApproval || $displayPendingVerification) {
                            $this->addFlash('error', $this->__('Sorry! Your account has not completed the registration process. Please contact a site administrator for more information.'));
                            $redirectToRoute = 'zikulausersmodule_account_menu';
                        } else {
                            $this->addFlash('error', $this->__('Sorry! An account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.'));
                        }
                        break;
                    default:
                        $this->addFlash('error', $this->__('Sorry! An active account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.'));
                }
            } elseif (count($mapping) > 1) {
                $this->addFlash('error', $this->__('There are too many users registered with that address. Please contact the system administrator for assistance.'));
            } else {
                $this->addFlash('error', $this->__f('%s not found. Please contact the system administrator for assistance.', ['%s' => ucwords($map[$field])]));
            }
            if (!empty($redirectToRoute)) {
                return $this->redirectToRoute($redirectToRoute);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/lost-password/reset")
     * @Template("ZikulaZAuthModule:Account:lostPasswordReset.html.twig")
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function lostPasswordResetAction(Request $request)
    {
        $redirectToRoute = 'zikulausersmodule_account_menu';

        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute($redirectToRoute);
        }

        if (!$request->query->has('id')) {
            $this->addFlash('error', $this->__('Your request could not be processed due to missing arguments.'));

            return $this->redirectToRoute($redirectToRoute);
        }

        $lostPasswordVerificationHelper = $this->get('zikula_zauth_module.helper.lost_password_verification_helper');

        try {
            $requestDetails = $lostPasswordVerificationHelper->decodeLostPasswordId($request->query->get('id'));
        } catch (\Exception $e) {
            $this->addFlash('error', $this->__('Your request could not be processed.') . ' ' . $e->getMessage());

            return $this->redirectToRoute($redirectToRoute);
        }

        if ($requestDetails['userId'] == '' || $requestDetails['userName'] == '' || $requestDetails['emailAddress'] == '') {
            $this->addFlash('error', $this->__('Your request could not be processed due to invalid arguments.'));

            return $this->redirectToRoute($redirectToRoute);
        }

        /** @var UserEntity $user */
        $user = $this->get('zikula_users_module.user_repository')->find($requestDetails['userId']);
        if (null === $user) {
            $this->addFlash('error', $this->__('User not found. Please contact the system administrator for assistance.'));

            return $this->redirectToRoute($redirectToRoute);
        }

        if (!$lostPasswordVerificationHelper->checkConfirmationCode($user->getUid(), $requestDetails['confirmationCode'])) {
            $this->addFlash('error', $this->__('Your request could not be processed due to invalid arguments. Maybe your link is expired?'));

            return $this->redirectToRoute($redirectToRoute);
        }

        $form = $this->createForm(LostPasswordType::class, [], [
            'translator' => $this->get('translator.default'),
            'includeReset' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // use authentication method to create zauth mapping if not already created
            $authenticationMethods = $this->get('zikula_users_module.internal.authentication_method_collector')->getActive();
            $authenticationMethod = array_shift($authenticationMethods);
            if (null === $authenticationMethod) {
                throw new RuntimeException($this->__('There is no authentication method activated.'));
            }
            $authenticationMethod->authenticate([
                'uname' => $user->getUname(),
                'email' => $user->getEmail(),
                'pass' => '1234567890'
            ]);
            // will not authenticate with pass. clear the flashbag of errors.
            $this->container->get('session')->getFlashBag()->clear();
            // update password
            $mappingRepository = $this->get('zikula_zauth_module.authentication_mapping_repository');
            $mapping = $mappingRepository->getByZikulaId($user->getUid());
            $mapping->setPass($this->get('zikula_zauth_module.api.password')->getHashedPassword($data['pass']));
            $mappingRepository->persistAndFlush($mapping);
            $this->get('zikula_users_module.helper.access_helper')->login($user);
            $this->addFlash('success', $this->__('Your change has been successfully saved. You are now logged in with your new password.'));

            return $this->redirectToRoute($redirectToRoute);
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/change-email")
     * @Template("ZikulaZAuthModule:Account:changeEmail.html.twig")
     * @param Request $request
     * @return array
     */
    public function changeEmailAction(Request $request)
    {
        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ChangeEmailType::class, [], [
            'translator' => $this->get('translator.default'),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $currentUser = $this->get('zikula_users_module.current_user');
            $passwordApi = $this->get('zikula_zauth_module.api.password');
            $code = $passwordApi->generatePassword();
            $this->get('zikula_zauth_module.user_verification_repository')->setVerificationCode($currentUser->get('uid'), ZAuthConstant::VERIFYCHGTYPE_EMAIL, $passwordApi->getHashedPassword($code), $data['email']);
            $templateArgs = [
                'uname'    => $currentUser->get('uname'),
                'email'    => $currentUser->get('email'),
                'newemail' => $data['email'],
                'url'      => $this->get('router')->generate('zikulazauthmodule_account_confirmchangedemail', ['code' => $code], RouterInterface::ABSOLUTE_URL),
            ];
            $sent = $this->get('zikula_zauth_module.helper.mail_helper')->sendNotification($data['email'], 'userverifyemail', $templateArgs);
            if ($sent) {
                $this->addFlash('success', $this->__('Done! You will receive an e-mail to your new e-mail address to confirm the change. You must follow the instructions in that message in order to verify your new address.'));
            } else {
                $this->addFlash('error', $this->__('Error! There was a problem saving your new e-mail address or sending you a verification message.'));
            }

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/change-email-confirm/{code}")
     * @param null $code
     * @return Response
     */
    public function confirmChangedEmailAction($code = null)
    {
        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        if (empty($code)) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        $emailExpireDays = $this->getVar(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL, ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL);
        $this->get('zikula_zauth_module.user_verification_repository')->purgeExpiredRecords($emailExpireDays, ZAuthConstant::VERIFYCHGTYPE_PWD, false);
        $currentUser = $this->get('zikula_users_module.current_user');
        /** @var UserVerificationEntity $verificationRecord */
        $verificationRecord = $this->get('zikula_zauth_module.user_verification_repository')->findOneBy([
            'uid' => $currentUser->get('uid'),
            'changetype' => ZAuthConstant::VERIFYCHGTYPE_EMAIL
        ]);
        $validCode = $this->get('zikula_zauth_module.api.password')->passwordsMatch($code, $verificationRecord->getVerifycode());
        if (!$validCode) {
            $this->addFlash('error', $this->__f('Error! Your e-mail has not been found. After your request you have %s days to confirm the new e-mail address.', ['%s' => $emailExpireDays]));
        } else {
            $mapping = $this->get('zikula_zauth_module.authentication_mapping_repository')->findOneBy(['uid' => $currentUser->get('uid')]);
            $mapping->setEmail($verificationRecord->getNewemail());
            $this->get('zikula_zauth_module.authentication_mapping_repository')->persistAndFlush($mapping);

            $user = $this->get('zikula_users_module.user_repository')->find($currentUser->get('uid'));
            $user->setEmail($verificationRecord->getNewemail());
            $this->get('zikula_users_module.user_repository')->persistAndFlush($user);

            $this->get('zikula_zauth_module.user_verification_repository')->resetVerifyChgFor($user->getUid(), [ZAuthConstant::VERIFYCHGTYPE_EMAIL]);
            $this->addFlash('success', $this->__('Done! Changed your e-mail address.'));
        }

        return $this->redirectToRoute('zikulausersmodule_account_menu');
    }

    /**
     * @Route("/change-password")
     * @Template("ZikulaZAuthModule:Account:changePassword.html.twig")
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function changePasswordAction(Request $request)
    {
        // Retrieve and delete any session variables being sent in before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $uid = $request->getSession()->get(UsersConstant::FORCE_PASSWORD_SESSION_UID_KEY);
        $authenticationMethod = $request->getSession()->get('authenticationMethod');
        $request->getSession()->remove(UsersConstant::FORCE_PASSWORD_SESSION_UID_KEY);
        $currentUser = $this->get('zikula_users_module.current_user');

        if (isset($uid)) {
            $login = true;
        } else {
            $login = false;
            $uid = $currentUser->get('uid');
        }

        $form = $this->createForm(ChangePasswordType::class, [
            'uid' => $uid,
            'login' => $login,
            'authenticationMethod' => $authenticationMethod
        ], [
            'translator' => $this->get('translator.default')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $mapping = $this->get('zikula_zauth_module.authentication_mapping_repository')->findOneBy(['uid' => $data['uid']]);
            $mapping->setPass($this->get('zikula_zauth_module.api.password')->getHashedPassword($data['pass']));
            $userEntity = $this->get('zikula_users_module.user_repository')->find($mapping->getUid());
            $userEntity->delAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY);
            $this->get('zikula_zauth_module.authentication_mapping_repository')->persistAndFlush($mapping);
            $this->addFlash('success', $this->__('Password successfully changed.'));
            if ($data['login']) {
                $this->get('zikula_users_module.helper.access_helper')->login($userEntity);
            }

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        return [
            'login' => $login,
            'form' => $form->createView(),
        ];
    }
}
