<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Container\HookContainer;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\RegistrationEvents;

/**
 * Class RegistrationController
 * @Route("")
 */
class RegistrationController extends AbstractController
{
    /**
     * This method is the first stage in a registration.
     * If only one registration method is available, it must redirect immediately to that method.
     * If more than one method is available, it must display these methods to the user.
     *
     * @Route("/register", options={"zkNoBundlePrefix"=1})
     * @Template
     * @param Request $request
     * @return array
     * @throws FatalErrorException
     */
    public function selectRegistrationMethodAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        // Check if registration is enabled
        if (!$this->getVar(UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED)) {
            return $this->render('@ZikulaUsersModule/Registration/registration_disabled.html.twig');
        }
        $this->throwExceptionForBannedUserAgents($request);

        // A selection was made. If selection is ZikulaUsersModule, proceed to registration stage
        $selectedAuthenticationMethod = $request->request->get('authentication_method', []);
        if (isset($selectedAuthenticationMethod['modname']) && $selectedAuthenticationMethod['modname'] == $this->name) {
            return $this->redirectToRoute('zikulausersmodule_registration_register', ['authentication_method' => $selectedAuthenticationMethod]);
        }

        // An authentication method to use with the user's registration has not been selected.
        // Present the choices to the user.
        /** @var \Zikula\UsersModule\Helper\AuthenticationMethodListHelper $authenticationMethodList */
        $authenticationMethodList = $this->get('zikula_users_module.helper.authentication_method_list_helper');
        $authenticationMethodList->initialize([], \Zikula_Api_AbstractAuthentication::FILTER_REGISTRATION_ENABLED);
        // If there is only the default ZikulaUsersModule method available. Skip method selection.
        if ($authenticationMethodList->countEnabledForRegistration() == 1 && $authenticationMethodList[0]->modname == $this->name) {
            $selectedAuthenticationMethod = [
                'modname' => $authenticationMethodList[0]->modname,
                'method' => $authenticationMethodList[0]->method,
            ];

            return $this->redirectToRoute('zikulausersmodule_registration_register', ['authentication_method' => $selectedAuthenticationMethod]);
        }

        // @todo - The order and availability should be set by configuration somewhere
        $authenticationMethodDisplayOrder = [];
        foreach ($authenticationMethodList as $authenticationMethodDisplayOrderItem) {
            if ($authenticationMethodDisplayOrderItem->isEnabledForRegistration()) {
                $authenticationMethodDisplayOrder[] = [
                    'modname'   => $authenticationMethodDisplayOrderItem->modname,
                    'method'    => $authenticationMethodDisplayOrderItem->method,
                ];
            }
        }

        return [
            'authentication_info'                   => [],
            'selected_authentication_method'        => $selectedAuthenticationMethod,
            'authentication_method_display_order'   => $authenticationMethodDisplayOrder,
        ];
    }

    /**
     * Display the registration form.
     *
     * @Route("/register-form", options={"zkNoBundlePrefix"=1})
     * @Template
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array
     */
    public function registerAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        // Check if registration is enabled
        if (!$this->getVar(UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED)) {
            return $this->render('@ZikulaUsersModule/Registration/registration_disabled.html.twig');
        }
        $this->throwExceptionForBannedUserAgents($request);

        $selectedAuthenticationMethod = $request->query->get('authentication_method', []);
        if (empty($selectedAuthenticationMethod)) {
            return $this->redirectToRoute('zikulausersmodule_registration_selectregistrationmethod');
        }

        /**
         * @todo
         *  - here must parse the $data possibly coming from authenticateRegistrationMethodAction as query params
         *    and modify the form data and options accordingly
         *      [
         *          'removePasswordReminderValidation'
         *          'authentication_info'
         *          'includeEmail'
         *          'uname'
         *          'email'
         *          'lang' ?
         *          'emailVerified' ??
         *      ]
         *  - also must deal with $removePasswordReminderValidation
         */
        $removePasswordReminderValidation = false;
        if ($selectedAuthenticationMethod['modname'] != 'ZikulaUsersModule') {
            $removePasswordReminderValidation = true;
        }
//        $authenticationInfo = json_decode($request->request->get('authentication_info_ser', false), true); // in register.twig.html

        $form = $this->createForm('Zikula\UsersModule\Form\Type\RegistrationType',
            new UserEntity(),
            [
                'translator' => $this->get('translator.default'),
                'passwordReminderEnabled' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED, UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED),
                'passwordReminderMandatory' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_MANDATORY, UsersConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY),
                'antiSpamQuestion' => $this->getVar(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION, ''),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $event = new GenericEvent($form->getData(), [], new ValidationProviders());
            $validators = $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_VALIDATE_NEW, $event)->getData();

            // Validate the hook
            $hook = new ValidationHook($validators);
            $this->get('hook_dispatcher')->dispatch(HookContainer::HOOK_REGISTRATION_VALIDATE, $hook);
            $validators = $hook->getValidators();

            if ($form->isValid() && !$validators->hasErrors()) {
                // No validation errors, process the form data.
                /** @var UserEntity $userEntity */
                $userEntity = $form->getData();
                $clearPassword = $userEntity->getPass();
                $notificationErrors = $this->get('zikula_users_module.helper.registration_helper')->registerNewUser($userEntity);

                if (!empty($notificationErrors)) {
                    // The main registration process failed.
                    $this->addFlash('error', $this->__('Error! Could not create the new user account or registration application. Please check with a site administrator before re-registering.'));
                    foreach ($notificationErrors as $notificationError) {
                        $this->addFlash('error', $notificationError);
                    }
                    // Notify that we are completing a registration session.
                    $event = $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_FAILED, new GenericEvent(null, ['redirecturl' => '']));
                    $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : '';

                    return !empty($redirectUrl) ? $this->redirect($redirectUrl) : $this->redirectToRoute('home');
                } else {
                    // The main registration completed successfully.
                    if ($selectedAuthenticationMethod['modname'] != $this->name) {
                        // The selected authentication module is NOT the Users module, so make sure the user is registered
                        // with the authentication module (associate the Users module record uid with the login information).
                        $arguments = [
                            'authentication_method' => $selectedAuthenticationMethod,
//                            'authentication_info'   => $authenticationInfo,
                            'uid'                   => $userEntity->getUid(),
                        ];
                        $authenticationRegistered = \ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'register', $arguments, 'Zikula_Api_AbstractAuthentication');
                        if (!$authenticationRegistered) {
                            $this->addFlash('warning', $this->__('There was a problem associating your log-in information with your account. Please contact the site administrator.'));

                            return $this->redirectToRoute('home');
                        }
                    } else {
                        // The authentication method IS the Users module, prepare for auto-login.
                        $loginMethodIsEmail = $this->getVar(UsersConstant::MODVAR_LOGIN_METHOD, UsersConstant::LOGIN_METHOD_UNAME) == UsersConstant::LOGIN_METHOD_EMAIL;
                        $authenticationInfo = [
                            'login_id' => $loginMethodIsEmail ? $userEntity->getEmail() : $userEntity->getUname(),
                            'pass'     => $clearPassword
                        ];
                    }

                    // Allow hook-like events to process the registration...
                    $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_PROCESS_NEW, new GenericEvent($userEntity));
                    // ...and hooks to process the registration.
                    $this->get('hook_dispatcher')->dispatch(HookContainer::HOOK_REGISTRATION_PROCESS, new ProcessHook($userEntity->getUid()));

                    // Register the appropriate status or error to be displayed to the user, depending on the account's
                    // activated status, whether registrations are moderated, whether e-mail addresses need to be verified,
                    // and other sundry conditions.
                    $canLogIn = $userEntity->getActivated() == UsersConstant::ACTIVATED_ACTIVE;
                    $autoLogIn = $this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN);
                    $this->generateRegistrationFlashMessage($userEntity->getActivated(), $autoLogIn);

                    // Notify that we are completing a registration session.
                    $event = $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_SUCCEEDED, new GenericEvent($userEntity, ['redirecturl' => '']));
                    $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : '';

                    if ($canLogIn && $autoLogIn) {
                        // Next is auto-login.
                        $post = [
                            'csrftoken' => $this->get('zikula_core.common.csrf_token_handler')->generate(),
                            'authentication_method' => $selectedAuthenticationMethod,
                            'authentication_info' => $authenticationInfo,
                            'rememberme' => false,
                            'returnpage' => $this->get('router')->generate('home'),
                        ];

                        return $this->forward('ZikulaUsersModule:User:login', [], [], $post);
                    } elseif (!empty($redirectUrl)) {
                        return $this->redirect($redirectUrl);
                    } elseif (!$canLogIn) {
                        return $this->redirectToRoute('home');
                    } else {
                        return $this->redirectToRoute('zikulausersmodule_user_login');
                    }
                }
            }
        }

        // Notify that we are beginning a registration session.
        $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_STARTED, new GenericEvent());

        return [
            'form' => $form->createView(),
            'authentication_method' => $selectedAuthenticationMethod,
//            'authentication_info'   => $authenticationInfo,
            'registration_info'     => isset($registrationInfo) ? $registrationInfo : [],
            'modvars' => $this->getVars()
        ];
    }

    /**
     * @Route("/verify-registration/{uname}/{verifycode}")
     * @Template
     * @param Request $request
     * @param null|string $uname
     * @param null|string $verifycode
     *
     * Render and process a registration e-mail verification code.
     *
     * This function will render and display to the user a form allowing him to enter
     * a verification code sent to him as part of the registration process. If the user's
     * registration does not have a password set (e.g., if an admin created the registration),
     * then he is prompted for it at this time. This function also processes the results of
     * that form, setting the registration record to verified (if appropriate), saving the password
     * (if provided) and if the registration record is also approved (or does not require it)
     * then a new user account is created.
     *
     * @return array
     */
    public function verifyAction(Request $request, $uname = null, $verifycode = null)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $setPass = false;
        if ($uname) {
            $uname = mb_strtolower($uname);
        }
        $reginfo = $this->get('zikula_users_module.helper.registration_helper')->get(null, $uname);
        if ($reginfo) {
            $setPass = !isset($reginfo['pass']) || empty($reginfo['pass']);
        }
        $form = $this->createForm('Zikula\UsersModule\Form\Type\VerifyRegistrationType',
            [
                'uname' => $uname,
                'verifycode' => $verifycode
            ],
            [
                'translator' => $this->getTranslator(),
                'setpass' => $setPass,
                'passwordReminderEnabled' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED),
                'passwordReminderMandatory' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_MANDATORY)
            ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userEntity = $this->get('zikula_users_module.user_repository')->find($reginfo['uid']);
            if (isset($data['pass'])) {
                $userEntity->setPass(\UserUtil::getHashedPassword($data['pass']));
            }
            $userEntity->setAttribute('_Users_isVerified', 1);
            if ($this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED) && isset($data['passreminder'])) {
                $userEntity->setPassreminder($data['passreminder']);
            }
            $this->get('zikula_users_module.user_repository')->persistAndFlush($userEntity);
            $this->get('zikula_users_module.user_verification_repository')->resetVerifyChgFor($userEntity->getUid(), UsersConstant::VERIFYCHGTYPE_REGEMAIL);
            $this->get('zikula_users_module.helper.registration_helper')->createUser($userEntity, true, false);

            switch ($userEntity->getActivated()) {
                case UsersConstant::ACTIVATED_PENDING_REG:
                    if ('' == $userEntity->getApproved_By()) {
                        $this->addFlash('status', $this->__('Done! Your account has been verified, and is awaiting administrator approval.'));
                    } else {
                        $this->addFlash('status', $this->__('Done! Your account has been verified. Your registration request is still pending completion. Please contact the site administrator for more information.'));
                    }
                    break;
                case UsersConstant::ACTIVATED_ACTIVE:
                    if ($userEntity->getPass() != UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
                        // The users module was used to register that account.
                        $this->addFlash('status', $this->__('Done! Your account has been verified. You may now log in with your user name and password.'));
                    } else {
                        // A third party module was used to register that account.
                        $this->addFlash('status', $this->__('Done! Your account has been verified. You may now log in.'));
                    }

                    return $this->redirectToRoute('zikulausersmodule_user_login');
                    break;
                default:
                    $this->addFlash('status', $this->__('Done! Your account has been verified.'));
                    $this->addFlash('status', $this->__('Your new account is not active yet. Please contact the site administrator for more information.'));
                    break;
            }
        }

        return [
            'form' => $form->createView(),
            'modvars' => $this->getVars()
        ];
    }

    /**
     * Display or authenticate the selected registration method with external authentication method.
     * If successful, redirect to registration.
     *
     * @Route("/authenticate-registration-method", options={"zkNoBundlePrefix"=1})
     * @param Request $request
     * @return RedirectResponse
     */
    public function authenticateRegistrationMethodAction(Request $request)
    {
        // @todo this needs to be enabled... was only when method was GET before
//        $sessionVars = $request->getSession()->get('User_register', false);
//        if ($sessionVars) {
//            $reentrantTokenReceived = $request->query->get('reentranttoken', false);
//            $reentrantToken = isset($sessionVars['reentranttoken']) ? $sessionVars['reentranttoken'] : false;
//            $authenticationInfo = isset($sessionVars['authentication_info']) ? $sessionVars['authentication_info'] : [];
//            $selectedAuthenticationMethod = isset($sessionVars['authentication_method']) ? $sessionVars['authentication_method'] : [];
//
//            if ($reentrantToken != $reentrantTokenReceived) {
//                throw new AccessDeniedException();
//            }
//        } else {
//            throw new FatalErrorException($this->__('An internal error occurred. Failed to retrieve stored registration state.'));
//        }

        // Save the submitted information in case the authentication method is external and reentrant.
        // We're using sessions here, even though anonymous sessions might be turned off for anonymous users.
        // If the user is trying to regiuster, then he's going to get a session if he's successful and logs in,
        // so using sessions on the anonymous user just before registration should be ok.
        $request->getSession()->start(); // restart?
        $authenticationInfo           = $request->request->get('authentication_info', []);
        $selectedAuthenticationMethod = $request->request->get('authentication_method', []);
        $reentrantToken = substr(\SecurityUtil::generateCsrfToken(), 0, 10);
        $sessionVars = [
            'authentication_info'   => $authenticationInfo,
            'authentication_method' => $selectedAuthenticationMethod,
            'reentranttoken'        => $reentrantToken,
        ];
        $request->getSession()->set('User_register', $sessionVars);

        // The authentication method selected might be reentrant (it might send the user out to an external web site
        // for authentication, and then send us back to finish the job). We need to tell the external system to where
        // we would like to return.
        $reentrantUrl = $this->get('router')->generate('zikulausersmodule_registration_register', ['reentranttoken' => $reentrantToken], RouterInterface::ABSOLUTE_URL);

        // The chosen authentication method might be reentrant, and this is the point were the user might be directed
        // outside the Zikula system for external authentication.
        $arguments = [
            'authentication_info'   => $authenticationInfo,
            'authentication_method' => $selectedAuthenticationMethod,
            'reentrant_url'         => $reentrantUrl,
        ];
        $checkPasswordResult = \ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'checkPasswordForRegistration', $arguments, 'Zikula_Api_AbstractAuthentication');

        // Did we get a good user? If so, then we can proceed to hook-like event and hook validation.
        if (isset($checkPasswordResult) && $checkPasswordResult && is_array($checkPasswordResult)) {
            if (isset($checkPasswordResult['authentication_info'])) {
                $arguments['authentication_info'] = $checkPasswordResult['authentication_info'];
            }
            $uid = \ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'getUidForAuthenticationInfo', $arguments, 'Zikula_Api_AbstractAuthentication');

            if ($uid === false) {
                $data = [];
                if (isset($checkPasswordResult['authentication_info'])) {
                    $data['authenticationInfo'] = $checkPasswordResult['authentication_info'];
                }

                $registrationInfo = (isset($checkPasswordResult['registration_info']) && is_array($checkPasswordResult['registration_info'])) ? $checkPasswordResult['registration_info'] : [];
                $data['removePasswordReminderValidation'] = ($selectedAuthenticationMethod['modname'] != 'ZikulaUsersModule');
                $data['includeEmail'] = true;
                if (!empty($registrationInfo)) {
                    if (isset($registrationInfo['uname']) && !empty($registrationInfo['uname'])) {
                        $data['uname'] = $registrationInfo['uname'];
                    }
                    if (isset($registrationInfo['email']) && !empty($registrationInfo['email'])) {
                        $data['email'] = $registrationInfo['email'];
                    }
                    if (isset($registrationInfo['hideEmail']) && $registrationInfo['hideEmail'] == true) {
                        $data['includeEmail'] = false;
                    }
                    // @todo Add this as soon as #1330 is implemented.
                    //if (isset($registrationInfo['lang']) && !empty($registrationInfo['lang'])) {
                    //    $data['lang'] = $registrationInfo['lang'];
                    //}

                    // @todo React to emailVerified !
                    // $registrationInfo['emailVerified']
                }
                // remove the session vars that were set prior to re-entrant authentication
                $request->getSession()->remove('User_register');

                return $this->redirectToRoute('zikulausersmodule_registration_register', $data);
            } else {
                $this->addFlash('error', $this->__('The credentials you provided are already associated with an existing user account or registration request.'));

                return $this->redirectToRoute('zikulausersmodule_registration_selectregistrationmethod');
            }
        } else {
            if (!$request->getSession()->getFlashBag()->has(\Zikula_Session::MESSAGE_ERROR)) {
                $this->addFlash('error', $this->__('Error: Unable to confirm your credentials with the selected service.'));
            }

            return $this->redirectToRoute('zikulausersmodule_registration_selectregistrationmethod');
        }
    }

    /**
     * Throw an exception if the user agent has been banned in the UserModule settings.
     *
     * @param Request $request
     * @throws AccessDeniedException if User Agent is banned.
     */
    private function throwExceptionForBannedUserAgents(Request $request)
    {
        // Check for illegal user agents trying to register.
        $userAgent = $request->server->get('HTTP_USER_AGENT', '');
        $illegalUserAgents = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS, '');
        // Convert the comma-separated list into a regexp pattern.
        $pattern = ['/^(\s*,\s*)+/D', '/\b(\s*,\s*)+\b/D', '/(\s*,\s*)+$/D'];
        $replace = ['', '|', ''];
        $illegalUserAgents = preg_replace($pattern, $replace, preg_quote($illegalUserAgents, '/'));
        // Check for emptiness here, in case there were just spaces and commas in the original string.
        if (!empty($illegalUserAgents) && preg_match("/^({$illegalUserAgents})/iD", $userAgent)) {
            throw new AccessDeniedException($this->__('Sorry! The user agent you are using (the browser or other software you are using to access this site) is banned from the registration process.'));
        }
    }

    /**
     * Add flash message to session based on registration results.
     *
     * @param bool $activatedStatus
     * @param bool $autoLogIn
     */
    private function generateRegistrationFlashMessage($activatedStatus, $autoLogIn = false)
    {
        if ($activatedStatus == UsersConstant::ACTIVATED_PENDING_REG) {
            // The account is saved and is pending either moderator approval, e-mail verification, or both.
            $moderation = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
            $moderationOrder = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
            $verifyEmail = $this->getVar(UsersConstant::MODVAR_REGISTRATION_VERIFICATION_MODE, UsersConstant::DEFAULT_REGISTRATION_VERIFICATION_MODE);

            if ($moderation && ($verifyEmail != UsersConstant::VERIFY_NO)) {
                // Pending both moderator approval, and e-mail verification. Set the appropriate message
                // based on the order of approval/verification set.
                if ($moderationOrder == UsersConstant::APPROVAL_AFTER) {
                    // Verification then approval.
                    $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified and your request must be approved before you will be able to log in. Please check your e-mail for an e-mail address verification message. Your account will not be approved until after the verification process is completed.'));
                } elseif ($moderationOrder == UsersConstant::APPROVAL_BEFORE) {
                    // Approval then verification.
                    $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your request must be approved and your e-mail address must be verified before you will be able to log in. Please check your e-mail periodically for a message from us. You will receive a message after we have reviewed your request.'));
                } else {
                    // Approval and verification in any order.
                    $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified and your request must be approved before you will be able to log in. Please check your e-mail for an e-mail address verification message.'));
                }
            } elseif ($moderation) {
                // Pending moderator approval only.
                $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your request must be approved before you will be able to log in. Please check your e-mail periodically for a message from us. You will receive a message after we have reviewed your request.'));
            } elseif ($verifyEmail != UsersConstant::VERIFY_NO) {
                // Pending e-mail address verification only.
                $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified before you will be able to log in. Please check your e-mail for an e-mail address verification message.'));
            } else {
                // Some unknown state! Should never get here, but just in case...
                $this->addFlash('error', $this->__('Your registration request has been saved, however your current registration status could not be determined. Please contact the site administrator regarding the status of your request.'));
            }
        } elseif ($activatedStatus == UsersConstant::ACTIVATED_ACTIVE) {
            // The account is saved, and is active (no moderator approval, no e-mail verification, and the user can log in now).
            if ($autoLogIn) {
                // No errors and auto-login is turned on. A simple post-log-in message.
                $this->addFlash('status', $this->__('Done! Your account has been created.'));
            } else {
                // No errors, and no auto-login. A simple message telling the user he may log in.
                $this->addFlash('status', $this->__('Done! Your account has been created and you may now log in.'));
            }
        }
    }
}
