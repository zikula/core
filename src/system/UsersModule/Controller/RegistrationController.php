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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\UsersModule\Constant as UsersConstant;
use UserUtil;
use Zikula\Core\Event\GenericEvent;

/**
 * Class RegistrationController
 * @package Zikula\UsersModule\Controller
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
     * @param Request $request
     * @return Response
     * @throws FatalErrorException
     */
    public function selectRegistrationMethodAction(Request $request)
    {
        // access checks
        if ((bool)$request->getSession()->get('uid')) {
            // user is logged in
            return $this->redirectToRoute('zikulausersmodule_user_index');
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
        $authenticationMethodList = $this->get('zikulausersmodule.helper.authentication_method_list_helper');
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

        $arguments = [
            'authentication_info'                   => [],
            'selected_authentication_method'        => $selectedAuthenticationMethod,
            'authentication_method_display_order'   => $authenticationMethodDisplayOrder,
        ];

        return $this->render('@ZikulaUsersModule/Registration/selectRegistrationMethod.html.twig', $arguments); // form_action is this method
    }

    /**
     * Display the registration form.
     *
     * @Route("/register-form", options={"zkNoBundlePrefix"=1})
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response|RedirectResponse symfony response object
     */
    public function registerAction(Request $request)
    {
        // access checks
        if ((bool)$request->getSession()->get('uid')) {
            // user is logged in
            return $this->redirectToRoute('zikulausersmodule_user_index');
        }
        // Check if registration is enabled
        if (!$this->getVar(UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED)) {
            return $this->render('@ZikulaUsersModule/Registration/registration_disabled.html.twig');
        }
        $this->throwExceptionForBannedUserAgents($request);

        $selectedAuthenticationMethod = $request->query->get('authentication_method', []);

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
        $authenticationInfo = json_decode($request->request->get('authentication_info_ser', false), true); // in register.twig.html

        $form = $this->createForm('Zikula\UsersModule\Form\Type\RegistrationType',
            [],
            [
                'translator' => $this->get('translator.default'),
                'minimumPasswordLength' => $this->getVar(UsersConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, UsersConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH),
                'passwordReminderEnabled' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED, UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED),
                'passwordReminderMandatory' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_MANDATORY, UsersConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY),
                'antiSpamQuestion' => $this->getVar(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION, ''),
                'antiSpamAnswer' => $this->getVar(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER, '')
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $event = new GenericEvent($form->getData(), [], new ValidationProviders());
            $validators = $this->get('event_dispatcher')->dispatch('module.users.ui.validate_edit.new_registration', $event)->getData();

            // Validate the hook
            $hook = new ValidationHook($validators);
            $this->get('hook_dispatcher')->dispatch('users.ui_hooks.registration.validate_edit', $hook);
            $validators = $hook->getValidators();

            if ($form->isValid() && !$validators->hasErrors()) {
                // No errors, process the form data.
                $canLogIn = false;
                $redirectUrl = '';
                $reginfo = $form->getData();

                $registeredObj = \ModUtil::apiFunc($this->name, 'registration', 'registerNewUser', [
                    'reginfo'           => $reginfo,
                    'usernotification'  => true,
                    'adminnotification' => true
                ]);

                if (isset($registeredObj) && $registeredObj) {
                    // The main registration completed successfully.
                    if ($selectedAuthenticationMethod['modname'] != $this->name) {
                        // The selected authentication module is NOT the Users module, so make sure the user is registered
                        // with the authentication module (associate the Users module record uid with the login information).
                        $arguments = [
                            'authentication_method' => $selectedAuthenticationMethod,
                            'authentication_info'   => $authenticationInfo,
                            'uid'                   => $registeredObj['uid'],
                        ];
                        $authenticationRegistered = \ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'register', $arguments, 'Zikula_Api_AbstractAuthentication');
                        if (!$authenticationRegistered) {
                            $this->addFlash('warning', $this->__('There was a problem associating your log-in information with your account. Please contact the site administrator.'));

                            return $this->redirectToRoute('home');
                        }
                    } elseif ($this->getVar(UsersConstant::MODVAR_LOGIN_METHOD, UsersConstant::LOGIN_METHOD_UNAME) == UsersConstant::LOGIN_METHOD_EMAIL) {
                        // The authentication method IS the Users module, prepare for auto-login.
                        // The log-in user ID is the user's e-mail address.
                        $authenticationInfo = [
                            'login_id' => $registeredObj['email'],
                            // Need the unhashed password here for auto-login
                            'pass'     => $reginfo['pass'],
                        ];
                    } else {
                        // The authentication method IS the Users module, prepare for auto-login.
                        // The log-in user ID is the user's user name.
                        $authenticationInfo = [
                            'login_id' => $registeredObj['uname'],
                            // Need the unhashed password here for auto-login
                            'pass'     => $reginfo['pass'],
                        ];
                    }

                    // Allow hook-like events to process the registration...
                    $event = new GenericEvent($registeredObj);
                    $this->get('event_dispatcher')->dispatch('module.users.ui.process_edit.new_registration', $event);

                    // ...and hooks to process the registration.
                    $hook = new ProcessHook($registeredObj['uid']);
                    $this->get('hook_dispatcher')->dispatch('users.ui_hooks.registration.process_edit', $hook);

                    // If there were errors after the main registration, then make sure they can be displayed.
                    // TODO - Would this even happen?
//                        if (!empty($registeredObj['regErrors'])) {
//                            $this->view->assign('regErrors', $registeredObj['regErrors']);
//                        }

                    // Register the appropriate status or error to be displayed to the user, depending on the account's
                    // activated status, whether registrations are moderated, whether e-mail addresses need to be verified,
                    // and other sundry conditions.
                    if ($registeredObj['activated'] == UsersConstant::ACTIVATED_PENDING_REG) {
                        // The account is saved and is pending either moderator approval, e-mail verification, or both.
                        $moderation = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
                        $moderationOrder = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
                        $verifyEmail = $this->getVar(UsersConstant::MODVAR_REGISTRATION_VERIFICATION_MODE, UsersConstant::DEFAULT_REGISTRATION_VERIFICATION_MODE);

                        if (!empty($registeredObj['regErrors'])) {
                            // There were errors. This message takes precedence.
                            $this->addFlash('error', $this->__('Your registration request has been saved, however the problems listed below were detected during the registration process. Please contact the site administrator regarding the status of your request.'));
                        } elseif ($moderation && ($verifyEmail != UsersConstant::VERIFY_NO)) {
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
                    } elseif ($registeredObj['activated'] == UsersConstant::ACTIVATED_ACTIVE) {
                        // The account is saved, and is active (no moderator approval, no e-mail verification, and the user can log in now).
                        if (!empty($registeredObj['regErrors'])) {
                            // Errors. This message takes precedence.
                            $this->addFlash('error', $this->__('Your account has been created and you may now log in, however the problems listed below were detected during the registration process. Please contact the site administrator for more information.'));
                        } elseif ($this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN)) {
                            // No errors and auto-login is turned on. A simple post-log-in message.
                            $this->addFlash('status', $this->__('Done! Your account has been created.'));
                        } else {
                            // No errors, and no auto-login. A simple message telling the user he may log in.
                            $this->addFlash('status', $this->__('Done! Your account has been created and you may now log in.'));
                        }
                        $canLogIn = true;
                    } else {
                        // Shouldn't really get here out of the registration process, but cover all the bases.
                        $this->addFlash('error', $this->__('Your registration request has been saved, however the problems listed below were detected during the registration process. Please contact the site administrator regarding the status of your request.'));
                        $registeredObj['regErrors'] = $this->__('Your account status will not permit you to log in at this time. Please contact the site administrator for more information.');
                    }

                    // Notify that we are completing a registration session.
                    $arguments = ['redirecturl' => $redirectUrl];
                    $event = new GenericEvent($registeredObj, $arguments);
                    $event = $this->get('event_dispatcher')->dispatch('module.users.ui.registration.succeeded', $event);
                    $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;

                    // Set up the next state to follow this one, along with any data needed.
                    if ($canLogIn && $this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN)) {
                        // Next is auto-login.
                        $post = [
                            'authentication_method' => $selectedAuthenticationMethod,
                            'authentication_info' => $authenticationInfo,
                            'rememberme' => false,
                            'returnpage' => $this->get('router')->generate('home'),
                        ];

                        $subRequest = $request->duplicate([], $post, ['_controller' => 'ZikulaUsersModule:User:login']);
                        $httpKernel = $this->get('http_kernel');
                        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

                        return $response;
                    } elseif (!empty($redirectUrl)) {
                        // No auto-login, but a redirect URL, so send the user there next.
                        return $this->redirect($redirectUrl);
                    } elseif (!$registeredObj || !empty($registeredObj['regErrors']) || !$canLogIn) {
                        // Either some sort of error, or the user cannot yet log in. Send him to a page to display
                        // the current status message or error message.
                        return $this->redirectToRoute('home');
                    } else {
                        // No auto-login, no redirect URL, no errors, and the user can log in at this point.
                        // Send him to the login screen.
                        return $this->redirectToRoute('zikulausersmodule_user_login');
                    }
                } else {
                    // The main registration process failed.
                    $this->addFlash('error', $this->__('Error! Could not create the new user account or registration application. Please check with a site administrator before re-registering.'));

                    // Notify that we are completing a registration session.
                    $arguments = ['redirecturl' => $redirectUrl];
                    $event = new GenericEvent(null, $arguments);
                    $event = $this->get('event_dispatcher')->dispatch('module.users.ui.registration.failed', $event);
                    $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;

                    // Set the next state to folllow this one.
                    if (!empty($redirectUrl)) {
                        // A redirect URL, so send the user there.
                        return $this->redirect($redirectUrl);
                    } else {
                        // No redirect, so send the user to a page to show the current error message.
                        return $this->redirectToRoute('home');
                    }
                }
            }
        }

        // Notify that we are beginning a registration session.
        $event = new GenericEvent();
        $this->get('event_dispatcher')->dispatch('module.users.ui.registration.started', $event);

        // display the registration form
        return $this->render('@ZikulaUsersModule/Registration/register.html.twig', [
            'form' => $form->createView(),
            'authentication_method' => $selectedAuthenticationMethod,
            'authentication_info'   => $authenticationInfo,
            'registration_info'     => isset($registrationInfo) ? $registrationInfo : [],
            'modvars' => $this->getVars()
        ]);

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
}
