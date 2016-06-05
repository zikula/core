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

use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula_View;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Helper\AuthenticationMethodHelper;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use UserUtil;
use ModUtil;
use SecurityUtil;
use System;
use SessionUtil;
use Zikula_Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
     * Note: No Route needed here because this is legacy-only
     * @return RedirectResponse symfony response object
     */
    public function mainAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_menu'));
    }

    /**
     * @Route("/useraccount", options={"zkNoBundlePrefix"=1})
     * @return RedirectResponse
     */
    public function indexAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_menu'));
    }

    /**
     * @Route("/view")
     * @return RedirectResponse
     */
    public function viewAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_menu'));
    }

    /**
     * @Route("/register", options={"zkNoBundlePrefix"=1})
     *
     * BC Method to forward to new Controller
     */
    public function registerAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationController::selectRegistrationMethodAction', E_USER_DEPRECATED);
        $subRequest = $this->getContainer()
            ->get('request_stack')
            ->getCurrentRequest()
            ->duplicate($request->query->all(), null, ['_controller' => 'ZikulaUsersModule:Registration:selectRegistrationMethod']);

        return $this->getContainer()->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @Route("/lost-account-details")
     * @return RedirectResponse
     */
    public function lostPwdUnameAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_menu'));
    }

    /**
     * @Route("/lost-username")
     * @return RedirectResponse
     */
    public function lostUnameAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::lostUserNameAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_lostusername'));
    }

    /**
     * @Route("/lost-password")
     * @return RedirectResponse
     */
    public function lostPasswordAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::lostPasswordAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_lostpassword'));
    }

    /**
     * @Route("/lost-password/code")
     * @return RedirectResponse
     */
    public function lostPasswordCodeAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::confirmationCodeAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_confirmationcode'));
    }

    /**
     * @Route("/login", options={"zkNoBundlePrefix"=1})
     * @Method({"GET", "POST"})
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
     *
     * @param Request $request
     *
     * Parameters passed via FORWARD:
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
     * Contents:  An array containing the information passed in via the GET or POST variables, and additionally, the
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
    public function loginAction(Request $request)
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index'));
        }

        // set default value of variables
        $loggedIn = false;
        $isFunctionCall = false;
        $isReentry = false;
        $firstmethodisdefault = true;
        $fromPasswordChange = $request->get('from_password_change', false);
        $authenticationInfo = [];
        $selectedAuthenticationMethod = [];
        $rememberMe = false;
        $returnPage = $request->query->get('returnpage', '');
        $eventType = false;

        if (!$fromPasswordChange) {
            // Get return page parameter. First try to get it from args and POST.
            $returnPage = $request->request->get('returnpage', '');
            if (empty($returnPage)) {
                // Check if returnurl was set instead of returnpage
                $returnPage = $request->request->get('returnurl', '');

                if (empty($returnPage)) {
                    // Still no return page. Try to get it from query.
                    $returnPage = urldecode($request->query->get('returnpage', $request->query->get('returnurl', '')));
                }
            }

            if ($request->isMethod('POST')) {
                // We got here from a POST, either from the login, the login block, or some reasonable facsimile thereof.
                if (System::getVar('anonymoussessions', false)) {
                    $this->checkCsrfToken();
                }

                $authenticationInfo = (array)$request->request->get('authentication_info', []);
                $selectedAuthenticationMethod = (array)$request->request->get('authentication_method', []);
                $rememberMe = (bool)$request->request->get('rememberme', false);
                $eventType = $request->request->get('event_type', false);
            } elseif ($request->isMethod('GET')) {
                $reentry = false;
                $reentrantTokenReceived = $request->query->get('reentranttoken', '');

                $sessionVars = $request->getSession()->get('User_login', [], UsersConstant::SESSION_VAR_NAMESPACE);
                $request->getSession()->remove('User_login', UsersConstant::SESSION_VAR_NAMESPACE);

                $reentrantToken = isset($sessionVars['reentranttoken']) ? $sessionVars['reentranttoken'] : false;

                if (!empty($reentrantTokenReceived) && ($reentrantTokenReceived == $reentrantToken)) {
                    // We are coming back (reentering) from someplace else. It is likely that we are coming back from an external
                    // authentication process initiated by an authentication module such as OpenID.
                    $authenticationInfo = isset($sessionVars['authentication_info']) ? $sessionVars['authentication_info'] : [];
                    $selectedAuthenticationMethod = isset($sessionVars['authentication_method']) ? $sessionVars['authentication_method'] : [];
                    $rememberMe         = isset($sessionVars['rememberme']) ? $sessionVars['rememberme'] : false;
                    $returnPage         = isset($sessionVars['returnpage']) ? $sessionVars['returnpage'] : $returnPage;
                    $eventType          = isset($sessionVars['event_type']) ? $sessionVars['event_type'] : false;
                    $user               = isset($sessionVars['user_obj']) ? $sessionVars['user_obj'] : null;

                    $isReentry = true;
                } else {
                    $authenticationInfo = [];
                    $selectedAuthenticationMethod = [];
                    $rememberMe         = false;
                    $eventType          = 'login_screen';
                    $user               = [];

                    $this->getDispatcher()->dispatch('module.users.ui.login.started', new GenericEvent());
                }
            }
        }

        if (!isset($reentrantToken)) {
            $reentrantToken = substr(SecurityUtil::generateCsrfToken(), 0, 10);
        }

        // Any authentication information for use in this pass through login is gathered, so ensure any session variable
        // is cleared, even if we are coming in through a post or a function call that didn't gather info from the session.
        $request->getSession()->remove('User_login', UsersConstant::SESSION_VAR_NAMESPACE);

//        $authenticationMethodList = new AuthenticationMethodListHelper($this);
        $authenticationMethodList = $this->get('zikula_users_module.helper.authentication_method_list_helper');
        $authenticationMethodList->initialize();

        if ($request->isMethod('POST') || $isFunctionCall || $isReentry) {
            // A form submission, or a simulated submission as a function call.
            if (isset($authenticationInfo) && is_array($authenticationInfo) && !empty($authenticationInfo)) {
                if (!isset($selectedAuthenticationMethod) || !is_array($selectedAuthenticationMethod) || empty($selectedAuthenticationMethod)
                        || !isset($selectedAuthenticationMethod['modname']) || empty($selectedAuthenticationMethod['modname'])
                        || !isset($selectedAuthenticationMethod['method']) || empty($selectedAuthenticationMethod['method'])
                        ) {
                    throw new \InvalidArgumentException($this->__('Error! Invalid authentication method information.'));
                }

                if (ModUtil::available($selectedAuthenticationMethod['modname'])
                        && ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'authentication', 'isEnabledForAuthentication', $selectedAuthenticationMethod)
                        ) {
                    // The authentication method is reasonably valid, moving on to validate the user-entered credentials
                    $validateAuthenticationInfoArgs = [
                        'authenticationMethod'  => $selectedAuthenticationMethod,
                        'authenticationInfo'    => $authenticationInfo,
                    ];

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
                        $sessionVars = [
                            'event_type'            => $eventType,
                            'returnpage'            => $returnPage,
                            'authentication_info'   => $authenticationInfo,
                            'authentication_method' => $selectedAuthenticationMethod,
                            'rememberme'            => $rememberMe,
                            'reentranttoken'        => $reentrantToken,
                        ];
                        $request->getSession()->set('User_login', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);

                        // The authentication method selected might be reentrant (it might send the user out to an external web site
                        // for authentication, and then send us back to finish the job). We need to tell the external system to where
                        // we would like to return.
                        $reentrantUrl = $this->get('router')->generate('zikulausersmodule_user_login', ['reentranttoken' => $reentrantToken]);

                        // There may be hook providers that need to be validated, so we cannot yet log in. The hook providers will
                        // need a user object to make sure they know who they're dealing with. Authenticate (so we are sure that
                        // the user is who he says he is) and get a user.
                        //
                        // The chosen authentication method might be reentrant, and this is the point were the user might be directed
                        // outside the Zikula system for external authentication.
                        try {
                            $user = UserUtil::authenticateUserUsing($selectedAuthenticationMethod, $authenticationInfo, $reentrantUrl, true);
                        } catch (AccessDeniedException $e) {
                            $request->getSession()->getFlashBag()->set('error', $e->getMessage());
                        }

                        // Did we get a good user? If so, then we can proceed to hook validation.
                        if (isset($user) && $user && is_array($user) && isset($user['uid']) && is_numeric($user['uid'])) {
                            $validators = new ValidationProviders();
                            if ($eventType) {
                                $event = new GenericEvent($user, [], $validators);
                                $validators  = $this->getDispatcher()->dispatch("module.users.ui.validate_edit.{$eventType}", $event)->getData();

                                $hook = new ValidationHook($validators);
                                $this->dispatchHooks("users.ui_hooks.{$eventType}.validate_edit", $hook);
                                $validators = $hook->getValidators();
                            }

                            if (!$validators->hasErrors()) {
                                // Process the edit hooks BEFORE we log in, so that any changes to the user record are recorded before we re-check
                                // the user's ability to log in. If we don't do this, then user.login.veto might trap and cancel the login attempt again.
                                if ($eventType) {
                                    $event = new GenericEvent($user, []);
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

                                    if (!$request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                        $request->getSession()->getFlashBag()->add('error', $this->__('Your log-in request was not completed.'));
                                    }

                                    $eventArgs = [
                                        'authentication_method' => $selectedAuthenticationMethod,
                                        'authentication_info'   => $authenticationInfo,
                                        'redirecturl'           => '',
                                    ];
                                    $failedEvent = new GenericEvent($user, $eventArgs);
                                    $failedEvent = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $failedEvent);

                                    $redirectUrl = $failedEvent->hasArgument('redirecturl') ? $failedEvent->getArgument('redirecturl') : '';
                                    if (!empty($redirectUrl)) {
                                        return new RedirectResponse($redirectUrl);
                                    }
                                }
                            } else {
                                if (!$request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                    $request->getSession()->getFlashBag()->add('error', $this->__('Your log-in request was not completed.'));
                                }

                                $eventArgs = [
                                    'authentication_method' => $selectedAuthenticationMethod,
                                    'authentication_info'   => $authenticationInfo,
                                    'redirecturl'           => '',
                                ];
                                $failedEvent = new GenericEvent($user, $eventArgs);
                                $failedEvent = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $failedEvent);

                                $redirectUrl = $failedEvent->hasArgument('redirecturl') ? $failedEvent->getArgument('redirecturl') : '';
                                if (!empty($redirectUrl)) {
                                    return new RedirectResponse($redirectUrl);
                                }
                            }
                        } else {
                            // The user with the given credentials does not exist.
                            // Check if we shall redirect to the account registration screen if a user with the given
                            // credentials does not exist..
                            if (ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'Authentication', 'redirectToRegistrationOnLoginError', [
                                'authentication_method' => $selectedAuthenticationMethod,
                                'authentication_info' => $authenticationInfo
                            ])) {
                                // We shall redirect to the account registration screen, but first we need to check
                                // if the user can be created with the given credentials or if another error has
                                // occured.
                                if (ModUtil::apiFunc($selectedAuthenticationMethod['modname'], 'Authentication', 'checkPassword', [
                                    'authentication_method' => $selectedAuthenticationMethod,
                                    'authentication_info' => $authenticationInfo,
                                    'reentrant_url' => $reentrantUrl
                                ])) {
                                    // Redirect to account registration screen. Clear error messages and re-save session
                                    // vars for registration.
                                    $request->getSession()->clearMessages(Zikula_Session::MESSAGE_ERROR);
                                    $request->getSession()->set('User_register', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);

                                    return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registration_register', ['reentranttoken' => $reentrantTokenReceived]));
                                }
                            }

                            if (!$request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                throw new NotFoundHttpException($this->__('There is no user account matching that information, or the password you gave does not match the password on file for that account.'));
                            }

                            $eventArgs = [
                                'authentication_method' => $selectedAuthenticationMethod,
                                'authentication_info'   => $authenticationInfo,
                                'redirecturl'           => '',
                            ];
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
                        $request->getSession()->remove('User_login', UsersConstant::SESSION_VAR_NAMESPACE);
                    } else {
                        if (!$request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                            throw new NotFoundHttpException($this->__('The credentials you entered were not valid. Please reenter the requested information and try again.'));
                        }
                    }
                } else {
                    if ($authenticationMethodList->countEnabledForAuthentication() <= 1) {
                        $request->getSession()->getFlashBag()->add('error', $this->__('The selected log-in method is not currently available. Please contact the site administrator for assistance.'));
                    } else {
                        $request->getSession()->getFlashBag()->add('error', $this->__('The selected log-in method is not currently available. Please choose another or contact the site administrator for assistance.'));
                    }
                }
            } elseif (isset($authenticationInfo) && (!is_array($authenticationInfo))) {
                throw new \InvalidArgumentException($this->__('Error! Invalid authentication information received.'));
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
                $selectedAuthenticationMethod = [
                    'modname'   => $authenticationMethod->modname,
                    'method'    => $authenticationMethod->method,
                ];
            }

            // TODO - The order and availability should be set by configuration
            $authenticationMethodDisplayOrder = [];
            foreach ($authenticationMethodList as $authenticationMethod) {
                if ($authenticationMethod->isEnabledForAuthentication()) {
                    $authenticationMethodDisplayOrder[] = [
                        'modname'   => $authenticationMethod->modname,
                        'method'    => $authenticationMethod->method,
                    ];
                }
            }

            $templateArgs = [
                'returnpage'                            => isset($returnPage) ? $returnPage : '',
                'authentication_info'                   => isset($authenticationInfo) ? $authenticationInfo : [],
                'selected_authentication_method'        => $selectedAuthenticationMethod,
                'authentication_method_display_order'   => $authenticationMethodDisplayOrder,
                'user_obj'                              => isset($user) ? $user : [],
            ];

            return new Response($this->view->assign($templateArgs)
                    ->fetch('User/login.tpl'));
        } else {
            $eventArgs = [
                'authentication_method' => $selectedAuthenticationMethod,
                'redirecturl'           => $returnPage,
            ];

            if (isset($isFirstLogin)) {
                $eventArgs['is_first_login'] = $isFirstLogin;
            }

            $event = new GenericEvent($user, $eventArgs);
            $event = $this->getDispatcher()->dispatch('module.users.ui.login.succeeded', $event);

            $returnPage = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $returnPage;

            if (empty($returnPage)) {
                $returnPage = System::getHomepageUrl();
            }

            return new RedirectResponse(System::normalizeUrl($returnPage));
        }
    }

    /**
     * @Route("/logout", options={"zkNoBundlePrefix"=1})
     *
     * Log a user out.
     *
     * @param Request $request
     *
     * The user is redirected to the entry point of the site, or to a redirect
     * page if specified in the site configuration.
     *
     * Parameters:
     * string  returnpage The URL of the page to return to if the log-out attempt is successful. (This URL must not be urlencoded.)
     *
     * @return Response
     */
    public function logoutAction(Request $request)
    {
        $returnpage = $request->query->get('returnpage', System::getHomepageUrl());

        // start logout event
        $uid = UserUtil::getVar('uid');
        $userObj = UserUtil::getVars($uid);
        $authenticationMethod = SessionUtil::getVar('authentication_method', ['modname' => '', 'method' => ''], UsersConstant::SESSION_VAR_NAMESPACE);
        if (UserUtil::logout()) {
            $event = new GenericEvent($userObj, [
                'authentication_method' => $authenticationMethod,
                'uid'                   => $uid,
            ]);
            $this->getDispatcher()->dispatch('module.users.ui.logout.succeeded', $event);

            return new RedirectResponse(System::normalizeUrl($returnpage));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! You have not been logged out.'));
        }

        return new PlainResponse();
    }

    /**
     * @Route("/verify-registration")
     * @return RedirectResponse
     */
    public function verifyRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationController::verifyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registration_verify', ['uname' => $request->get('uname'), 'verifycode' => $request->get('verifycode')]));
    }

    /**
     * LEGACY user account activation.
     * This is a Core-1.2 era method and is only kept here to properly redirect if it is used.
     * @return RedirectResponse
     */
    public function activationAction(Request $request)
    {
        @trigger_error('This method is removed.', E_USER_DEPRECATED);
        $request->getSession()->getFlashBag()->add('error', $this->__('Warning! This method is no longer functional. You must re-register.'));

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registration_selectregistrationmethod'));
    }

    /**
     * Log into a site that is currently "off" (normal logins are not allowed).
     * @Method("POST")
     *
     * Allows the administrator to access the site during maintenance.
     *
     * @param Request $request
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
    public function siteOffLoginAction(Request $request)
    {
        // do not process if the site is enabled
        if (!System::getVar('siteoff', false)) {
            return new RedirectResponse(System::normalizeUrl(System::getHomepageUrl()));
        }

        $user = $request->request->get('user', null);
        $pass = $request->request->get('pass', null);
        $rememberme = $request->request->get('rememberme', false);

        $redirectUrl = System::getHomepageUrl();

        $authenticationInfo = [
            'login_id'  => $user,
            'pass'      => $pass
        ];
        $authenticationMethod = [
            'modname'   => $this->name,
            'method'    => 'uname',
        ];

        if (UserUtil::loginUsing($authenticationMethod, $authenticationInfo, $rememberme)) {
            $user = UserUtil::getVars(UserUtil::getVar('uid'));
            if (!SecurityUtil::checkPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN)) {
                UserUtil::logout();

                $eventArgs = [
                    'authentication_method' => $authenticationMethod,
                    'redirecturl'           => '',
                ];
                $event = new GenericEvent($user, $eventArgs);
                $event = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $event);
                $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;
            } else {
                $eventArgs = [
                    'authentication_method' => $authenticationMethod,
                    'redirecturl'           => $redirectUrl,
                ];
                $event = new GenericEvent($user, $eventArgs);
                $event = $this->getDispatcher()->dispatch('module.users.ui.login.succeeded', $event);
                $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $redirectUrl;
            }
        } else {
            $eventArgs = [
                'authentication_method' => $authenticationMethod,
                'authentication_info'   => $authenticationInfo,
                'redirecturl'           => '',
            ];
            $event = new GenericEvent(null, $eventArgs);
            $event = $this->getDispatcher()->dispatch('module.users.ui.login.failed', $event);
            $redirectUrl = $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : '';
        }

        return new RedirectResponse(System::normalizeUrl($redirectUrl));
    }

    /**
     * @Route("/password")
     * @return RedirectResponse
     */
    public function changePasswordAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::changePasswordAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_changepassword'));
    }

    /**
     * @Route("/email")
     * @return RedirectResponse
     */
    public function changeEmailAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_changeemail'));
    }

    /**
     * @Route("/email/confirm/{confirmcode}")
     * @return RedirectResponse
     */
    public function confirmChEmailAction(Request $request, $confirmcode = null)
    {
        @trigger_error('This method is deprecated. Please use AccountController::confirmChangeEmailAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_confirmchangedemail', ['code' => $confirmcode]));
    }

    /**
     * @Route("/lang")
     * @return RedirectResponse
     */
    public function changeLangAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::changeLanguageAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_changelanguage'));
    }

    /**
     * Display the login screen
     * @param array $args parameters for this function
     * @see \Zikula\UsersModule\Controller\UserController::login
     * @return RedirectResponse
     * @deprecated since 1.4.0 use loginAction instead
     */
    public function loginScreenAction($args)
    {
        @trigger_error('This method is deprecated.', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_login'), 301);
    }
}
