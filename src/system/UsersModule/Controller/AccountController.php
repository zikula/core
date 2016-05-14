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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\LinkContainer\LinkContainerInterface;
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
     * @param Request $request
     * @return Response|array
     */
    public function menuAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn() && !$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $accountLinks = [];
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            // get the menu links for Core-2.0 modules
            $accountLinks = $this->get('zikula.link_container_collector')->getAllLinksByType(LinkContainerInterface::TYPE_ACCOUNT);
            $legacyAccountLinksFromNew = [];
            foreach ($accountLinks as $moduleName => $links) {
                foreach ($links as $link) {
                    $legacyAccountLinksFromNew[] = [
                        'module' => $moduleName,
                        'url' => $link['url'],
                        'text' => $link['text'],
                        'icon' => $link['icon']
                    ];
                }
            }

            // @deprecated The API function is called for old-style modules
            $legacyAccountLinks = \ModUtil::apiFunc('ZikulaUsersModule', 'user', 'accountLinks');
            if (false === $legacyAccountLinks) {
                $legacyAccountLinks = [];
            }
            // add the arrays together
            $accountLinks = $legacyAccountLinksFromNew + $legacyAccountLinks;
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

        $form = $this->createForm('Zikula\UsersModule\Form\Account\Type\LostUserNameType',
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
     * @todo refactor to reduce code/simplify in controller
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

        $form = $this->createForm('Zikula\UsersModule\Form\Account\Type\LostPasswordType',
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
                        $newConfirmationCode = $this->get('zikula_users_module.user_verification_repository')->resetVerificationCode($user->getUid());
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

        $form = $this->createForm('Zikula\UsersModule\Form\Account\Type\LostPasswordType', [], [
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
}
