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
     * @Route("/register", options={"zkNoBundlePrefix"=1})
     * @Method({"GET", "POST"})
     *
     * Display the registration form.
     *
     * @param Request $request
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * See the definition of {@link FormData\RegistrationForm}.
     *
     * Parameters passed via SERVER:
     * ------------------------------
     * string HTTP_USER_AGENT The browser user agent string, for comparison with illegal user agent strings.
     *
     * @return Response|RedirectResponse symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module or
     *                                          if the registration information hasn't been passed correctly through the authentication module or
     *                                          if an illegal user agent was detected
     * @throws FatalErrorException Thrown if there was a problem reading the registration information or
     *                                     if the authentication module couldn't be found or
     *                                     if the registration process reaches an unknown state
     */
    public function registerAction(Request $request)
    {
        // Should not be here if logged in.
        if (UserUtil::isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_user_index');
        }

        // Check if registration is enabled
        if (!$this->getVar(UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED)) {
            return $this->render('@ZikulaUsersModule/Registration/registration_disabled.html.twig');
        }

        // Initialize state for the state machine later on.
        $state = 'error';

        if ($request->isMethod('GET')) {
            // An HTTP GET, meaning either we are reentering the function from an external authenticator,
            // or we are entering the function for the very first time.
            $reentrantTokenReceived = $request->query->get('reentranttoken', false);
            if ($reentrantTokenReceived) {
                // We got here by reentering from an external authenticator. Grab the data we stored in session variables.
                $sessionVars = $request->getSession()->get('User_register', false);
                if ($sessionVars) {
                    $reentrantToken = isset($sessionVars['reentranttoken']) ? $sessionVars['reentranttoken'] : false;
                    $authenticationInfo = isset($sessionVars['authentication_info']) ? $sessionVars['authentication_info'] : [];
                    $selectedAuthenticationMethod = isset($sessionVars['authentication_method']) ? $sessionVars['authentication_method'] : [];

                    if ($reentrantToken != $reentrantTokenReceived) {
                        throw new AccessDeniedException();
                    }
                } else {
                    throw new FatalErrorException($this->__('An internal error occurred. Failed to retrieve stored registration state.'));
                }

                $state = 'authenticate';
            } else {
                // We are entering this function for the very first time.
                $selectedAuthenticationMethod = [];
                $authenticationInfo = [];

                $state = 'start';
            }
        } elseif ($request->isMethod('POST')) {
            // An HTTP POST, so a form was submitted in order to get into the function. There are three possibilities.
            // It could be that the user selected an authentication method, and we need to switch to that method.
            // It could be that the user supplied authentication info to send to an authentication method, and we need to do that.
            // It could be that the user submitted the actual registration form.

//            $this->checkCsrfToken();

            if ($request->request->get('authentication_method_selector', false)) {
                // The user selected an authentication method, so we need to switch to it.
                $selectedAuthenticationMethod = $request->request->get('authentication_method', false);
                $authenticationInfo = [];

                $state = 'authentication_method_selector';
            } elseif ($request->request->get('registration_authentication_info', false)) {
                // The user submitted authentication information that needs to be processed by the authentication module.
                $authenticationInfo           = $request->request->get('authentication_info', []);
                $selectedAuthenticationMethod = $request->request->get('authentication_method', []);

                $reentrantToken = substr(\SecurityUtil::generateCsrfToken(), 0, 10);

                $state = 'authenticate';
            } elseif ($request->request->get('registration_info', false)) {
                // The user submitted the acutal registration form, so we need to validate the entries and register him.
                $selectedAuthenticationMethod = json_decode($request->request->get('authentication_method_ser', false), true);

                $removePasswordReminderValidation = false;
                if ($selectedAuthenticationMethod['modname'] != 'ZikulaUsersModule') {
                    $removePasswordReminderValidation = true;
                }
                $formData = new FormData\RegistrationForm('users_register', $this->container, $removePasswordReminderValidation);
                $formData->setFromRequestCollection($request->request);
                $authenticationInfo = json_decode($request->request->get('authentication_info_ser', false), true);

                $state = 'validate';
            }
        }

        // The state machine that handles the processing of the data from the initialization above.
        while ($state != 'stop') {
            switch ($state) {
                case 'start':
                    // Initial starting point for registration - a GET request without a reentrant token
                    // Check for illegal user agents trying to register.
                    $userAgent = $request->server->get('HTTP_USER_AGENT', '');
                    $illegalUserAgents = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS, '');
                    // Convert the comma-separated list into a regexp pattern.
                    $pattern = array('/^(\s*,\s*)+/D', '/\b(\s*,\s*)+\b/D', '/(\s*,\s*)+$/D');
                    $replace = array('', '|', '');
                    $illegalUserAgents = preg_replace($pattern, $replace, preg_quote($illegalUserAgents, '/'));
                    // Check for emptiness here, in case there were just spaces and commas in the original string.
                    if (!empty($illegalUserAgents) && preg_match("/^({$illegalUserAgents})/iD", $userAgent)) {
                        throw new AccessDeniedException($this->__('Sorry! The user agent you are using (the browser or other software you are using to access this site) is banned from the registration process.'));
                    }

                    // Notify that we are beginning a registration session.
                    $event = new GenericEvent();
                    $this->get('event_dispatcher')->dispatch('module.users.ui.registration.started', $event);

                    // Get a list of authentication methods available for registration
                    $authenticationMethodList = $this->get('zikulausersmodule.helper.authentication_method_list_helper');
                    $authenticationMethodList->initialize([], \Zikula_Api_AbstractAuthentication::FILTER_REGISTRATION_ENABLED);

                    if ($authenticationMethodList->countEnabledForRegistration() == 1 && $authenticationMethodList[0]->modname == $this->name) {
                        // There is only the default ZikulaUsersModule method available. Skip method selection.

                        $selectedAuthenticationMethod = array(
                            'modname'   => $authenticationMethodList[0]->modname,
                            'method'    => $authenticationMethodList[0]->method,
                        );

                        $state = 'display_registration';
                    } else {
                        // There are other authentication modules with methods that are enabled for registration. Display
                        // the choices to the user.
                        $state = 'display_method_selector';
                    }
                    break;

                case 'display_registration':
                    // An authentication method has been selected (or defaulted), or there were errors with the last
                    // submission of the registration form.
                    // Display the registration form to the user.
//                    if (!isset($formData)) {
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

//                        $formData = new FormData\RegistrationForm('users_register', $this->container);
//                    }

                    $state = 'stop';

                    return $this->render('@ZikulaUsersModule/Registration/register.html.twig', [
                        'form' => $form->createView(),
                        'authentication_method' => $selectedAuthenticationMethod,
                        'authentication_info'   => $authenticationInfo,
                        'registration_info'     => isset($registrationInfo) ? $registrationInfo : [],
                        'modvars' => $this->getVars()
                    ]);

//                    return new Response($this->view->assign_by_ref('formData', $formData)
//                        ->assign($arguments)
//                        ->fetch('User/register.tpl'));
                    break;

                case 'display_method_selector':
                    // An authentication method to use with the user's registration has not been selected.
                    // Present the choices to the user.
                    $authenticationMethodList = $this->get('zikulausersmodule.helper.authentication_method_list_helper');
                    $authenticationMethodList->initialize([], \Zikula_Api_AbstractAuthentication::FILTER_REGISTRATION_ENABLED);

                    // TODO - The order and availability should be set by configuration
                    $authenticationMethodDisplayOrder = [];
                    foreach ($authenticationMethodList as $am) {
                        if ($am->isEnabledForRegistration()) {
                            $authenticationMethodDisplayOrder[] = array(
                                'modname'   => $am->modname,
                                'method'    => $am->method,
                            );
                        }
                    }

                    $state = 'stop';

                    $arguments = array(
                        'authentication_info'                   => isset($authenticationInfo) ? $authenticationInfo : [],
                        'selected_authentication_method'        => $selectedAuthenticationMethod,
                        'authentication_method_display_order'   => $authenticationMethodDisplayOrder,
                    );

                    return new Response($this->view->assign($arguments)
                        ->fetch('User/registration_method.tpl'));
                    break;

                case 'authentication_method_selector':
                    // One of the authentication method selectors on the registration methods page was clicked.
                    if (!$selectedAuthenticationMethod || !is_array($selectedAuthenticationMethod) || empty($selectedAuthenticationMethod)
                        || !isset($selectedAuthenticationMethod['modname']) || !is_string($selectedAuthenticationMethod['modname']) || empty($selectedAuthenticationMethod['modname'])
                        || !isset($selectedAuthenticationMethod['method']) || !is_string($selectedAuthenticationMethod['method']) || empty($selectedAuthenticationMethod['method'])
                    ) {
                        throw new \InvalidArgumentException($this->__('An invalid authentication method was selected.'));
                    }

                    if ($selectedAuthenticationMethod['modname'] == $this->name) {
                        $state = 'display_registration';
                    } else {
                        $state = 'display_method_selector';
                    }
                    break;

                case 'authenticate':
                    // The user provided and submitted the authentication information form on the registration methods page in order
                    // to authenticate his credentials with the authentication method in order to proceed to the main registration page,
                    // OR the user is reentering the registration process after exiting to the external authentication service to
                    // authenticate his credentials.

                    // Save the submitted information in case the authentication method is external and reentrant.
                    // We're using sessions here, even though anonymous sessions might be turned off for anonymous users.
                    // If the user is trying to regiuster, then he's going to get a session if he's successful and logs in,
                    // so using sessions on the anonymous user just before registration should be ok.
                    \SessionUtil::requireSession();
                    $sessionVars = array(
                        'authentication_info'   => $authenticationInfo,
                        'authentication_method' => $selectedAuthenticationMethod,
                        'reentranttoken'        => $reentrantToken,
                    );
                    $request->getSession()->set('User_register', $sessionVars);

                    // The authentication method selected might be reentrant (it might send the user out to an external web site
                    // for authentication, and then send us back to finish the job). We need to tell the external system to where
                    // we would like to return.
                    $reentrantUrl = $this->get('router')->generate('zikulausersmodule_registration_register', array('reentranttoken' => $reentrantToken), RouterInterface::ABSOLUTE_URL);

                    // The chosen authentication method might be reentrant, and this is the point were the user might be directed
                    // outside the Zikula system for external authentication.
                    $arguments = array(
                        'authentication_info'   => $authenticationInfo,
                        'authentication_method' => $selectedAuthenticationMethod,
                        'reentrant_url'         => $reentrantUrl,
                    );
                    $checkPasswordResult = \ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'checkPasswordForRegistration', $arguments, 'Zikula_Api_AbstractAuthentication');

                    // Did we get a good user? If so, then we can proceed to hook-like event and hook validation.
                    if (isset($checkPasswordResult) && $checkPasswordResult && is_array($checkPasswordResult)) {
                        if (isset($checkPasswordResult['authentication_info'])) {
                            $arguments['authentication_info'] = $checkPasswordResult['authentication_info'];
                        }
                        $uid = \ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'getUidForAuthenticationInfo', $arguments, 'Zikula_Api_AbstractAuthentication');

                        if ($uid === false) {
                            if (isset($checkPasswordResult['authentication_info'])) {
                                $authenticationInfo = $checkPasswordResult['authentication_info'];
                            }

                            $removePasswordReminderValidation = false;
                            if ($selectedAuthenticationMethod['modname'] != 'ZikulaUsersModule') {
                                $removePasswordReminderValidation = true;
                            }
                            $formData = new FormData\RegistrationForm('users_register', $this->container, $removePasswordReminderValidation);

                            $registrationInfo = (isset($checkPasswordResult['registration_info']) && is_array($checkPasswordResult['registration_info'])) ? $checkPasswordResult['registration_info'] : [];
                            if (!empty($registrationInfo)) {
                                if (isset($registrationInfo['uname']) && !empty($registrationInfo['uname'])) {
                                    $formData->setField('uname', $registrationInfo['uname']);
                                }
                                if (isset($registrationInfo['email']) && !empty($registrationInfo['email'])) {
                                    $formData->setField('email', $registrationInfo['email']);
                                }
                                if (isset($registrationInfo['hideEmail']) && $registrationInfo['hideEmail'] == true) {
                                    $formData->setField('emailagain', $registrationInfo['email']);
                                    $this->view->assign('hideEmail', true);
                                }
                                // @todo Add this as soon as #1330 is implemented.
                                //if (isset($registrationInfo['lang']) && !empty($registrationInfo['lang'])) {
                                //    $formData->setField('lang', $registrationInfo['lang']);
                                //}

                                // @todo React to emailVerified !
                                // $registrationInfo['emailVerified']
                            }

                            $state = 'display_registration';
                        } else {
                            $this->addFlash('error', $this->__('The credentials you provided are already associated with an existing user account or registration request.'));
                            $state = 'display_method_selector';
                        }
                    } else {
                        if (!$request->getSession()->getFlashBag()->has(\Zikula_Session::MESSAGE_ERROR)) {
                            $this->addFlash('error', $this->__('We were unable to confirm your credentials with the selected service.'));
                        }
                        $state = 'display_method_selector';
                    }

                    // If we have gotten to this point in the same call to registrationMethod(), then the authentication method was not external
                    // and reentrant, so we should not need the session variable any more. If it is external and reentrant, and the
                    // user was required to exit the Zikula system for authentication on the external system, then we will not get
                    // to this point until the reentrant callback (at which point the variable should, again, not be needed
                    // anymore).
                    $request->getSession()->remove('Users_register');

                    break;

                case 'validate':
                    // The user filled in and submitted the main registration form and it needs to be validated.
                    // Get the form data
                    $formData->getField('uname')->setData(mb_strtolower($formData->getField('uname')->getData()));
                    $formData->getField('email')->setData(mb_strtolower($formData->getField('email')->getData()));
                    $formData->getField('emailagain')->setData(mb_strtolower($formData->getField('emailagain')->getData()));

                    // Set up the parameters for a call to Users_Api_Registration#getRegistrationErrors()
                    $antispamAnswer = $formData->getFieldData('antispamanswer');
                    $reginfo = $formData->toUserArray();
                    $arguments = array(
                        'checkmode'         => 'new',
                        'reginfo'           => $reginfo,
                        'passagain'         => $formData->getFieldData('passagain'),
                        'emailagain'        => $formData->getFieldData('emailagain'),
                        'antispamanswer'    => isset($antispamAnswer) ? $antispamAnswer : '',
                    );

                    if ($formData->isValid()) {
                        $errorFields = \ModUtil::apiFunc($this->name, 'registration', 'getRegistrationErrors', $arguments);
                    } else {
                        $errorFields = $formData->getErrorMessages();
                    }

                    // Validate the hook-like event.
                    $event = new GenericEvent($reginfo, [], new ValidationProviders());
                    $validators = $this->get('event-dispatcher')->dispatch('module.users.ui.validate_edit.new_registration', $event)->getData();

                    // Validate the hook
                    $hook = new ValidationHook($validators);
                    $this->get('hook_dispatcher')->dispatch('users.ui_hooks.registration.validate_edit', $hook);
                    $validators = $hook->getValidators();

                    if (empty($errorFields) && !$validators->hasErrors()) {
                        // No errors, move on to registration.
                        $state = 'register';
                    } else {
                        // There were errors with the entries on the registration form. Redisplay it.
                        $state = 'display_registration';
                    }
                    break;

                case 'register':
                    // The registration validated, so do the actual registration.
                    $canLogIn = false;
                    $redirectUrl = '';

                    $registeredObj = \ModUtil::apiFunc($this->name, 'registration', 'registerNewUser', array(
                        'reginfo'           => $reginfo,
                        'usernotification'  => true,
                        'adminnotification' => true
                    ));

                    if (isset($registeredObj) && $registeredObj) {
                        // The main registration completed successfully.
                        if ($selectedAuthenticationMethod['modname'] != $this->name) {
                            // The selected authentication module is NOT the Users module, so make sure the user is registered
                            // with the authentication module (associate the Users module record uid with the login information).
                            $arguments = array(
                                'authentication_method' => $selectedAuthenticationMethod,
                                'authentication_info'   => $authenticationInfo,
                                'uid'                   => $registeredObj['uid'],
                            );
                            $authenticationRegistered = \ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'register', $arguments, 'Zikula_Api_AbstractAuthentication');
                            if (!$authenticationRegistered) {
                                $this->addFlash('warning', $this->__('There was a problem associating your log-in information with your account. Please contact the site administrator.'));

                                return $this->redirectToRoute('home');
                            }
                        } elseif ($this->getVar(UsersConstant::MODVAR_LOGIN_METHOD, UsersConstant::LOGIN_METHOD_UNAME) == UsersConstant::LOGIN_METHOD_EMAIL) {
                            // The authentication method IS the Users module, prepare for auto-login.
                            // The log-in user ID is the user's e-mail address.
                            $authenticationInfo = array(
                                'login_id' => $registeredObj['email'],
                                // Need the unhashed password here for auto-login
                                'pass'     => $reginfo['pass'],
                            );
                        } else {
                            // The authentication method IS the Users module, prepare for auto-login.
                            // The log-in user ID is the user's user name.
                            $authenticationInfo = array(
                                'login_id' => $registeredObj['uname'],
                                // Need the unhashed password here for auto-login
                                'pass'     => $reginfo['pass'],
                            );
                        }

                        // Allow hook-like events to process the registration...
                        $event = new GenericEvent($registeredObj);
                        $this->get('event-dispatcher')->dispatch('module.users.ui.process_edit.new_registration', $event);

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
                        $arguments = array(
                            'redirecturl' => $redirectUrl,
                        );
                        $event = new GenericEvent($registeredObj, $arguments);
                        $event = $this->get('event-dispatcher')->dispatch('module.users.ui.registration.succeeded', $event);
                        $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;

                        // Set up the next state to follow this one, along with any data needed.
                        if ($canLogIn && $this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN)) {
                            // Next is auto-login. Make sure redirectUrl has a value so we know where to send the user.
                            if (empty($redirectUrl)) {
                                $redirectUrl = \System::getHomepageUrl();
                            }
                            $state = 'auto_login';
                        } elseif (!empty($redirectUrl)) {
                            // No auto-login, but a redirect URL, so send the user there next.
                            $state = 'redirect';
                        } elseif (!$registeredObj || !empty($registeredObj['regErrors']) || !$canLogIn) {
                            // Either some sort of error, or the user cannot yet log in. Send him to a page to display
                            // the current status message or error message.
                            $state = 'display_status';
                        } else {
                            // No auto-login, no redirect URL, no errors, and the user can log in at this point.
                            // Send him to the login screen.
                            $redirectUrl = $this->get('router')->generate('zikulausersmodule_user_login', [], RouterInterface::ABSOLUTE_URL);
                            $state = 'redirect';
                        }
                    } else {
                        // The main registration process failed.
                        $this->addFlash('error', $this->__('Error! Could not create the new user account or registration application. Please check with a site administrator before re-registering.'));

                        // Notify that we are completing a registration session.
                        $arguments = array(
                            'redirecturl' => $redirectUrl,
                        );
                        $event = new GenericEvent(null, $arguments);
                        $event = $this->get('event-dispatcher')->dispatch('module.users.ui.registration.failed', $event);
                        $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;

                        // Set the next state to folllow this one.
                        if (!empty($redirectUrl)) {
                            // A redirect URL, so send the user there.
                            $state = 'redirect';
                        } else {
                            // No redirect, so send the user to a page to show the current error message.
                            $state = 'display_status';
                        }
                    }
                    break;

                case 'display_status':
                    // At the end of the registration process with no where else to go.
                    // Show the user the current status message(s) or error message(s).
                    $state = 'stop';

                    return new Response($this->view->fetch('User/displaystatusmsg.tpl'));
                    break;

                case 'redirect':
                    // At the end of the registration process with a redirect URL. Send the user there.
                    $state = 'stop';

                    return new RedirectResponse(\System::normalizeUrl($redirectUrl));
                    break;

                case 'auto_login':
                    // At the end of the registration process that was successful with the user in a state where
                    // he can log in, and auto-login enabled. Log the user in, sending him to the page specified.
                    $state = 'stop';
                    $post = array(
                        'authentication_method' => $selectedAuthenticationMethod,
                        'authentication_info' => $authenticationInfo,
                        'rememberme' => false,
                        'returnpage' => $redirectUrl,
                    );

                    $subRequest = $request->duplicate(array(), $post, ['_controller' => 'ZikulaUsersModule:User:login']);
                    $httpKernel = $this->get('http_kernel');
                    $response = $httpKernel->handle(
                        $subRequest,
                        HttpKernelInterface::SUB_REQUEST
                    );

                    return $response;
                    break;

                default:
                    // An unknown processing state.
                    $state = 'stop';
                    break;
            }
        }

        // If we got here then we exited the above state machine with a 'stop', but there was no return statement
        // in the terminal state. We don't know what to do.
        throw new FatalErrorException($this->__('The registration process has entered an unknown state.'));
    }
}
