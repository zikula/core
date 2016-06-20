<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Entity\UserVerificationEntity;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("")
     * @Template
     * @return Response|array
     */
    public function menuAction()
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn() && !$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $accountLinks = [];
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            $accountLinks = $this->get('zikula_users_module.helper.account_links_helper')->getAllAccountLinks();
        }

        return ['accountLinks' => $accountLinks];
    }

    /**
     * @todo consider click overload protection to prevent DOS
     * @Route("/lost-user-name")
     * @Template
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function lostUserNameAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $form = $this->createForm('Zikula\UsersModule\Form\AccountType\LostUserNameType',
            [], ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $user = $this->get('zikula_users_module.user_repository')->findBy(['email' => $data['email']]);
            if (count($user) == 1) {
                // send email
                $sent = $this->get('zikula_users_module.helper.mail_helper')->mailUserName($user[0]);
                if ($sent) {
                    $this->addFlash('status', $this->__f('Done! The account information for %s has been sent via e-mail.', ['%s' => $data['email']]));
                } else {
                    $this->addFlash('error', $this->__('Unable to send email to the requested address. Please contact the system administrator for assistance.'));
                }
            } elseif (count($user) > 1) {
                // too many users
                $this->addFlash('error', $this->__('There are too many users registered with that address. Please contact the system administrator for assistance.'));
            } else {
                // no user
                $this->addFlash('error', $this->__('Unable to send email to the requested address. Please contact the system administrator for assistance.'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @todo refactor to reduce code/simplify in this method
     * @todo consider click overload protection to prevent DOS
     * @Route("/lost-password")
     * @Template
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function lostPasswordAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $form = $this->createForm('Zikula\UsersModule\Form\AccountType\LostPasswordType',
            [], ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $redirectToRoute = '';
            $map = ['uname' => $this->__('username'), 'email' => $this->__('email address')];
            $data = $form->getData();
            $field = empty($data['uname']) ? 'email' : 'uname';
            $inverse = $field == 'uname' ? 'email' : 'uname';
            $user = $this->get('zikula_users_module.user_repository')->findBy([$field => $data[$field]]);
            if (count($user) == 1) {
                /** @var UserEntity $user */
                $user = $user[0];
                switch ($user->getActivated()) {
                    case UsersConstant::ACTIVATED_ACTIVE:
                        if ('' == $user->getPass() || UsersConstant::PWD_NO_USERS_AUTHENTICATION == $user->getPass()) {
                            $this->addFlash('error', $this->__('Sorry! Your account is not set up to use a password to log into this site. Please recover your account information to determine your available log-in options.'));
                            $redirectToRoute = 'zikulausersmodule_account_menu';
                            break;
                        }
                        $newConfirmationCode = $this->get('zikula_users_module.user_verification_repository')->setVerificationCode($user->getUid());
                        $sent = $this->get('zikula_users_module.helper.mail_helper')->mailConfirmationCode($user, $newConfirmationCode);
                        if ($sent) {
                            $this->addFlash('status', $this->__f('Done! The confirmation code for %s has been sent via e-mail.', ['%s' => $data[$field]]));
                            $redirectToRoute = 'zikulausersmodule_account_confirmationcode';
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
                    case UsersConstant::ACTIVATED_PENDING_DELETE:
                        if ($this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS)) {
                            $this->addFlash('error', $this->__('Sorry! Your account is marked for removal. Please contact a site administrator for more information.'));
                        }
                        $redirectToRoute = 'zikulausersmodule_account_menu';
                        break;
                    case UsersConstant::ACTIVATED_PENDING_REG:
                        $displayPendingApproval = $this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS);
                        $displayPendingVerification = $this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
                        if ($displayPendingApproval || $displayPendingVerification) {
                            $registrationsModerated = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
                            if ($registrationsModerated) {
                                $registrationApprovalOrder = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
                                if (!$user->isApproved() && ($registrationApprovalOrder == UsersConstant::APPROVAL_BEFORE)) {
                                    $this->addFlash('error', $this->__('Sorry! Your registration request is still waiting for approval from a site administrator.'));
                                } elseif (!$user->isVerified() && (($registrationApprovalOrder == UsersConstant::APPROVAL_AFTER) || ($registrationApprovalOrder == UsersConstant::APPROVAL_ANY)
                                        || (($registrationApprovalOrder == UsersConstant::APPROVAL_BEFORE) && $user->isApproved()))
                                ) {
                                    $this->addFlash('error', $this->__('Sorry! Your registration request is still waiting for verification of your e-mail address. Check your inbox for an e-mail message from us. If you need another verification e-mail sent, please contact a site administrator.'));
                                } else {
                                    $this->addFlash('error', $this->__('Sorry! Your account has not completed the registration process. Please contact a site administrator for more information.'));
                                }
                            } elseif (!$user->isVerified()) {
                                $this->addFlash('error', $this->__('Sorry! Your registration request is still waiting for verification of your e-mail address. Check your inbox for an e-mail message from us. If you need another verification e-mail sent, please contact a site administrator.'));
                            } else {
                                $this->addFlash('error', $this->__('Sorry! Your account has not completed the registration process. Please contact a site administrator for more information.'));
                            }
                            $redirectToRoute = 'zikulausersmodule_account_menu';
                        } else {
                            $this->addFlash('error', $this->__('Sorry! An account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.'));
                        }
                        break;
                    default:
                        $this->addFlash('error', $this->__('Sorry! An active account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.'));
                }
            } elseif (count($user) > 1) {
                // too many users
                $this->addFlash('error', $this->__('There are too many users registered with that address. Please contact the system administrator for assistance.'));
            } else {
                // no user
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
     * @todo consider click overload protection to prevent DOS
     * @Route("/lost-password/code")
     * @Template
     * @param Request $request
     * @return array
     */
    public function confirmationCodeAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $form = $this->createForm('Zikula\UsersModule\Form\AccountType\LostPasswordType', [], [
                'translator' => $this->get('translator.default'),
                'includeCode' => true,
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $map = ['uname' => $this->__('username'), 'email' => $this->__('email address')];
            $data = $form->getData();
            $field = empty($data['uname']) ? 'email' : 'uname';
            $user = $this->get('zikula_users_module.user_repository')->findBy([$field => $data[$field]]);
            if (count($user) == 1) {
                /** @var UserEntity $user */
                $user = $user[0];
                $changePasswordExpireDays = $this->getVar(UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, UsersConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD);
                $this->get('zikula_users_module.user_verification_repository')->purgeExpiredRecords($changePasswordExpireDays);
                /** @var UserVerificationEntity $userVerificationEntity */
                $userVerificationEntity = $this->get('zikula_users_module.user_verification_repository')->findOneBy(['uid' => $user->getUid(), 'changetype' => UsersConstant::VERIFYCHGTYPE_PWD]);
                if (\UserUtil::passwordsMatch($data['code'], $userVerificationEntity->getVerifycode())) {
                    \UserUtil::setPassword($data['pass'], $user->getUid());
                    $authenticationInfo = ['login_id' => $data[$field], 'pass' => $data['pass']];
                    $authenticationMethod = ['modname' => 'ZikulaUsersModule', 'method' => $field];
                    \UserUtil::loginUsing($authenticationMethod, $authenticationInfo);
                    $this->addFlash('success', $this->__('Code is confirmed. You are now logged in with your new password.'));

                    return $this->redirectToRoute('zikulausersmodule_account_menu');
                } else {
                    $this->addFlash('error', $this->__('Invalid code.'));
                }
            } elseif (count($user) > 1) {
                // too many users
                $this->addFlash('error', $this->__('There are too many users registered with that address. Please contact the system administrator for assistance.'));
            } else {
                // no user
                $this->addFlash('error', $this->__f('%s not found. Please contact the system administrator for assistance.', ['%s' => ucwords($map[$field])]));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/change-email")
     * @Template
     * @param Request $request
     * @return array
     */
    public function changeEmailAction(Request $request)
    {
        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        if ((bool)$this->getVar(UsersConstant::MODVAR_MANAGE_EMAIL_ADDRESS, UsersConstant::DEFAULT_MANAGE_EMAIL_ADDRESS) != true) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        $form = $this->createForm('Zikula\UsersModule\Form\AccountType\ChangeEmailType', [], [
                'translator' => $this->get('translator.default'),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $currentUser = $this->get('zikula_users_module.current_user');
            $code = $this->get('zikula_users_module.user_verification_repository')->setVerificationCode($currentUser->get('uid'), UsersConstant::VERIFYCHGTYPE_EMAIL, $data['email']);
            $templateArgs = [
                'uname'     => $currentUser->get('uname'),
                'email'     => $currentUser->get('email'),
                'newemail'  => $data['email'],
                'url'       => $this->get('router')->generate('zikulausersmodule_account_confirmchangedemail', ['code' => $code], RouterInterface::ABSOLUTE_URL),
            ];
            $sent = $this->get('zikula_users_module.helper.mail_helper')->sendNotification($data['email'], 'userverifyemail', $templateArgs);
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
        $emailExpireDays = $this->getVar(UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL, UsersConstant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL);
        $this->get('zikula_users_module.user_verification_repository')->purgeExpiredRecords($emailExpireDays, UsersConstant::VERIFYCHGTYPE_PWD, false);
        $currentUser = $this->get('zikula_users_module.current_user');
        /** @var UserVerificationEntity $verificationRecord */
        $verificationRecord = $this->get('zikula_users_module.user_verification_repository')->findOneBy([
            'uid' => $currentUser->get('uid'),
            'changetype' => UsersConstant::VERIFYCHGTYPE_EMAIL
        ]);
        $validCode = \UserUtil::passwordsMatch($code, $verificationRecord->getVerifycode());
        if (!$validCode) {
            $this->addFlash('error', $this->__f('Error! Your e-mail has not been found. After your request you have %s days to confirm the new e-mail address.', ['%s' => $emailExpireDays]));
        } else {
            $user = $this->get('zikula_users_module.user_repository')->find($currentUser->get('uid'));
            $user->setEmail($verificationRecord->getNewemail());
            $this->get('zikula_users_module.user_repository')->persistAndFlush($user);
            $this->get('zikula_users_module.user_verification_repository')->resetVerifyChgFor($user->getUid(), [UsersConstant::VERIFYCHGTYPE_EMAIL]);
            $this->addFlash('success', $this->__('Done! Changed your e-mail address.'));
        }

        return $this->redirectToRoute('zikulausersmodule_account_menu');
    }

    /**
     * @Route("/change-language")
     * @Template
     * @param Request $request
     * @return array
     */
    public function changeLanguageAction(Request $request)
    {
        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        $installedLanguages = \ZLanguage::getInstalledLanguageNames();
        $form = $this->createFormBuilder()
            ->add('language', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Choose language'),
                'choices' => array_flip($installedLanguages),
                'choices_as_values' => true,
                'placeholder' => $this->__('Site default'),
                'required' => false,
                'data' => \ZLanguage::getLanguageCode()
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('submit')->isClicked()) {
                $data = $form->getData();
                if ($data['language']) {
                    $request->getSession()->set('language', $data['language']);
                    $this->addFlash('success', $this->__f('Language changed to %lang', ['%lang' => $installedLanguages[$data['language']]]));
                } else {
                    $request->getSession()->remove('language');
                    $this->addFlash('success', $this->__('Language set to site default.'));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/change-password")
     * @Template
     * @param Request $request
     * @return array
     * @throws FatalErrorException|\InvalidArgumentException Thrown if there are no arguments provided or
     *                                    if the user is logged in but the user is coming from the login process or
     *                                    if the authentication information is invalid
     * @throws AccessDeniedException Thrown if the user isn't logged in and isn't coming from the login process
     */
    public function changePasswordAction(Request $request)
    {
        // Retrieve and delete any session variables being sent in before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $uid = $request->getSession()->get(UsersConstant::FORCE_PASSWORD_SESSION_UID_KEY);
        $authenticationMethod = $request->getSession()->get('authenticationMethod');
        $request->getSession()->clear();
        $currentUser = $this->get('zikula_users_module.current_user');

        // In order to change one's password, the user either must be logged in already, or specifically
        // must be coming from the login process. This is an exclusive-or. It is an error if neither is set,
        // and likewise if both are set. One or the other, please!
        if (!isset($uid) && !$currentUser->isLoggedIn()) {
            throw new AccessDeniedException();
        } elseif (isset($uid) && $currentUser->isLoggedIn()) {
            throw new FatalErrorException();
        }

        if (isset($uid)) {
            $login = true;
        } else {
            $login = false;
            $uid = $currentUser->get('uid');
        }
        $userEntity = $this->get('zikula_users_module.user_repository')->find($uid);

        $form = $this->createForm('Zikula\UsersModule\Form\AccountType\ChangePasswordType', [
            'uid' => $uid,
            'authenticationMethod' => $authenticationMethod
        ], [
                'translator' => $this->get('translator.default'),
                'passwordReminderEnabled' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED),
                'passwordReminderMandatory' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_MANDATORY)
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userEntity->setPass(\UserUtil::getHashedPassword($data['pass']));
            $userEntity->setPassreminder($data['passreminder']);
            $userEntity->delAttribute('_Users_mustChangePassword');
            $this->get('zikula_users_module.user_repository')->persistAndFlush($userEntity);
            $this->addFlash('success', $this->__('Password successfully changed.'));
            if ($login) {
                $this->get('zikula_users_module.helper.access_helper')->login($userEntity, $data['authenticationMethod']);
            }

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        return [
            'login' => $login,
            'form' => $form->createView(),
            'user' => $userEntity,
            'modvars' => $this->getVars(),
        ];
    }
}
