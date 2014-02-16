<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\UsersModule\Controller;

use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula_View;
use Zikula\Module\UsersModule\Constant as UsersConstant;
use Zikula\Module\UsersModule\Helper\AuthenticationMethodHelper;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ProcessHook;
use Zikula_Exception_Redirect;
use UserUtil;
use ModUtil;
use SecurityUtil;
use System;
use Zikula\Module\UsersModule\Controller\FormData\RegistrationForm;
use Zikula\Module\UsersModule\Helper\AuthenticationMethodListHelper;
use SessionUtil;
use Zikula_Session;
use LogUtil;
use ThemeUtil;
use ZLanguage;
use Zikula_Api_AbstractAuthentication;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * User controllers for the Users module.
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * Run after construction.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // Disable caching by default.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * @Route("/useraccount")
     *
     * Render and display the user's account panel. If he is not logged in, then redirect to the login screen.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedExceptionif the current user does not have adequate permissions to perform this function.
     */
    public function mainAction()
    {
        // Security check
        if(!UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'login', array('returnpage' => urlencode(ModUtil::url($this->name, 'user', 'index'))))));
        }

        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // The API function is called.
        $accountLinks = ModUtil::apiFunc($this->name, 'user', 'accountLinks');

        if ($accountLinks == false) {
            $this->request->getSession()->getFlashbag()->add('warning', $this->__('Error! No account links available.'));
        }

        return $this->response($this->view->assign('accountLinks', $accountLinks)
                          ->fetch('User/main.tpl'));
    }

    /**
     * @Route("/user/view")
     *
     * Display the base user form (login/lostpassword/register options).
     *
     * If the user is logged in, then he is redirected to the home page.
     *
     * @return Response symfony response object
     */
    public function viewAction()
    {
        // If user has logged in, redirect to homepage
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(System::getHomepageUrl()));
        }

        return $this->response($this->view->assign($this->getVars())
            ->fetch('User/view.tpl'));
    }

    /**
     * @Route("/register")
     *
     * Display the registration form.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * See the definition of {@link FormData\RegistrationForm}.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * Parameters passed via SERVER:
     * ------------------------------
     * string HTTP_USER_AGENT The browser user agent string, for comparison with illegal user agent strings.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module or
     *                                          if the registration information hasn't been passed correctly through the authentication module or
     *                                          if an illegal user agent was detected
     * @throws FatalErrorException Thrown if there was a problem reading the registration information or
     *                                     if the authentication module couldn't be found or
     *                                     if the registration process reaches an unknown state
     */
    public function registerAction()
    {
        // Should not be here if logged in.
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        // check permisisons
        if (!SecurityUtil::checkPermission($this->name .'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // Check if registration is enabled
        if (!$this->getVar(UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED)) {
            return $this->response($this->view->fetch('User/registration_disabled.tpl'));
        }

        // Initialize state for the state machine later on.
        $state = 'error';

        if ($this->request->isMethod('GET')) {
            // An HTTP GET, meaning either we are reentering the function from an external authenticator,
            // or we are entering the function for the very first time.
            $reentrantTokenReceived = $this->request->query->get('reentranttoken', false);
            if ($reentrantTokenReceived) {
                // We got here by reentering from an external authenticator. Grab the data we stored in session variables.
                $sessionVars = $this->request->getSession()->get('User_register', false, UsersConstant::SESSION_VAR_NAMESPACE);
                if ($sessionVars) {
                    $reentrantToken = isset($sessionVars['reentranttoken']) ? $sessionVars['reentranttoken'] : false;
                    $authenticationInfo = isset($sessionVars['authentication_info']) ? $sessionVars['authentication_info'] : array();
                    $selectedAuthenticationMethod = isset($sessionVars['authentication_method']) ? $sessionVars['authentication_method'] : array();

                    if ($reentrantToken != $reentrantTokenReceived) {
                        throw new AccessDeniedException();
                    }
                } else {
                    throw new FatalErrorException($this->__('An internal error occurred. Failed to retrieve stored registration state.'));
                }

                $state = 'authenticate';
            } else {
                // We are entering this function for the very first time.
                $selectedAuthenticationMethod = array();
                $authenticationInfo = array();

                $state = 'start';
            }
        } elseif ($this->request->isMethod('POST')) {
            // An HTTP POST, so a form was submitted in order to get into the function. There are three possibilities.
            // It could be that the user selected an authentication method, and we need to switch to that method.
            // It could be that the user supplied authentication info to send to an authentication method, and we need to do that.
            // It could be that the user submitted the actual registration form.

            $this->checkCsrfToken();

            if ($this->request->request->get('authentication_method_selector', false)) {
                // The user selected an authentication method, so we need to switch to it.
                $selectedAuthenticationMethod = $this->request->request->get('authentication_method', false);
                $authenticationInfo = array();

                $state = 'authentication_method_selector';
            } elseif ($this->request->request->get('registration_authentication_info', false)) {
                // The user submitted authentication information that needs to be processed by the authentication module.
                $authenticationInfo           = $this->request->request->get('authentication_info', array());
                $selectedAuthenticationMethod = $this->request->request->get('authentication_method', array());

                $reentrantToken = substr(SecurityUtil::generateCsrfToken(), 0, 10);

                $state = 'authenticate';
            } elseif ($this->request->request->get('registration_info', false)) {
                // The user submitted the acutal registration form, so we need to validate the entries and register him.
                $selectedAuthenticationMethod = json_decode($this->request->request->get('authentication_method_ser', false), true);

                $removePasswordReminderValidation = false;
                if ($selectedAuthenticationMethod['modname'] != 'ZikulaUsersModule') {
                    $removePasswordReminderValidation = true;
                }
                $formData = new FormData\RegistrationForm('users_register', $this->getContainer(), $removePasswordReminderValidation);
                $formData->setFromRequestCollection($this->request->request);
                $authenticationInfo = json_decode($this->request->request->get('authentication_info_ser', false), true);

                $state = 'validate';
            }
        } else {
            // Neither a POST nor a GET, so a fatal error.
            throw new AccessDeniedException();
        }

        // The state machine that handles the processing of the data from the initialization above.
        while ($state != 'stop') {
            switch ($state) {
                case 'start':
                    // Initial starting point for registration - a GET request without a reentrant token
                    // Check for illegal user agents trying to register.
                    $userAgent = $this->request->server->get('HTTP_USER_AGENT', '');
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
                    $this->getDispatcher()->dispatch('module.users.ui.registration.started', $event);

                    // Get a list of authentication methods available for registration
                    $authenticationMethodList = new AuthenticationMethodListHelper($this, array(), Zikula_Api_AbstractAuthentication::FILTER_REGISTRATION_ENABLED);

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
                    if (!isset($formData)) {
                        $formData = new FormData\RegistrationForm('users_register', $this->getContainer());
                    }

                    $state = 'stop';

                    $arguments = array(
                        'authentication_method' => $selectedAuthenticationMethod,
                        'authentication_info'   => $authenticationInfo,
                        'registration_info'     => isset($registrationInfo) ? $registrationInfo : array(),
                        'errorFields'           => isset($errorFields) ? $errorFields : array(),
                        'errorMessages'         => isset($errorMessages) ? $errorMessages : array(),
                    );

                    return $this->response($this->view->assign_by_ref('formData', $formData)
                            ->assign($arguments)
                            ->fetch('User/register.tpl'));
                    break;

                case 'display_method_selector':
                    // An authentication method to use with the user's registration has not been selected.
                    // Present the choices to the user.
                    $authenticationMethodList = new AuthenticationMethodListHelper($this, array(), Zikula_Api_AbstractAuthentication::FILTER_REGISTRATION_ENABLED);

                    // TODO - The order and availability should be set by configuration
                    $authenticationMethodDisplayOrder = array();
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
                        'authentication_info'                   => isset($authenticationInfo) ? $authenticationInfo : array(),
                        'selected_authentication_method'        => $selectedAuthenticationMethod,
                        'authentication_method_display_order'   => $authenticationMethodDisplayOrder,
                    );

                    return $this->response($this->view->assign($arguments)
                            ->fetch('User/registration_method.tpl'));
                    break;

                case 'authentication_method_selector':
                    // One of the authentication method selectors on the registration methods page was clicked.
                    if (!$selectedAuthenticationMethod || !is_array($selectedAuthenticationMethod) || empty($selectedAuthenticationMethod)
                            || !isset($selectedAuthenticationMethod['modname']) || !is_string($selectedAuthenticationMethod['modname']) || empty($selectedAuthenticationMethod['modname'])
                            || !isset($selectedAuthenticationMethod['method']) || !is_string($selectedAuthenticationMethod['method']) || empty($selectedAuthenticationMethod['method'])
                            ) {
                        throw new FatalErrorException($this->__('An invalid authentication method was selected.'));
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
                    SessionUtil::requireSession();
                    $sessionVars = array(
                        'authentication_info'   => $authenticationInfo,
                        'authentication_method' => $selectedAuthenticationMethod,
                        'reentranttoken'        => $reentrantToken,
                    );
                    $this->request->getSession()->set('User_register', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);

                    // The authentication method selected might be reentrant (it might send the user out to an external web site
                    // for authentication, and then send us back to finish the job). We need to tell the external system to where
                    // we would like to return.
                    $reentrantUrl = ModUtil::url($this->name, 'user', 'register', array('reentranttoken' => $reentrantToken), null, null, true, true);

                    // The chosen authentication method might be reentrant, and this is the point were the user might be directed
                    // outside the Zikula system for external authentication.
                    $arguments = array(
                        'authentication_info'   => $authenticationInfo,
                        'authentication_method' => $selectedAuthenticationMethod,
                        'reentrant_url'         => $reentrantUrl,
                    );
                    $checkPasswordResult = ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'checkPasswordForRegistration', $arguments, 'Zikula_Api_AbstractAuthentication');

                    // Did we get a good user? If so, then we can proceed to hook-like event and hook validation.
                    if (isset($checkPasswordResult) && $checkPasswordResult && is_array($checkPasswordResult)) {
                        if (isset($checkPasswordResult['authentication_info'])) {
                            $arguments['authentication_info'] = $checkPasswordResult['authentication_info'];
                        }
                        $uid = ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'getUidForAuthenticationInfo', $arguments, 'Zikula_Api_AbstractAuthentication');

                        if ($uid === false) {
                            if (isset($checkPasswordResult['authentication_info'])) {
                                $authenticationInfo = $checkPasswordResult['authentication_info'];
                            }

                            $removePasswordReminderValidation = false;
                            if ($selectedAuthenticationMethod['modname'] != 'ZikulaUsersModule') {
                                $removePasswordReminderValidation = true;
                            }
                            $formData = new FormData\RegistrationForm('users_register', $this->getContainer(), $removePasswordReminderValidation);

                            $registrationInfo = (isset($checkPasswordResult['registration_info']) && is_array($checkPasswordResult['registration_info'])) ? $checkPasswordResult['registration_info'] : array();
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
                            $this->request->getSession()->getFlashbag()->add('error', $this->__('The credentials you provided are already associated with an existing user account or registration request.'));
                            $state = 'display_method_selector';
                        }

                    } else {
                        if (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                            $this->request->getSession()->getFlashbag()->add('error', $this->__('We were unable to confirm your credentials with the selected service.'));
                        }
                        $state = 'display_method_selector';
                    }

                    // If we have gotten to this point in the same call to registrationMethod(), then the authentication method was not external
                    // and reentrant, so we should not need the session variable any more. If it is external and reentrant, and the
                    // user was required to exit the Zikula system for authentication on the external system, then we will not get
                    // to this point until the reentrant callback (at which point the variable should, again, not be needed
                    // anymore).
                    $this->request->getSession()->del('Users_register', UsersConstant::SESSION_VAR_NAMESPACE);

                    break;

                case 'validate':
                    // The user filled in and submitted the main registration form and it needs to be validated.
                    // Get the form data
                    $formData->getField('uname')->setData(mb_strtolower($formData->getField('uname')->getData()));
                    $formData->getField('email')->setData(mb_strtolower($formData->getField('email')->getData()));

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
                        $errorFields = ModUtil::apiFunc($this->name, 'registration', 'getRegistrationErrors', $arguments);
                    } else {
                        $errorFields = $formData->getErrorMessages();
                    }

                    // Validate the hook-like event.
                    $event = new GenericEvent($reginfo, array(), new ValidationProviders());
                    $validators = $this->getDispatcher()->dispatch('module.users.ui.validate_edit.new_registration', $event)->getData();

                    // Validate the hook
                    $hook = new ValidationHook($validators);
                    $this->dispatchHooks('users.ui_hooks.registration.validate_edit', $hook);
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

                    $registeredObj = ModUtil::apiFunc($this->name, 'registration', 'registerNewUser', array(
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
                            $authenticationRegistered = ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'register', $arguments, 'Zikula_Api_AbstractAuthentication');
                            if (!$authenticationRegistered) {
                                $this->request->getSession()->getFlashbag()->add('warning', $this->__('There was a problem associating your log-in information with your account. Please contact the site administrator.'));
                                $response = new RedirectResponse(System::normalizeUrl(System::getHomepageUrl()));
                                return $response;
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
                        $this->getDispatcher()->dispatch('module.users.ui.process_edit.new_registration', $event);

                        // ...and hooks to process the registration.
                        $hook = new ProcessHook($registeredObj['uid']);
                        $this->dispatchHooks('users.ui_hooks.registration.process_edit', $hook);

                        // If there were errors after the main registration, then make sure they can be displayed.
                        // TODO - Would this even happen?
                        if (!empty($registeredObj['regErrors'])) {
                            $this->view->assign('regErrors', $registeredObj['regErrors']);
                        }

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
                                $this->request->getSession()->getFlashbag()->add('error', $this->__('Your registration request has been saved, however the problems listed below were detected during the registration process. Please contact the site administrator regarding the status of your request.'));
                            } elseif ($moderation && ($verifyEmail != UsersConstant::VERIFY_NO)) {
                                // Pending both moderator approval, and e-mail verification. Set the appropriate message
                                // based on the order of approval/verification set.
                                if ($moderationOrder == UsersConstant::APPROVAL_AFTER) {
                                    // Verification then approval.
                                    $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified and your request must be approved before you will be able to log in. Please check your e-mail for an e-mail address verification message. Your account will not be approved until after the verification process is completed.'));
                                } elseif ($moderationOrder == UsersConstant::APPROVAL_BEFORE) {
                                    // Approval then verification.
                                    $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your registration request has been saved. Remember that your request must be approved and your e-mail address must be verified before you will be able to log in. Please check your e-mail periodically for a message from us. You will receive a message after we have reviewed your request.'));
                                } else {
                                    // Approval and verification in any order.
                                    $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified and your request must be approved before you will be able to log in. Please check your e-mail for an e-mail address verification message.'));
                                }
                            } elseif ($moderation) {
                                // Pending moderator approval only.
                                $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your registration request has been saved. Remember that your request must be approved before you will be able to log in. Please check your e-mail periodically for a message from us. You will receive a message after we have reviewed your request.'));
                            } elseif ($verifyEmail != UsersConstant::VERIFY_NO) {
                                // Pending e-mail address verification only.
                                $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified before you will be able to log in. Please check your e-mail for an e-mail address verification message.'));
                            } else {
                                // Some unknown state! Should never get here, but just in case...
                                $this->request->getSession()->getFlashbag()->add('error', $this->__('Your registration request has been saved, however your current registration status could not be determined. Please contact the site administrator regarding the status of your request.'));
                            }
                        } elseif ($registeredObj['activated'] == UsersConstant::ACTIVATED_ACTIVE) {
                            // The account is saved, and is active (no moderator approval, no e-mail verification, and the user can log in now).
                            if (!empty($registeredObj['regErrors'])) {
                                // Errors. This message takes precedence.
                                $this->request->getSession()->getFlashbag()->add('error', $this->__('Your account has been created and you may now log in, however the problems listed below were detected during the registration process. Please contact the site administrator for more information.'));
                            } elseif ($this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN)) {
                                // No errors and auto-login is turned on. A simple post-log-in message.
                                $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your account has been created.'));
                            } else {
                                // No errors, and no auto-login. A simple message telling the user he may log in.
                                $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your account has been created and you may now log in.'));
                            }
                            $canLogIn = true;
                        } else {
                            // Shouldn't really get here out of the registration process, but cover all the bases.
                            $this->request->getSession()->getFlashbag()->add('error', $this->__('Your registration request has been saved, however the problems listed below were detected during the registration process. Please contact the site administrator regarding the status of your request.'));
                            $registeredObj['regErrors'] = $this->__('Your account status will not permit you to log in at this time. Please contact the site administrator for more information.');
                        }

                        // Notify that we are completing a registration session.
                        $arguments = array(
                            'redirecturl' => $redirectUrl,
                        );
                        $event = new GenericEvent($registeredObj, $arguments);
                        $event = $this->getDispatcher()->dispatch('module.users.ui.registration.succeeded', $event);
                        $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;

                        // Set up the next state to follow this one, along with any data needed.
                        if ($canLogIn && $this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN)) {
                            // Next is auto-login. Make sure redirectUrl has a value so we know where to send the user.
                            if (empty($redirectUrl)) {
                                $redirectUrl = System::getHomepageUrl();
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
                            $redirectUrl = ModUtil::url($this->name, 'user', 'login');
                            $state = 'redirect';
                        }
                    } else {
                        // The main registration process failed.
                        $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Could not create the new user account or registration application. Please check with a site administrator before re-registering.'));

                        // Notify that we are completing a registration session.
                        $arguments = array(
                            'redirecturl' => $redirectUrl,
                        );
                        $event = new GenericEvent(null, $arguments);
                        $event = $this->getDispatcher()->dispatch('module.users.ui.registration.failed', $event);
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
                    return $this->response($this->view->fetch('User/displaystatusmsg.tpl'));
                    break;

                case 'redirect':
                    // At the end of the registration process with a redirect URL. Send the user there.
                    $state = 'stop';
                    return new RedirectResponse(System::normalizeUrl($redirectUrl));
                    break;

                case 'auto_login':
                    // At the end of the registration process that was successful with the user in a state where
                    // he can log in, and auto-login enabled. Log the user in, sending him to the page specified.
                    $state = 'stop';
                    $arguments = array(
                        'authentication_method' => $selectedAuthenticationMethod,
                        'authentication_info'   => $authenticationInfo,
                        'rememberme'            => false,
                        'returnpage'            => $redirectUrl,
                    );

                    return ModUtil::func($this->name, 'user', 'login', $arguments);
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

    /**
     * @Route("/user/lost-account-details")
     *
     * Display the lost user name / password choices.
     *
     * @return Response symfony response object
     */
    public function lostPwdUnameAction()
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        return $this->response($this->view->fetch('User/lostpwduname.tpl'));
    }

    /**
     * @Route("/user/lost-username")
     *
     * Display the account information recovery form.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string email The email address on the account of the account information to recover.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedHttpException Thrown if the parameters aren't available in either GET or POST
     */
    public function lostUnameAction()
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        $proceedToForm = true;
        $email = '';

        if ($this->request->isMethod('POST')) {
            $emailMessageSent = false;

            $this->checkCsrfToken();

            $email = $this->request->request->get('email', null);

            if (empty($email)) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! E-mail address field is empty.'));
            } else {
                // save username and password for redisplay
                $emailMessageSent = ModUtil::apiFunc($this->name, 'user', 'mailUname', array(
                    'idfield'   => 'email',
                    'id'        => $email
                ));

                if ($emailMessageSent) {
                    $this->request->getSession()->getFlashbag()->add('status', $this->__f('Done! The account information for %s has been sent via e-mail.', $email));
                    $proceedToForm = false;
                } else {
                    $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! We are unable to send the account information for that e-mail address. Please reenter your information, or contact an administrator.'));
                }
            }
        } elseif ($this->request->isMethod('GET')) {
            $email = '';
        } else {
            throw new AccessDeniedException();
        }

        if ($proceedToForm) {
            return $this->response($this->view->assign('email', $email)
                    ->fetch('User/lostuname.tpl'));
        } else {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'login')));
        }
    }

    /**
     * @Route("/user/lost-password")
     *
     * Display the lost password form.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string uname The user name on the account of the password to recover.
     * string email The email address on the account of the password to recover.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return Response symfony response object
     *
     * @throws NotFoundHttpException Thrown if the account couldn't be found
     * @throws AccessDeniedException Thrown if the parameters cannot be found in either GET or POST
     */
    public function lostPasswordAction()
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        $formStage = 'request';

        if ($this->request->isMethod('POST')) {
            $emailMessageSent = false;

            $this->checkCsrfToken();

            $uname = $this->request->request->get('uname', '');
            $email = $this->request->request->get('email', '');

            if (empty($uname) && empty($email)) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! User name and e-mail address fields are empty.'));
            } elseif (!empty($email) && !empty($uname)) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Please enter either a user name OR an e-mail address, but not both of them.'));
            } else {
                if (!empty($uname)) {
                    $idfield = 'uname';
                    $idvalue = $uname;
                } else {
                    $idfield = 'email';
                    $idvalue = $email;
                }

                $userObj = UserUtil::getVars($idvalue, false, $idfield);

                if ($userObj) {
                    if ($userObj['activated'] == UsersConstant::ACTIVATED_ACTIVE) {
                        if (!empty($userObj['pass']) && ($userObj['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
                            $emailMessageSent = ModUtil::apiFunc($this->name, 'user', 'mailConfirmationCode', array(
                                'idfield' => $idfield,
                                'id' => $idvalue
                            ));

                            if ($emailMessageSent) {
                                $this->request->getSession()->getFlashbag()->add('status', $this->__f('Done! The confirmation code for %s has been sent via e-mail.', $idvalue));
                                $formStage = 'code';
                            } elseif ($idfield == 'email') {
                                $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! We are unable to send a password recovery code for that e-mail address. Please try your user name, or contact an administrator.'));
                            } else {
                                $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! We are unable to send a password recovery code for that user name. Please try your e-mail address, contact an administrator.'));
                            }
                        } else {
                            $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! Your account is not set up to use a password to log into this site. Please recover your account information to determine your available log-in options.'));
                            $formStage = 'lostPwdUname';
                        }
                    } elseif (($userObj['activated'] == UsersConstant::ACTIVATED_INACTIVE) && ($this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS))) {
                        $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! Your account is marked as inactive. Please contact a site administrator for more information.'));
                        $formStage = 'lostPwdUname';
                    } elseif (($userObj['activated'] == UsersConstant::ACTIVATED_PENDING_DELETE) && ($this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS))) {
                        $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! Your account is marked for removal. Please contact a site administrator for more information.'));
                        $formStage = 'lostPwdUname';
                    } else {
                        throw new NotFoundHttpException($this->__('Sorry! An active account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.'));
                    }
                } else {
                    $displayPendingApproval = $this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS);
                    $displayPendingVerification = $this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);

                    if ($displayPendingApproval || $displayPendingVerification) {
                        $userObj = UserUtil::getVars($idvalue, false, $idfield, true);

                        if ($userObj) {
                            $registrationsModerated = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
                            if ($registrationsModerated) {
                                $registrationApprovalOrder = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
                                if (!$userObj['isapproved'] && ($registrationApprovalOrder == UsersConstant::APPROVAL_BEFORE)) {
                                    $message = $this->__('Sorry! Your registration request is still waiting for approval from a site administrator.');
                                    $formStage = 'lostPwdUname';
                                } elseif (!$userObj['isverified'] && (($registrationApprovalOrder == UsersConstant::APPROVAL_AFTER) || ($registrationApprovalOrder == UsersConstant::APPROVAL_ANY)
                                        || (($registrationApprovalOrder == UsersConstant::APPROVAL_BEFORE) && $userObj['isapproved']))
                                        ) {
                                    $message = $this->__('Sorry! Your registration request is still waiting for verification of your e-mail address. Check your inbox for an e-mail message from us. If you need another verification e-mail sent, please contact a site administrator.');
                                    $formStage = 'lostPwdUname';
                                } else {
                                    $message = $this->__('Sorry! Your account has not completed the registration process. Please contact a site administrator for more information.');
                                    $formStage = 'lostPwdUname';
                                }
                            } elseif (!$userObj['isverified']) {
                                $message = $this->__('Sorry! Your registration request is still waiting for verification of your e-mail address. Check your inbox for an e-mail message from us. If you need another verification e-mail sent, please contact a site administrator.');
                                $formStage = 'lostPwdUname';
                            } else {
                                $message = $this->__('Sorry! Your account has not completed the registration process. Please contact a site administrator for more information.');
                                $formStage = 'lostPwdUname';
                            }
                        } else {
                            $message = $this->__('Sorry! An account could not be located with that information. Correct your entry and try again.');
                        }
                    } else {
                        $message = $this->__('Sorry! An account could not be located with that information. Correct your entry and try again. If you have recently registered a new account with this site, we may be waiting for you to verify your e-mail address, or we might not have approved your registration request yet.');
                    }

                    $this->request->getSession()->getFlashbag()->add('error', $message);
                }
            }
        } elseif ($this->request->isMethod('GET')) {
            $uname = '';
            $email = '';
        } else {
            throw new AccessDeniedException();
        }

        if ($formStage == 'request') {
            $templateVariables = array(
                'uname' => $uname,
                'email' => $email,
            );
            return $this->response($this->view->assign($templateVariables)
                    ->fetch('User/lostpassword.tpl'));
        } elseif ($formStage == 'code') {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'lostPasswordCode')));
        } elseif ($formStage == 'lostPwdUname') {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'lostPwdUname')));
        } else {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }
    }

    /**
     * @Route("/user/lost-password/code")
     *
     * Display the lost password confirmation code entry form.
     *
     * Parameters passed via GET:
     * --------------------------
     * string email The e-mail address of the user who lost his password.
     * string uname The user name of the user who lost his password.
     * string code  The confirmation code used to enable the user to reset his password.
     *
     * Parameters passed via POST -- from the lostpasswordcode template:
     * -----------------------------------------------------------------
     * boolean setpass Must be a value of 0 (zero).
     * string  email   The e-mail address of the user who lost his password.
     * string  uname   The user name of the user who lost his password.
     * string  code    The confirmation code used to enable the user to reset his password.
     *
     * Parameters passed via POST -- from the passwordreminder template:
     * -----------------------------------------------------------------
     * string setpass         Must be a value of 1 (one).
     * string uname           The user name of the user who lost his password.
     * string newpass         The new password.
     * string newpassagain    The new password, repeated for verification.
     * string newpassreminder The new password reminder.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the parameters cannot be found in either GET or POST
     */
    public function lostPasswordCodeAction()
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        $formStage = 'code';
        $errorInfo = array();

        if ($this->request->isMethod('POST')) {
            $this->checkCsrfToken();

            $setPass = $this->request->request->get('setpass', false);

            if (!$setPass) {
                // lostpasswordcode form
                $uname = $this->request->request->get('uname', '');
                $email = $this->request->request->get('email', '');
                $code  = $this->request->request->get('code',  '');

                $newpass = '';
                $newpassagain = '';
                $newpassreminder = '';
                $passreminder = '';
            } else {
                // Reset password (passwordreminder) form
                $uname          = $this->request->request->get('uname', '');
                $newpass        = $this->request->request->get('newpass', '');
                $newpassagain   = $this->request->request->get('newpassagain', '');
                $newpassreminder= $this->request->request->get('newpassreminder', '');

                $formStage = 'setpass';
            }
        } elseif ($this->request->isMethod('GET')) {
            $setpass = false;
            $uname = $this->request->query->get('uname', '');
            $email = $this->request->query->get('email', '');
            $code = $this->request->query->get('code', '');

            $newpass = '';
            $newpassagain = '';
            $newpassreminder = '';
            $passreminder = '';
        } else {
            throw new AccessDeniedException();
        }

        if (($formStage == 'code') && ($this->request->isMethod('POST') || !empty($uname) || !empty($email) || !empty($code))) {
            // Got something to process from either GET or POST
            if (empty($uname) && empty($email)) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! User name and e-mail address fields are empty.'));
                $formStage = 'code';
            } elseif (!empty($email) && !empty($uname)) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Please enter either a user name OR an e-mail address, but not both of them.'));
                $formStage = 'code';
            } elseif (empty($code)) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Please enter the confirmation code you received in the e-mail message.'));
                $formStage = 'code';
            } else {
                if (!empty($uname)) {
                    $idfield = 'uname';
                    $idvalue = $uname;
                } else {
                    $idfield = 'email';
                    $idvalue = $email;
                }

                $checkConfArgs = array(
                    'idfield' => $idfield,
                    'id'      => $idvalue,
                    'code'    => $code,
                );
                if (ModUtil::apiFunc($this->name, 'user', 'checkConfirmationCode', $checkConfArgs)) {
                    $userObj = UserUtil::getVars($idvalue, true, $idfield);

                    if (isset($userObj) && $userObj) {
                        $passreminder = isset($userObj['passreminder']) ? $userObj['passreminder'] : '';
                        $formStage = 'setpass';
                    } else {
                        $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! Could not load that user account.'));
                        $formStage = 'error';
                    }
                } else {
                    $this->request->getSession()->getFlashbag()->add('error', $this->__("Error! The code that you have entered is invalid."));
                }
            }
        } elseif ($formStage == 'setpass') {
            $userObj = UserUtil::getVars($uname, false, 'uname');

            if ($userObj) {
                $passreminder = isset($userObj['passreminder']) ? $userObj['passreminder'] : '';

                $passwordErrors = ModUtil::apiFunc($this->name, 'registration', 'getPasswordErrors', array(
                    'uname'         => $uname,
                    'pass'          => $newpass,
                    'passagain'     => $newpassagain,
                    'passreminder'  => $newpassreminder,
                ));

                if (empty($passwordErrors)) {
                    $passwordSet = UserUtil::setPassword($newpass, $userObj['uid']);

                    if ($passwordSet) {
                        $reminderSet = UserUtil::setVar('passreminder', $newpassreminder, $userObj['uid']);

                        if (!$reminderSet) {
                            $this->request->getSession()->getFlashbag()->add('warning', $this->__('Warning! Your new password has been saved, but there was an error while trying to save your new password reminder.'));
                        } else {
                            $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your password has been reset, and you may now log in. Please keep your password in a safe place!'));
                        }
                        $formStage = 'login';
                    } else {
                        $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Your new password could not be saved.'));
                        $formStage = 'error';
                    }
                } else {
                    $errorInfo = ModUtil::apiFunc($this->name, 'user', 'processRegistrationErrorsForDisplay', array('registrationErrors' => $passwordErrors));
                }
            } else {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! Could not load that user account.'));
                $formStage = 'error';
            }
        }

        if ($formStage == 'code') {
            $templateVariables = array(
                'uname' => $uname,
                'email' => $email,
                'code'  => $code,
            );
            return $this->response($this->view->assign($templateVariables)
                    ->fetch('User/lostpasswordcode.tpl'));
        } elseif ($formStage == 'setpass') {
            $templateVariables = array(
                'uname'             => $userObj['uname'],
                'passreminder'      => $passreminder,
                'newpassreminder'   => $newpassreminder,
                'errormessages'     => (isset($errorInfo['errorMessages']) && !empty($errorInfo['errorMessages'])) ? $errorInfo['errorMessages'] : array(),
            );

            return $this->response($this->view->assign($templateVariables)
                    ->fetch('User/passwordreminder.tpl'));
        } elseif ($formStage == 'login') {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'login')));
        } else {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'lostPwdUname')));
        }
    }

    /**
     * @Route("/login")
     *
     * Display the login form, or process a user log-in request.
     *
     * This displays the main log-in screen to the user, allowing him to select a method of authenticating himself
     * to the system (if more than one authentication method is available), and to provide his credentials in
     * order to log into the site.
     *
     * Upon submitting his credentials (either through the log-in form mentioned above, or through another form
     * such as the log-in block, this processes the credentials as a log-in request.
     *
     * If the user is already logged in, then he is redirected the main Users module page.
     * Parameters passed via the $args array:
     * --------------------------------------
     * array   authentication_info   An array containing the authentication information entered by the user.
     * array   authentication_method An array containing two elements: 'modname', the authentication module name, and 'method', the
     *                                      selected authentication method as defined by the module.
     * boolean firstmethodisdefault  If to display first of authentication methods as preselected in login form, when more then one are specified (default is true).
     * boolean rememberme            True if the user should remain logged in at that computer for future visits; otherwise false.
     * string  returnpage            The URL of the page to return to if the log-in attempt is successful. (This URL must not be urlencoded.)
     * boolean from_password_change  Always true.
     *
     * Parameters passed via GET:
     * --------------------------
     * string returnpage The urlencoded URL of the page to return to if the log-in attempt is successful.
     *
     * Parameters passed via POST:
     * ---------------------------
     * array   authentication_info   An array containing the authentication information entered by the user.
     * array   authentication_method An array containing two elements: 'modname', the authentication module name, and 'method', the
     *                                      selected authentication method as defined by the module.
     * boolean rememberme            True if the user should remain logged in at that computer for future visits; otherwise false.
     * string  returnpage            The URL of the page to return to if the log-in attempt is successful. (This URL must not be urlencoded.)
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * Namespace: users
     * Variable:  User_login
     * Type:      array
     * Contents:  An array containing the information passed in via the $args array or the GET or POST variables, and additionaly, the
     *                  element 'user_obj'if the user record has been loaded. (The returnpage element must not be urlencoded when stored
     *                  on the session.)
     *
     * @return boolean|Response True on successful authentication and login, the rendered output of the appropriate
     *                        template to display the log-in form.
     *
     * @throws FatalErrorException Thrown if no arguments are provided or
     *                                    if an invalid authentication method is provided
     * @throws AccessDeniedException Thrown if no arguments are found in either GET or POST
     * @throws NotFoundHttpException Thrown if the user account couldn't be found or
     *                                      if the user credentials aren't valid
     */
    public function loginAction(array $args = array())
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        $loggedIn = false;
        $isFunctionCall = false;
        $isReentry = false;
        $firstmethodisdefault = isset($args['firstmethodisdefault']) ? $args['firstmethodisdefault'] : true;

        // Need to check for $args first, since isPost() and isGet() will have been set on the original call
        if (isset($args) && is_array($args) && !empty($args) && isset($args['from_password_change'])) {
            // We are coming in or back (reentering) from someplace else via a direct call to this function. It is likely that
            // we are coming back from a user.login.veto event handler that redirected the user to a page where he had to provide
            // more information.
            $authenticationInfo = isset($args['authentication_info']) ? $args['authentication_info'] : array();
            $selectedAuthenticationMethod = isset($args['authentication_method']) ? $args['authentication_method'] : array();
            $rememberMe         = isset($args['rememberme']) ? $args['rememberme'] : false;
            $returnPage         = isset($args['returnpage']) ? $args['returnpage'] : $this->request->query->get('returnpage', '');
            $eventType          = isset($args['event_type']) ? $args['event_type'] : false;

            $isFunctionCall = true;
        } elseif (isset($args) && !is_array($args)) {
            // Coming from a function call, but bad $args
            throw new FatalErrorException(LogUtil::getErrorMsgArgs());
        } else {
            $args['from_password_change'] = false;
        }

        if (!$args['from_password_change']) {

            // Get return page parameter. First try to get it from args and POST.
            $returnPage = (isset($args['returnpage'])) ? $args['returnpage'] : $this->request->request->get('returnpage', '');
            if (empty($returnPage)) {
                // Check if returnurl was set instead of returnpage
                $returnPage = (isset($args['returnurl'])) ? $args['returnurl'] : $this->request->request->get('returnurl', '');

                if (empty($returnPage)) {
                    // Still no return page. Try to get it from query.
                    $returnPage = urldecode($this->request->query->get('returnpage', $this->request->query->get('returnurl', '')));
                }
            }

            if ($this->request->isMethod('POST')) {
                // We got here from a POST, either from the login, the login block, or some reasonable facsimile thereof.
                if (System::getVar('anonymoussessions', false)) {
                    $this->checkCsrfToken();
                }

                $authenticationInfo = (isset($args['authentication_info'])) ? (array)$args['authentication_info'] : (array)$this->request->request->get('authentication_info', array());
                $selectedAuthenticationMethod = (isset($args['authentication_method'])) ? (array)$args['authentication_method'] : (array)$this->request->request->get('authentication_method', array());
                $rememberMe = (isset($args['rememberme'])) ? (bool)$args['rememberme'] : (bool)$this->request->request->get('rememberme', false);

                $eventType = (isset($args['event_type'])) ? $args['event_type'] : $this->request->request->get('event_type', false);
            } elseif ($this->request->isMethod('GET')) {
                $reentry = false;
                $reentrantTokenReceived = $this->request->query->get('reentranttoken', '');

                $sessionVars = $this->request->getSession()->get('User_login', array(), UsersConstant::SESSION_VAR_NAMESPACE);
                $this->request->getSession()->del('User_login', UsersConstant::SESSION_VAR_NAMESPACE);

                $reentrantToken = isset($sessionVars['reentranttoken']) ? $sessionVars['reentranttoken'] : false;

                if (!empty($reentrantTokenReceived) && ($reentrantTokenReceived == $reentrantToken)) {
                    // We are coming back (reentering) from someplace else. It is likely that we are coming back from an external
                    // authentication process initiated by an authentication module such as OpenID.
                    $authenticationInfo = isset($sessionVars['authentication_info']) ? $sessionVars['authentication_info'] : array();
                    $selectedAuthenticationMethod = isset($sessionVars['authentication_method']) ? $sessionVars['authentication_method'] : array();
                    $rememberMe         = isset($sessionVars['rememberme']) ? $sessionVars['rememberme'] : false;
                    $returnPage         = isset($sessionVars['returnpage']) ? $sessionVars['returnpage'] : $returnPage;
                    $eventType          = isset($sessionVars['event_type']) ? $sessionVars['event_type'] : false;
                    $user               = isset($sessionVars['user_obj']) ? $sessionVars['user_obj'] : null;

                    $isReentry = true;
                } else {
                    $authenticationInfo = array();
                    $selectedAuthenticationMethod = array();
                    $rememberMe         = false;
                    $eventType          = 'login_screen';
                    $user               = array();

                    $this->getDispatcher()->dispatch('module.users.ui.login.started', new GenericEvent());
                }
            } else {
                throw new AccessDeniedException();
            }
        }

        if (!isset($reentrantToken)) {
            $reentrantToken = substr(SecurityUtil::generateCsrfToken(), 0, 10);
        }

        // Any authentication information for use in this pass through login is gathered, so ensure any session variable
        // is cleared, even if we are coming in through a post or a function call that didn't gather info from the session.
        $this->request->getSession()->del('User_login', UsersConstant::SESSION_VAR_NAMESPACE);

        $authenticationMethodList = new AuthenticationMethodListHelper($this);

        if ($this->request->isMethod('POST') || $isFunctionCall || $isReentry) {
            // A form submission, or a simulated submission as a function call.
            if (isset($authenticationInfo) && is_array($authenticationInfo) && !empty($authenticationInfo)) {
                if (!isset($selectedAuthenticationMethod) || !is_array($selectedAuthenticationMethod) || empty($selectedAuthenticationMethod)
                        || !isset($selectedAuthenticationMethod['modname']) || empty($selectedAuthenticationMethod['modname'])
                        || !isset($selectedAuthenticationMethod['method']) || empty($selectedAuthenticationMethod['method'])
                        ) {
                    throw new FatalErrorException($this->__('Error! Invalid authentication method information.'));
                }

                if (ModUtil::available($selectedAuthenticationMethod['modname'])
                        && ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'isEnabledForAuthentication', $selectedAuthenticationMethod)
                        ) {
                    // The authentication method is reasonably valid, moving on to validate the user-entered credentials
                    $validateAuthenticationInfoArgs = array(
                        'authenticationMethod'  => $selectedAuthenticationMethod,
                        'authenticationInfo'    => $authenticationInfo,
                    );

                    if (ModUtil::func($selectedAuthenticationMethod['modname'], 'authentication', 'validateAuthenticationInformation', $validateAuthenticationInfoArgs)) {
                        // The authentication method and the authentication information have been validated at the UI level.
                        //
                        // Moving on to the actual authentication process. Save the submitted information in case the authentication
                        // method is external and reentrant.
                        //
                        // We're using sessions here, even though anonymous sessions might be turned off for anonymous users.
                        // If the user is trying to log in, then he's going to get a session if he's successful,
                        // so using sessions on the anonymous user just before logging in should be ok.
                        SessionUtil::requireSession();
                        $sessionVars = array(
                            'event_type'            => $eventType,
                            'returnpage'            => $returnPage,
                            'authentication_info'   => $authenticationInfo,
                            'authentication_method' => $selectedAuthenticationMethod,
                            'rememberme'            => $rememberMe,
                            'reentranttoken'        => $reentrantToken,
                        );
                        $this->request->getSession()->set('User_login', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);

                        // The authentication method selected might be reentrant (it might send the user out to an external web site
                        // for authentication, and then send us back to finish the job). We need to tell the external system to where
                        // we would like to return.
                        $reentrantUrl = ModUtil::url($this->name, 'user', 'login', array('reentranttoken' => $reentrantToken), null, null, true, true);

                        // There may be hook providers that need to be validated, so we cannot yet log in. The hook providers will
                        // need a user object to make sure they know who they're dealing with. Authenticate (so we are sure that
                        // the user is who he says he is) and get a user.
                        //
                        // The chosen authentication method might be reentrant, and this is the point were the user might be directed
                        // outside the Zikula system for external authentication.
                        $user = UserUtil::authenticateUserUsing($selectedAuthenticationMethod, $authenticationInfo, $reentrantUrl, true);

                        // Did we get a good user? If so, then we can proceed to hook validation.
                        if (isset($user) && $user && is_array($user) && isset($user['uid']) && is_numeric($user['uid'])) {
                            $validators = new ValidationProviders();
                            if ($eventType) {
                                $event = new GenericEvent($user, array(), $validators);
                                $validators  = $this->getDispatcher()->dispatch("module.users.ui.validate_edit.{$eventType}", $event)->getData();

                                $hook = new ValidationHook($validators);
                                $this->dispatchHooks("users.ui_hooks.{$eventType}.validate_edit", $hook);
                                $validators = $hook->getValidators();
                            }

                            if (!$validators->hasErrors()) {
                                // Process the edit hooks BEFORE we log in, so that any changes to the user record are recorded before we re-check
                                // the user's ability to log in. If we don't do this, then user.login.veto might trap and cancel the login attempt again.
                                if ($eventType) {
                                    $event = new GenericEvent($user, array());
                                    $this->getDispatcher()->dispatch("module.users.ui.process_edit.{$eventType}", $event);

                                    $hook = new ProcessHook($user['uid']);
                                    $this->dispatchHooks("users.ui_hooks.{$eventType}.process_edit", $hook);
                                }

                                if (!isset($user['lastlogin']) || empty($user['lastlogin']) || ($user['lastlogin'] == '1970-01-01 00:00:00')) {
                                    $isFirstLogin = true;
                                } else {
                                    $isFirstLogin = false;
                                }

                                // Because we are passing a $user and setting checkPassword false, this call back into the authentication
                                // chain should not trigger an external re-authentication, so it should not need preparation for reentry.
                                $loggedIn = UserUtil::loginUsing($selectedAuthenticationMethod, $authenticationInfo, $rememberMe, $reentrantUrl, false, $user);

                                if (!$loggedIn) {
                                    // Because the user was preauthentication, this should never happen, but just in case...

                                    if (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                        $this->request->getSession()->getFlashbag()->add('error', $this->__('Your log-in request was not completed.'));
                                    }

                                    $eventArgs = array(
                                        'authentication_method' => $selectedAuthenticationMethod,
                                        'authentication_info'   => $authenticationInfo,
                                        'redirecturl'           => '',
                                    );
                                    $failedEvent = new GenericEvent($user, $eventArgs);
                                    $failedEvent = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $failedEvent);

                                    $redirectUrl = $failedEvent->hasArgument('redirecturl') ? $failedEvent->getArgument('redirecturl') : '';
                                    if (!empty($redirectUrl)) {
                                        return $this->redirect($redirectUrl);
                                    }
                                }
                            } else {
                                if (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                    $this->request->getSession()->getFlashbag()->add('error', $this->__('Your log-in request was not completed.'));
                                }

                                $eventArgs = array(
                                    'authentication_method' => $selectedAuthenticationMethod,
                                    'authentication_info'   => $authenticationInfo,
                                    'redirecturl'           => '',
                                );
                                $failedEvent = new GenericEvent($user, $eventArgs);
                                $failedEvent = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $failedEvent);

                                $redirectUrl = $failedEvent->hasArgument('redirecturl') ? $failedEvent->getArgument('redirecturl') : '';
                                if (!empty($redirectUrl)) {
                                    return $this->redirect($redirectUrl);
                                }
                            }
                        } else {
                            // The user with the given credentials does not exist.
                            // Check if we shall redirect to the account registration screen if a user with the given
                            // credentials does not exist..
                            if (ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'Authentication', 'redirectToRegistrationOnLoginError', array('authentication_method' => $selectedAuthenticationMethod, 'authentication_info' => $authenticationInfo))) {
                                // We shall redirect to the account registration screen, but first we need to check
                                // if the user can be created with the given credentials or if another error has
                                // occured.
                                if (ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'Authentication', 'checkPassword', array('authentication_method' => $selectedAuthenticationMethod, 'authentication_info' => $authenticationInfo, 'reentrant_url' => $reentrantUrl))) {
                                    // Redirect to account registration screen. Clear error messages and re-save session
                                    // vars for registration.
                                    $this->request->getSession()->clearMessages(Zikula_Session::MESSAGE_ERROR);
                                    $this->request->getSession()->set('User_register', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);
                                    return new RedirectResponse(ModUtil::url($this->name, 'user', 'register', array('reentranttoken' => $reentrantTokenReceived)));
                                }
                            }

                            if (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                throw new NotFoundHttpException($this->__('There is no user account matching that information, or the password you gave does not match the password on file for that account.'));
                            }

                            $eventArgs = array(
                                'authentication_method' => $selectedAuthenticationMethod,
                                'authentication_info'   => $authenticationInfo,
                                'redirecturl'           => '',
                            );
                            $failedEvent = new GenericEvent(null, $eventArgs);
                            $failedEvent = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $failedEvent);

                            $redirectUrl = $failedEvent->hasArgument('redirecturl') ? $failedEvent->getArgument('redirecturl') : '';
                            if (!empty($redirectUrl)) {
                                return new RedirectResponse(System::normalizeUrl($redirectUrl));
                            }
                        }

                        // If we have gotten to this point in the same call to login(), then the authentication method was not external
                        // and reentrant, so we should not need the session variable any more. If it is external and reentrant, and the
                        // user was required to exit the Zikula system for authentication on the external system, then we will not get
                        // to this point until the reentrant call back to login() (at which point the variable should, again, not be needed
                        // anymore).
                        $this->request->getSession()->del('User_login', UsersConstant::SESSION_VAR_NAMESPACE);

                    } else {
                        if (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                            throw new NotFoundHttpException($this->__('The credentials you entered were not valid. Please reenter the requested information and try again.'));
                        }
                    }
                } else {
                    if ($authenticationMethodList->countEnabledForAuthentication() <= 1) {
                        $this->request->getSession()->getFlashbag()->add('error', $this->__('The selected log-in method is not currently available. Please contact the site administrator for assistance.'));
                    } else {
                        $this->request->getSession()->getFlashbag()->add('error', $this->__('The selected log-in method is not currently available. Please choose another or contact the site administrator for assistance.'));
                    }
                }
            } elseif (isset($authenticationInfo) && (!is_array($authenticationInfo))) {
                throw new FatalErrorException($this->__('Error! Invalid authentication information received.'));
            }
        }

        if (!$loggedIn) {
            // Either a GET request type to initially display the login form, or a failed login attempt
            // which means the login form should be displayed anyway.
            if ((!isset($selectedAuthenticationMethod) || empty($selectedAuthenticationMethod))
                    && ($firstmethodisdefault || $authenticationMethodList->countEnabledForAuthentication() <= 1)
                    ) {
                /* @var AuthenticationMethodHelper $authenticationMethod */
                $authenticationMethod = $authenticationMethodList->getAuthenticationMethodForDefault();
                $selectedAuthenticationMethod = array(
                    'modname'   => $authenticationMethod->modname,
                    'method'    => $authenticationMethod->method,
                );
            }

            // TODO - The order and availability should be set by configuration
            $authenticationMethodDisplayOrder = array();
            foreach ($authenticationMethodList as $authenticationMethod) {
                if ($authenticationMethod->isEnabledForAuthentication()) {
                    $authenticationMethodDisplayOrder[] = array(
                        'modname'   => $authenticationMethod->modname,
                        'method'    => $authenticationMethod->method,
                    );
                }
            }

            $templateArgs = array(
                'returnpage'                            => isset($returnPage) ? $returnPage : '',
                'authentication_info'                   => isset($authenticationInfo) ? $authenticationInfo : array(),
                'selected_authentication_method'        => $selectedAuthenticationMethod,
                'authentication_method_display_order'   => $authenticationMethodDisplayOrder,
                'user_obj'                              => isset($user) ? $user : array(),
            );
            return $this->response($this->view->assign($templateArgs)
                    ->fetch('User/login.tpl'));
        } else {
            $eventArgs = array(
                'authentication_method' => $selectedAuthenticationMethod,
                'redirecturl'           => $returnPage,
            );

            if (isset($isFirstLogin)) {
                $eventArgs['is_first_login'] = $isFirstLogin;
            }

            $event = new GenericEvent($user, $eventArgs);
            $event = $this->getDispatcher()->dispatch('module.users.ui.login.succeeded', $event);

            $returnPage = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $returnPage;

            if (empty($returnPage)) {
                $returnPage = System::getHomepageUrl();
            }

            // A successful login.
            if ($this->getVar(UsersConstant::MODVAR_LOGIN_WCAG_COMPLIANT, 1) == 1) {
                // WCAG compliant login
                return new RedirectResponse(System::normalizeUrl($returnPage));
            } else {
                // meta refresh
                $this->printRedirectPage($this->__('You are being logged-in. Please wait...'), $returnPage);

                return true;
            }
        }
    }

    /**
     * @Route("/logout")
     *
     * Log a user out.
     *
     * The user is redirected to the entry point of the site, or to a redirect
     * page if specified in the site configuration.
     *
     * Parameters:
     * string  returnpage The URL of the page to return to if the log-out attempt is successful. (This URL must not be urlencoded.)
     *
     * @return Response
     */
    public function logoutAction()
    {
        $login_redirect = $this->getVar('login_redirect');
        $returnpage     = $this->request->query->get('returnpage', isset($args['returnpage']) ? $args['returnpage'] : System::getHomepageUrl());

        // start logout event
        $uid = UserUtil::getVar('uid');
        $userObj = UserUtil::getVars($uid);
        $authenticationMethod = SessionUtil::getVar('authentication_method', array('modname' => '', 'method' => ''), UsersConstant::SESSION_VAR_NAMESPACE);
        if (UserUtil::logout()) {
            $event = new GenericEvent($userObj, array(
                'authentication_method' => $authenticationMethod,
                'uid'                   => $uid,
            ));
            $this->getDispatcher()->dispatch('module.users.ui.logout.succeeded', $event);

            if ($login_redirect == 1) {
                // WCAG compliant logout - we redirect to index.php because
                // we might no have the permission for the recent site any longer
                return new RedirectResponse(System::normalizeUrl($returnpage));
            } else {
                // meta refresh
                $this->printRedirectPage($this->__('Done! You have been logged out.'), $returnpage);
            }
        } else {
            $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! You have not been logged out.'));
        }

        return new PlainResponse();
    }

    /**
     * @Route("/user/verify-registration")
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
     * Parameters passed via GET:
     * --------------------------
     * string uname      The user name of the user who is verifying his e-mail address for registration.
     * string verifycode The code sent to the user in order to verify his e-mail address.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string uname           The user name of the user who is verifying his e-mail address for registration.
     * string verifycode      The code sent to the user in order to verify his e-mail address.
     * string newpass         If the user needs to set his password (the admin created the account record and did not create a password
     *                              at that time), then this contains the user's new password.
     * string newpassagain    The new password repeated for verification.
     * string newpassreminder The new password reminder.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return Response|bool Symfony response object; true on redirect; false on error.
     *
     * @throws AccessDeniedException Thrown if there are no arguments in either GET or POST
     */
    public function verifyRegistrationAction()
    {
        if (UserUtil::isLoggedIn()) {
            $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! An account cannot be verified while you are logged in.'));
        }

        if ($this->request->isMethod('GET')) {
            $uname      = $this->request->query->get('uname', '');
            $verifycode = $this->request->query->get('verifycode', '');
        } elseif ($this->request->isMethod('POST')) {
            $this->checkCsrfToken();
            $uname          = $this->request->request->get('uname', '');
            $verifycode     = $this->request->request->get('verifycode', '');
            $newpass        = $this->request->request->get('newpass', '');
            $newpassagain   = $this->request->request->get('newpassagain', '');
            $newpassreminder= $this->request->request->get('newpassreminder', '');
        } else {
            throw new AccessDeniedException();
        }

        if ($uname) {
            $uname = mb_strtolower($uname);
        }
        $setPass = false;

        if ($uname && $verifycode) {
            // Both a user name and verification code were submitted

            $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uname' => $uname));

            if ($reginfo) {
                if (!isset($reginfo['pass']) || empty($reginfo['pass'])) {
                    $setPass = true;

                    if ($this->request->isMethod('POST')) {
                        $passwordErrors = ModUtil::apiFunc($this->name, 'registration', 'getPasswordErrors', array(
                            'uname'         => $uname,
                            'pass'          => $newpass,
                            'passagain'     => $newpassagain,
                            'passreminder'  => $newpassreminder,
                        ));

                        if (empty($passwordErrors)) {
                            $newpassHash = UserUtil::getHashedPassword($newpass);;
                            $passSaved = UserUtil::setVar('pass', $newpassHash, $reginfo['uid']);
                            if (!$passSaved) {
                                $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! There was an error while trying to save your new password and reminder.'));
                            } else {
                                $reginfo['pass'] = $newpassHash;
                            }

                            $passReminderSaved = UserUtil::setVar('passreminder', $newpassreminder, $reginfo['uid']);
                            if (!$passReminderSaved) {
                                $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! There was an error while trying to save your new password and reminder.'));
                            } else {
                                $reginfo['passreminder'] = $newpassreminder;
                            }
                        }
                    }
                }

                if ($verifycode && $reginfo && isset($reginfo['pass']) && !empty($reginfo['pass'])) {
                    $verifyChg = ModUtil::apiFunc($this->name, 'registration', 'getVerificationCode', array(
                        'uid'   => $reginfo['uid'],
                    ));

                    if ($verifyChg) {
                        $codesMatch = UserUtil::passwordsMatch($verifycode, $verifyChg['verifycode']);

                        if ($codesMatch) {
                            $verified = ModUtil::apiFunc($this->name, 'registration', 'verify', array('reginfo' => $reginfo));

                            if ($verified) {

                                if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                    $regErrorsMessage = $this->__('There were some problems detected during the verification process. Please contact the site administrator regarding the status of your verification.');
                                    $this->view->assign('regErrors', $verified['regErrors']);
                                }

                                $extAuthModuleUsed = ($verified['pass'] == UsersConstant::PWD_NO_USERS_AUTHENTICATION);

                                switch ($verified['activated']) {
                                    case UsersConstant::ACTIVATED_PENDING_REG:
                                        if (empty($verified['approved_by'])) {
                                            $message = $this->__('Done! Your account has been verified, and is awaiting administrator approval.');
                                        } else {
                                            $message = $this->__('Done! Your account has been verified. Your registration request is still pending completion. Please contact the site administrator for more information.');
                                        }
                                        $this->request->getSession()->getFlashbag()->add('status', $message);
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $this->request->getSession()->getFlashbag()->add('status', $regErrorsMessage);
                                        }
                                        return $this->response($this->view->fetch('User/displaystatusmsg.tpl'));
                                        break;
                                    case UsersConstant::ACTIVATED_ACTIVE:
                                        if (!$extAuthModuleUsed) {
                                            // The users module was used to register that account.
                                            $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your account has been verified. You may now log in with your user name and password.'));
                                        } else {
                                            // A third party module was used to register that account.
                                            $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your account has been verified. You may now log in.'));
                                        }
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $this->request->getSession()->getFlashbag()->add('status', $regErrorsMessage);
                                            return $this->response($this->view->fetch('User/displaystatusmsg.tpl'));
                                        } else {
                                            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'login')));
                                        }
                                        break;
                                    default:
                                        $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Your account has been verified.'));
                                        $this->request->getSession()->getFlashbag()->add('status', $this->__('Your new account is not active yet. Please contact the site administrator for more information.'));
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $this->request->getSession()->getFlashbag()->add('status', $regErrorsMessage);
                                        }
                                        return $this->response($this->view->fetch('User/displaystatusmsg.tpl'));
                                        break;
                                }
                            } else {
                                if (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                    $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! There was an error while marking your registration as verifed. Please contact an administrator.'));
                                } else {
                                    return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
                                }
                            }
                        } else {
                            $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! The verification code you provided does not match our records. Please check the code, and also check your e-mail for a newer verification code that might have been sent.'));
                        }
                    } elseif ($verifyChg === false) {
                        $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! There was a problem retrieving the verification code for comparison.'));

                        return false;
                    } else {
                        $this->request->getSession()->getFlashbag()->add('error', $this->__f('Error! There is no pending verification code for \'%1$s\'. Please contact the site administrator.', array($reginfo['uname'])));

                        return false;
                    }
                }
                // No code, or no password. Pass down through to the template rendering.
            } else {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! A registration does not exist for the user name you provided. Maybe your request has expired? Please check the user name, or contact an administrator.'));
            }
        }

        if (isset($passwordErrors) && !empty($passwordErrors)) {
            $errorInfo = ModUtil::apiFunc($this->name, 'user', 'processRegistrationErrorsForDisplay', array('registrationErrors' => $passwordErrors));
        } else {
            $errorInfo = array();
        }
        $rendererArgs = array(
            'verify_uname'      => $uname,
            'verifycode'        => $verifycode,
            'reginfo'           => isset($reginfo) ? $reginfo : array(),
            'setpass'           => $setPass,
            'newpass'           => isset($newpass) ? $newpass : '',
            'newpassreminder'   => isset($newpassreminder) ? $newpassreminder : '',
            'errormessages'     => (isset($errorInfo['errorMessages']) && !empty($errorInfo['errorMessages'])) ? $errorInfo['errorMessages'] : array(),
        );

        return $this->response($this->view->assign($rendererArgs)
                          ->fetch('User/verifyregistration.tpl'));
    }

    /**
     * LEGACY user account activation.
     *
     * We must keep this function, because there is no way to know whether an inactive account
     * is inactive because it needs activation, or for some other reason set manually by the site admin.
     *
     * Parameters passed via GET:
     * --------------------------
     * string code Confirmation/Activation code.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string code Confirmation/Activation code.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return RedirectResponse
     */
    public function activationAction(array $args = array())
    {
        if ($this->request->query->has('code')) {
            $code = $this->request->query->get('code');
        } elseif ($this->request->request->has('code')) {
            $code = $this->request->request->get('code');
        } else {
            $code = isset($args['code']) ? $args['code'] : null;
        }
        $code = base64_decode($code);
        $code = explode('#', $code);

        if (!isset($code[0]) || !isset($code[1])) {
            $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Could not activate your account. Please contact the site administrator.'));
        }
        $uid = $code[0];
        $code = $code[1];

        // Get user Regdate
        $regdate = UserUtil::getVar('user_regdate', $uid);

        // Checking length in case the date has been stripped from its space in the mail.
        if (strlen($code) == 18) {
            if (!strpos($code, ' ')) {
                $code = substr($code, 0, 10) . ' ' . substr($code, -8);
            }
        }

        if (hash('md5', $regdate) == hash('md5', $code)) {
            $returncode = ModUtil::apiFunc($this->name, 'registration', 'activateUser',
                                       array('uid'     => $uid,
                                             'regdate' => $regdate));

            if (!$returncode) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Could not activate your account. Please contact the site administrator.'));
            }
            $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Account activated.'));
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'login')));
        } else {
            $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! You entered an invalid confirmation code. Please correct your entry and try again.'));
        }
    }

    /**
     * Print a (legacy) login/logout redirect page. Internal use only, not intended to be called through the API.
     *
     * @param string $message The message to display on the redirect page.
     * @param string $url     The URL of the page to redirect to after this redirect page has been displayed.
     *
     * @return PlainResponse symfony response object
     */
    private function printRedirectPage($message, $url)
    {
        $url = (!isset($url) || empty($url)) ? System::getHomepageUrl() : $url;

        // check the url
        if (substr($url, 0, 1) == '/') {
            // Root-relative links
            $url = 'http'.(System::serverGetVar('HTTPS')=='on' ? 's' : '').'://'.System::serverGetVar('HTTP_HOST').$url;
        } elseif (!preg_match('!^(?:http|https):\/\/!', $url)) {
            // Removing leading slashes from redirect url
            $url = preg_replace('!^/*!', '', $url);
            // Get base URL and append it to our redirect url
            $baseurl = System::getBaseUrl();
            $url = $baseurl.$url;
        }

        $this->view->assign('ThemeSel', System::getVar('Default_Theme'))
                ->assign('url', $url)
                ->assign('message', $message)
                ->assign('stylesheet', ThemeUtil::getModuleStylesheet($this->name))
                ->assign('redirectmessage', $this->__('If you are not automatically re-directed then please click here.'))
                ->display('User/redirectpage.tpl');

        return new PlainResponse();
    }

    /**
     * Log into a site that is currently "off" (normal logins are not allowed).
     *
     * Allows the administrator to access the site during maintenance.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string  user       The user name of the user attempting to log in.
     * string  pass       The password of the user attempting to log in.
     * boolean rememberme Whether the login session should persist.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if there are no POST parameters
     */
    public function siteOffLoginAction()
    {
        // do not process if the site is enabled
        if (!System::getVar('siteoff', false)) {
            return new RedirectResponse(System::normalizeUrl(System::getHomepageUrl()));
        }

        if ($this->request->getMethod() == 'POST') {
            $user = $this->request->request->get('user', null);
            $pass = $this->request->request->get('pass', null);
            $rememberme = $this->request->request->get('rememberme', false);
        } else {
            throw new AccessDeniedException();
        }

        $redirectUrl = System::getHomepageUrl();

        $authenticationInfo = array(
            'login_id'  => $user,
            'pass'      => $pass
        );
        $authenticationMethod = array(
            'modname'   => $this->name,
            'method'    => 'uname',
        );

        if (UserUtil::loginUsing($authenticationMethod, $authenticationInfo, $rememberme)) {
            $user = UserUtil::getVars(UserUtil::getVar('uid'));
            if (!SecurityUtil::checkPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN)) {
                UserUtil::logout();

                $eventArgs = array(
                    'authentication_method' => $authenticationMethod,
                    'redirecturl'           => '',
                );
                $event = new GenericEvent($user, $eventArgs);
                $event = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $event);
                $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;
            } else {
                $eventArgs = array(
                    'authentication_method' => $authenticationMethod,
                    'redirecturl'           => $redirectUrl,
                );
                $event = new GenericEvent($user, $eventArgs);
                $event = $this->getDispatcher()->dispatch('module.users.ui.login.succeeded', $event);
                $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;
            }
        } else {
            $eventArgs = array(
                'authentication_method' => $authenticationMethod,
                'authentication_info'   => $authenticationInfo,
                'redirecturl'           => '',
            );
            $event = new GenericEvent(null, $eventArgs);
            $event = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $event);
            $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : '';
        }

        return new RedirectResponse(System::normalizeUrl($redirectUrl));
    }

    /**
     * Display the configuration options for the users block.
     *
     * @return Response symfony response object
     *
     * @throws FatalExceptionError Thrown if the users block isn't found
     */
    public function usersBlockAction()
    {
        $blocks = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getall');
        $mid = ModUtil::getIdFromName($this->name);
        $found = false;
        foreach ($blocks as $block) {
            if ($block['mid'] == $mid && $block['bkey'] == 'user') {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new FatalErrorException();
        }

        return $this->response($this->view->assign(UserUtil::getVars(UserUtil::getVar('uid')))
                ->fetch('User/usersblock.tpl'));
    }

    /**
     * Update the custom users block.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * boolean ublockon Whether the block is displayed or not.
     * mixed   ublock   ?.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return RedirectResponse
     *
     * @return AccessDeniedHttpException Thrown if the user isn't logged in or
     *                                          if there are no post parameters
     * @throws FatalExceptionError Thrown if the users block isn't found
     */
    public function updateUsersBlockAction()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $blocks = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getall');
        $mid = ModUtil::getIdFromName($this->name);
        $found = false;
        foreach ($blocks as $block) {
            if ($block['mid'] == $mid && $block['bkey'] == 'user') {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new FatalErrorException();
        }

        if ($this->request->isMethod('POST')) {
            $ublockon = (bool)$this->request->request->get('ublockon', false);
            $ublock = (string)$this->request->request->get('ublock', '');
        } else {
            throw new AccessDeniedException();
        }

        $uid = UserUtil::getVar('uid');

        UserUtil::setVar('ublockon', $ublockon);
        UserUtil::setVar('ublock', $ublock);

        $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Saved custom block.'));
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
    }

    /**
     * @Route("/user/password")
     *
     * Display the change password form.
     *
     * Parameters passed via the $args array:
     * --------------------------------------
     * boolean login True if in the middle of a log-in attempt and changing the password via a forced password change.
     *
     * Parameters passed via GET:
     * --------------------------
     * boolean login True if in the middle of a log-in attempt and changing the password via a forced password change.
     *
     * Parameters passed via POST:
     * ---------------------------
     * boolean login True if in the middle of a log-in attempt and changing the password via a forced password change.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * Namespace: UsersConstant::SESSION_VAR_NAMESPACE
     * Variable:  User_changePassword
     * Type:      array
     * Contents:  An array containing the information saved from the log-in attempt in order to re-enter it, including:
     *              'authentication_method', an array containing the selected authentication module name and method name,
     *              'authentication_info', an array containing the authentication information entered by the user,
     *              'user_obj', a user record containing the user information found during the log-in attempt,
     *              'password_errors', errors that have occurred during a previous pass through this function.
     *
     * @return Response symfony response object
     *
     * @throws FatalErrorException Thrown if there are no arguments provided or
     *                                    if the user is logged in but the user is coming from the login process or
     *                                    if the authentication information is invalid
     * @throws AccessDeniedException Thrown if the user isn't logged in and isn't coming from the login process
     */
    public function changePasswordAction(array $args = array())
    {
        // Retrieve and delete any session variables being sent in before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $sessionVars = $this->request->getSession()->get('User_changePassword', null, UsersConstant::SESSION_VAR_NAMESPACE);
        $this->request->getSession()->del('User_changePassword', UsersConstant::SESSION_VAR_NAMESPACE);

        // The check for $args must be first, because isPost() and isGet() will be set for the function that called this one
        if (isset($args) && !empty($args) && is_array($args)) {
            // Arrived via function call

            if (!isset($args['login'])) {
                $args['login'] = false;
            }
        } elseif (isset($args) && !is_array($args)) {
            // Arrived via function call with bad $args
            throw new FatalErrorException(LogUtil::getErrorMsgArgs());
        } elseif ($this->request->isMethod('POST')) {
            // Arrived from a form post
            $args['login'] = $this->request->request->get('login', false);
        } elseif ($this->request->isMethod('GET')) {
            // Arrived from a simple URL
            $args['login'] = $this->request->query->get('login', false);
        }

        // In order to change one's password, the user either must be logged in already, or specifically
        // must be coming from the login process. This is an exclusive-or. It is an error if neither is set,
        // and likewise if both are set. One or the other, please!
        if (!$args['login'] && !UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        } elseif ($args['login'] && UserUtil::isLoggedIn()) {
            throw new FatalErrorException();
        }

        // If we are coming here from the login process, then there are certain things that must have been
        // send along in the session variable. If not, then error.
        if ($args['login'] && (!isset($sessionVars['user_obj']) || !is_array($sessionVars['user_obj'])
                || !isset($sessionVars['authentication_info']) || !is_array($sessionVars['authentication_info'])
                || !isset($sessionVars['authentication_method']) || !is_array($sessionVars['authentication_method']))
                ) {
            throw new FatalErrorException();
        }

        if ($this->getVar('changepassword', 1) != 1) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        $passwordErrors = array();
        if (isset($sessionVars['password_errors'])) {
            if (!empty($sessionVars['password_errors'])) {
                $passwordErrors = $sessionVars['password_errors'];
            }
            unset($sessionVars['password_errors']);
        }

        if ($args['login']) {
            // Pass along the session vars to updatePassword. We didn't want to just keep them in the session variable
            // /Users_Controller_User_changePassword because if we hit an exception or got redirected, then the data
            // would have been orphaned, and it contains some sensitive information.
            SessionUtil::requireSession();
            $this->request->getSession()->set('User_updatePassword', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);
        }

        // Return the output that has been generated by this function
        return $this->response($this->view->assign('password_errors', $passwordErrors)
                          ->assign('login', (bool)$args['login'])
                          ->assign('user_obj', ($args['login'] ? $sessionVars['user_obj'] : null))
                          ->assign('authentication_method', ($args['login'] ? $sessionVars['authentication_method'] : null))
                          ->fetch('User/changepassword.tpl'));
    }

    /**
     * @Route("/user/password/update")
     *
     * Update the user's password.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string oldpassword        The original password.
     * string newpassword        The new password to be stored for the user.
     * string newpasswordconfirm Verification of the new password to be stored for the user.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * Namespace: UsersConstant::SESSION_VAR_NAMESPACE
     * Variable:  User_updatePassword
     * Type:      array
     * Contents:  An array containing the information saved from the log-in attempt in order to re-enter it, including:
     *              'authentication_method', an array containing the selected authentication module name and method name,
     *              'authentication_info', an array containing the authentication information entered by the user,
     *              'user_obj', a user record containing the user information found during the log-in attempt,
     *              'password_errors', errors that have occurred during a previous pass through this function.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if there is no POST information
     * @throws FatalErrorException Thrown if there are no arguments provided or
     *                                    if the user is logged in but the user is coming from the login process or
     *                                    if there's a problem saving the new password
     */
    public function updatePasswordAction()
    {
        $sessionVars = $this->request->getSession()->get('User_updatePassword', null, UsersConstant::SESSION_VAR_NAMESPACE);
        $this->request->getSession()->del('User_updatePassword', UsersConstant::SESSION_VAR_NAMESPACE);

        if (!$this->request->isMethod('POST')) {
            throw new AccessDeniedException();
        }

        $this->checkCsrfToken();

        if (isset($sessionVars) && !empty($sessionVars)) {
            $login = true;
            $userObj = $sessionVars['user_obj'];
        } else {
            $login = false;
            $userObj = UserUtil::getVars(UserUtil::getVar('uid'), true);
        }
        $uid = $userObj['uid'];

        if (!$login && !UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        } elseif ($login && UserUtil::isLoggedIn()) {
            throw new FatalErrorException();
        }

        $passwordChanged    = false;
        $currentPassword    = $this->request->request->get('oldpassword', '');
        $newPassword        = $this->request->request->get('newpassword', '');
        $newPasswordAgain   = $this->request->request->get('newpasswordconfirm', '');
        $newPasswordReminder= $this->request->request->get('passreminder', '');
        $passwordErrors     = array();

        if (empty($currentPassword) || !UserUtil::passwordsMatch($currentPassword, $userObj['pass'])) {
            $passwordErrors['oldpass'][] = $this->__('The current password you entered is not correct. Please correct your entry and try again.');
        } else {
            $passwordErrors = ModUtil::apiFunc($this->name, 'registration', 'getPasswordErrors', array(
                'uname'         => $userObj['uname'],
                'pass'          => $newPassword,
                'passagain'     => $newPasswordAgain,
                'passreminder'  => $newPasswordReminder
            ));

            if ($login && ($currentPassword == $newPassword)) {
                $passwordErrors['reginfo_pass'][] = $this->__('Your new password cannot match your current password.');
            }
        }

        if (empty($passwordErrors)) {
            if (UserUtil::setPassword($newPassword, $uid)) {
                // no user.update event for password chagnes.

                $passwordChanged = true;

                // Clear the forced change of password flag, if it exists.
                UserUtil::delVar('_Users_mustChangePassword', $uid);

                if (!UserUtil::setVar('passreminder', $newPasswordReminder, $uid)) {
                    $this->request->getSession()->getFlashbag()->add('error', $this->__('Warning! Your new password was saved, however there was a problem saving your new password reminder.'));
                } else {
                    $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Saved your new password.'));
                }

                $userObj = UserUtil::getVars($uid, true);
                if ($login) {
                    $sessionVars['user_obj'] = $userObj;
                    if ($sessionVars['authentication_method']['modname'] == $this->name) {
                        // The password for Users module authentication was just changed.
                        // In order to successfully log in the user, we need to change it on the authentication_info.
                        $sessionVars['authentication_info']['pass'] = $newPassword;
                    }
                }
            } else {
                throw new FatalErrorException($this->__('Sorry! There was a problem saving your new password.'));
            }
        }

        if ($passwordChanged) {
            if ($login) {
                $loginArgs = $this->request->getSession()->get('User_login', array(), UsersConstant::SESSION_VAR_NAMESPACE);
                $loginArgs['authentication_method'] = $sessionVars['authentication_method'];
                $loginArgs['authentication_info']   = $sessionVars['authentication_info'];
                $loginArgs['rememberme']            = $sessionVars['rememberme'];
                $loginArgs['from_password_change']  = true;

                return ModUtil::func($this->name, 'user', 'login', $loginArgs);
            } else {
                return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
            }
        } else {
            $sessionVars['password_errors'] = $passwordErrors;
            SessionUtil::requireSession();
            $this->request->getSession()->set('User_changePassword', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'changePassword', array('login' => $login))));
        }
    }

    /**
     * @Route("/user/email")
     *
     * Display the change email address form.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    public function changeEmailAction()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        if ($this->getVar('changeemail', 1) != 1) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        return $this->response($this->view->fetch('User/changeemail.tpl'));
    }

    /**
     * @Route("/user/email/update")
     *
     * Update the email address.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string newemail      The new e-mail address to store for the user.
     * string newemailagain The new e-mail address repeated for verification.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    public function updateEmailAction()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $this->checkCsrfToken();

        $uservars = $this->getVars();
        if ($uservars['changeemail'] <> 1) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
        }

        $newemail = $this->request->request->get('newemail', '');
        $newemailagain = $this->request->request->get('newemailagain', '');

        $emailErrors = ModUtil::apiFunc($this->name, 'registration', 'getEmailErrors', array(
            'uid'           => \UserUtil::getVar('uid'),
            'email'         => $newemail,
            'emailagain'    => $newemailagain,
            'checkmode'     => 'modify',
        ));

        if (!empty($emailErrors)) {
            foreach ($emailErrors as $field => $errorList) {
                if (is_array($errorList)) {
                    // More than one error.
                    foreach ($errorList as $errorMessage) {
                        $this->request->getSession()->getFlashbag()->add('error', $errorMessage);
                    }
                } else {
                    // Only one error.
                    $this->request->getSession()->getFlashbag()->add('error', $errorList);
                }

            }
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'changeEmail')));
        }

        // save the provisional email until confimation
        $verificationSent = ModUtil::apiFunc($this->name, 'user', 'savePreEmail', array('newemail' => $newemail));

        if (!$verificationSent) {
            $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! There was a problem saving your new e-mail address or sending you a verification message.'));
        }

        $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! You will receive an e-mail to your new e-mail address to confirm the change. You must follow the instructions in that message in order to verify your new address.'));
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
    }

    /**
     * @Route("/user/lang")
     *
     * Display the form that allows the user to change the language displayed to him on the site.
     *
     * @return Response symfony response object if a form is to be displayed
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    public function changeLangAction()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        // Assign the languages
        return $this->response($this->view->assign('languages', \ZLanguage::getInstalledLanguageNames())
                ->assign('usrlang', \ZLanguage::getLanguageCode())
                ->fetch('User/changelang.tpl'));
    }

    /**
     * @Route("/user/email/confirm")
     *
     * Confirm the update of the email address.
     *
     * Available Get Parameters:
     * - confirmcode (string) The confirmation code.
     *
     * Parameters passed via the $args array:
     * --------------------------------------
     * string $args['confirmcode'] Default value for the 'confirmcode' get parameter. Allows this function to be called internally.
     *
     * Parameters passed via GET:
     * --------------------------
     * string confirmcode The confirmation code for verifying the change of e-mail address.
     *
     * Parameters passed via POST:
     * ---------------------------
     * None.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return RedirectResponse
     *
     * @throws \RuntimeException Thrown if the user isn't logged in or
     *                                  if the e-mail address hasn't be found
     */
    public function confirmChEmailAction(array $args = array())
    {
        $confirmcode = $this->request->query->get('confirmcode', isset($args['confirmcode']) ? $args['confirmcode'] : null);

        if (!UserUtil::isLoggedIn()) {
            $this->request->getSession()->getFlashbag()->add('error', $this->__('Please log into your account in order to confirm your change of e-mail address.'));
        }

        // get user new email that is waiting for confirmation
        $preemail = ModUtil::apiFunc($this->name, 'user', 'getUserPreEmail');

        $validCode = UserUtil::passwordsMatch($confirmcode, $preemail['verifycode']);

        if (!$preemail || !$validCode) {
            $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Your e-mail has not been found. After your request you have five days to confirm the new e-mail address.'));
        }

        // user and confirmation code are correct. set the new email
        UserUtil::setVar('email', $preemail['newemail']);

        // the preemail record is deleted
        ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array(
            'uid'       => $preemail['uid'],
            'changetype'=> UsersConstant::VERIFYCHGTYPE_EMAIL,
        ));

        $this->request->getSession()->getFlashbag()->add('status', $this->__('Done! Changed your e-mail address.'));
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
    }

    /**
     * Display the login screen
     *
     * @param array $args parameters for this function
     *
     * @see Users_Controller_User::login
     *
     * @return RedirectResponse
     *
     * @deprecated since 1.4.0 use loginAction instead
     */
    public function loginScreenAction($args)
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'login')), 301);
    }
}
