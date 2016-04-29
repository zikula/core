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
use Zikula\Core\LinkContainer\LinkContainerInterface;
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
use LogUtil;
use ThemeUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;
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
     *
     * Render and display the user's account panel. If he is not logged in, then redirect to the login screen.
     *
     * @return RedirectResponse symfony response object
     *
     * @throws AccessDeniedException if the current user does not have adequate permissions to perform this function.
     */
    public function mainAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/useraccount", options={"zkNoBundlePrefix"=1})
     *
     * Render and display the user's account panel. If he is not logged in, then redirect to the login screen.
     *
     * @param Request $request
     *
     * @return RedirectResponse symfony response object
     *
     * @throws AccessDeniedException if the current user does not have adequate permissions to perform this function.
     */
    public function indexAction(Request $request)
    {
        // Security check
        if (!UserUtil::isLoggedIn()) {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_login', array('returnpage' => urlencode($this->get('router')->generate('zikulausersmodule_user_index'))), RouterInterface::ABSOLUTE_URL));
        }

        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // get the menu links for Core-2.0 modules
        $accountLinks = $this->get('zikula.link_container_collector')->getAllLinksByType(LinkContainerInterface::TYPE_ACCOUNT);
        // create legacy array @todo refactor template to remove need for this
        $legacyAccountLinksFromNew = [];
        foreach ($accountLinks as $moduleName => $links) {
            foreach ($links as $link) {
                $legacyAccountLinksFromNew[] = [
                    'module' => $moduleName,
                    'url' => $link['url'],
                    'title' => !empty($link['text']) ? $link['text'] : '',
                    'icon' => 'admin.png'
                ];
            }
        }

        // The API function is called for old-style modules
        $legacyAccountLinks = ModUtil::apiFunc($this->name, 'user', 'accountLinks');
        if (false === $legacyAccountLinks) {
            $legacyAccountLinks = [];
        }

        // add the arrays together
        $accountLinks = $legacyAccountLinksFromNew + $legacyAccountLinks;

        if ($accountLinks == false) {
            $request->getSession()->getFlashBag()->add('warning', $this->__('Error! No account links available.'));
        }

        return new Response($this->view->assign('accountLinks', $accountLinks)
            ->fetch('User/main.tpl'));
    }

    /**
     * @Route("/view")
     *
     * Display the base user form (login/lostpassword/register options).
     *
     * If the user is logged in, then he is redirected to the home page.
     *
     * @return Response|RedirectResponse symfony response object
     */
    public function viewAction()
    {
        // If user has logged in, redirect to homepage
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse(System::normalizeUrl(System::getHomepageUrl()));
        }

        return new Response($this->view->assign($this->getVars())
            ->fetch('User/view.tpl'));
    }

    /**
     * @Route("/register", options={"zkNoBundlePrefix"=1})
     *
     * BC Method to forward to new Controller
     */
    public function registerAction(Request $request)
    {
        $subRequest = $this->getContainer()
            ->get('request_stack')
            ->getCurrentRequest()
            ->duplicate($request->query->all(), null, ['_controller' => 'ZikulaUsersModule:Registration:selectRegistrationMethod']);

        return $this->getContainer()->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @Route("/lost-account-details")
     *
     * Display the lost user name / password choices.
     *
     * @return Response|RedirectResponse symfony response object
     */
    public function lostPwdUnameAction()
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        return new Response($this->view->fetch('User/lostpwduname.tpl'));
    }

    /**
     * @Route("/lost-username")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
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
     * @return Response|RedirectResponse symfony response object
     */
    public function lostUnameAction(Request $request)
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        $proceedToForm = true;
        $email = '';

        if ($request->isMethod('POST')) {
            $emailMessageSent = false;

            $this->checkCsrfToken();

            $email = $request->request->get('email', null);

            if (empty($email)) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! E-mail address field is empty.'));
            } else {
                // save username and password for redisplay
                $emailMessageSent = ModUtil::apiFunc($this->name, 'user', 'mailUname', array(
                    'idfield'   => 'email',
                    'id'        => $email
                ));

                if ($emailMessageSent) {
                    $request->getSession()->getFlashBag()->add('status', $this->__f('Done! The account information for %s has been sent via e-mail.', $email));
                    $proceedToForm = false;
                } else {
                    $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! We are unable to send the account information for that e-mail address. Please reenter your information, or contact an administrator.'));
                }
            }
        } elseif ($request->isMethod('GET')) {
            $email = '';
        }

        if ($proceedToForm) {
            return new Response($this->view->assign('email', $email)
                    ->fetch('User/lostuname.tpl'));
        } else {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_login', array(), RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/lost-password")
     * @Method({"GET", "POST"})
     *
     * Display the lost password form.
     *
     * @param Request $request
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
     * @return Response|RedirectResponse symfony response object
     *
     * @throws NotFoundHttpException Thrown if the account couldn't be found
     * @throws AccessDeniedException Thrown if the parameters cannot be found in either GET or POST
     */
    public function lostPasswordAction(Request $request)
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        $formStage = 'request';

        if ($request->isMethod('POST')) {
            $emailMessageSent = false;

            $this->checkCsrfToken();

            $uname = $request->request->get('uname', '');
            $email = $request->request->get('email', '');

            if (empty($uname) && empty($email)) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! User name and e-mail address fields are empty.'));
            } elseif (!empty($email) && !empty($uname)) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Please enter either a user name OR an e-mail address, but not both of them.'));
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
                                $request->getSession()->getFlashBag()->add('status', $this->__f('Done! The confirmation code for %s has been sent via e-mail.', $idvalue));
                                $formStage = 'code';
                            } elseif ($idfield == 'email') {
                                $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! We are unable to send a password recovery code for that e-mail address. Please try your user name, or contact an administrator.'));
                            } else {
                                $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! We are unable to send a password recovery code for that user name. Please try your e-mail address, contact an administrator.'));
                            }
                        } else {
                            $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! Your account is not set up to use a password to log into this site. Please recover your account information to determine your available log-in options.'));
                            $formStage = 'lostPwdUname';
                        }
                    } elseif (($userObj['activated'] == UsersConstant::ACTIVATED_INACTIVE) && ($this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS))) {
                        $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! Your account is marked as inactive. Please contact a site administrator for more information.'));
                        $formStage = 'lostPwdUname';
                    } elseif (($userObj['activated'] == UsersConstant::ACTIVATED_PENDING_DELETE) && ($this->getVar(UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS))) {
                        $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! Your account is marked for removal. Please contact a site administrator for more information.'));
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

                    $request->getSession()->getFlashBag()->add('error', $message);
                }
            }
        } elseif ($request->isMethod('GET')) {
            $uname = '';
            $email = '';
        }

        if ($formStage == 'request') {
            $templateVariables = array(
                'uname' => $uname,
                'email' => $email,
            );

            return new Response($this->view->assign($templateVariables)
                    ->fetch('User/lostpassword.tpl'));
        } elseif ($formStage == 'code') {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_lostpasswordcode', array(), RouterInterface::ABSOLUTE_URL));
        } elseif ($formStage == 'lostPwdUname') {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_lostpwduname', array(), RouterInterface::ABSOLUTE_URL));
        } else {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/lost-password/code")
     * @Method({"GET", "POST"})
     *
     * Display the lost password confirmation code entry form.
     *
     * @param Request $request
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
     * @return Response|RedirectResponse symfony response object
     *
     * @throws AccessDeniedException Thrown if the parameters cannot be found in either GET or POST
     */
    public function lostPasswordCodeAction(Request $request)
    {
        // we shouldn't get here if logged in already....
        if (UserUtil::isLoggedIn()) {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        $formStage = 'code';
        $errorInfo = array();

        if ($request->isMethod('POST')) {
            $this->checkCsrfToken();

            $setPass = $request->request->get('setpass', false);

            if (!$setPass) {
                // lostpasswordcode form
                $uname = $request->request->get('uname', '');
                $email = $request->request->get('email', '');
                $code  = $request->request->get('code', '');

                $newpass = '';
                $newpassagain = '';
                $newpassreminder = '';
                $passreminder = '';
            } else {
                // Reset password (passwordreminder) form
                $uname          = $request->request->get('uname', '');
                $newpass        = $request->request->get('newpass', '');
                $newpassagain   = $request->request->get('newpassagain', '');
                $newpassreminder = $request->request->get('newpassreminder', '');

                $formStage = 'setpass';
            }
        } elseif ($request->isMethod('GET')) {
            $setpass = false;
            $uname = $request->query->get('uname', '');
            $email = $request->query->get('email', '');
            $code = $request->query->get('code', '');

            $newpass = '';
            $newpassagain = '';
            $newpassreminder = '';
            $passreminder = '';
        }

        if (($formStage == 'code') && ($request->isMethod('POST') || !empty($uname) || !empty($email) || !empty($code))) {
            // Got something to process from either GET or POST
            if (empty($uname) && empty($email)) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! User name and e-mail address fields are empty.'));
                $formStage = 'code';
            } elseif (!empty($email) && !empty($uname)) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Please enter either a user name OR an e-mail address, but not both of them.'));
                $formStage = 'code';
            } elseif (empty($code)) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Please enter the confirmation code you received in the e-mail message.'));
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
                        $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! Could not load that user account.'));
                        $formStage = 'error';
                    }
                } else {
                    $request->getSession()->getFlashBag()->add('error', $this->__("Error! The code that you have entered is invalid."));
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
                            $request->getSession()->getFlashBag()->add('warning', $this->__('Warning! Your new password has been saved, but there was an error while trying to save your new password reminder.'));
                        } else {
                            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Your password has been reset, and you may now log in. Please keep your password in a safe place!'));
                        }
                        $formStage = 'login';
                    } else {
                        $request->getSession()->getFlashBag()->add('error', $this->__('Error! Your new password could not be saved.'));
                        $formStage = 'error';
                    }
                } else {
                    $errorInfo = ModUtil::apiFunc($this->name, 'user', 'processRegistrationErrorsForDisplay', array('registrationErrors' => $passwordErrors));
                }
            } else {
                $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! Could not load that user account.'));
                $formStage = 'error';
            }
        }

        if ($formStage == 'code') {
            $templateVariables = array(
                'uname' => $uname,
                'email' => $email,
                'code'  => $code,
            );

            return new Response($this->view->assign($templateVariables)
                    ->fetch('User/lostpasswordcode.tpl'));
        } elseif ($formStage == 'setpass') {
            $templateVariables = array(
                'uname'             => $userObj['uname'],
                'passreminder'      => $passreminder,
                'newpassreminder'   => $newpassreminder,
                'errormessages'     => (isset($errorInfo['errorMessages']) && !empty($errorInfo['errorMessages'])) ? $errorInfo['errorMessages'] : array(),
            );

            return new Response($this->view->assign($templateVariables)
                    ->fetch('User/passwordreminder.tpl'));
        } elseif ($formStage == 'login') {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_login', array(), RouterInterface::ABSOLUTE_URL));
        } else {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_lostpwduname', array(), RouterInterface::ABSOLUTE_URL));
        }
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
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        // set default value of variables
        $loggedIn = false;
        $isFunctionCall = false;
        $isReentry = false;
        $firstmethodisdefault = true;
        $fromPasswordChange = $request->get('from_password_change', false);
        $authenticationInfo = array();
        $selectedAuthenticationMethod = array();
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

                $authenticationInfo = (array)$request->request->get('authentication_info', array());
                $selectedAuthenticationMethod = (array)$request->request->get('authentication_method', array());
                $rememberMe = (bool)$request->request->get('rememberme', false);
                $eventType = $request->request->get('event_type', false);
            } elseif ($request->isMethod('GET')) {
                $reentry = false;
                $reentrantTokenReceived = $request->query->get('reentranttoken', '');

                $sessionVars = $request->getSession()->get('User_login', array(), UsersConstant::SESSION_VAR_NAMESPACE);
                $request->getSession()->remove('User_login', UsersConstant::SESSION_VAR_NAMESPACE);

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
            }
        }

        if (!isset($reentrantToken)) {
            $reentrantToken = substr(SecurityUtil::generateCsrfToken(), 0, 10);
        }

        // Any authentication information for use in this pass through login is gathered, so ensure any session variable
        // is cleared, even if we are coming in through a post or a function call that didn't gather info from the session.
        $request->getSession()->remove('User_login', UsersConstant::SESSION_VAR_NAMESPACE);

//        $authenticationMethodList = new AuthenticationMethodListHelper($this);
        $authenticationMethodList = $this->get('zikulausersmodule.helper.authentication_method_list_helper');
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
                        $request->getSession()->set('User_login', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);

                        // The authentication method selected might be reentrant (it might send the user out to an external web site
                        // for authentication, and then send us back to finish the job). We need to tell the external system to where
                        // we would like to return.
                        $reentrantUrl = $this->get('router')->generate('zikulausersmodule_user_login', array('reentranttoken' => $reentrantToken), RouterInterface::ABSOLUTE_URL);

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

                                    if (!$request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                        $request->getSession()->getFlashBag()->add('error', $this->__('Your log-in request was not completed.'));
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
                                        return new RedirectResponse($redirectUrl);
                                    }
                                }
                            } else {
                                if (!$request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                    $request->getSession()->getFlashBag()->add('error', $this->__('Your log-in request was not completed.'));
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
                                    return new RedirectResponse($redirectUrl);
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
                                    $request->getSession()->clearMessages(Zikula_Session::MESSAGE_ERROR);
                                    $request->getSession()->set('User_register', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);

                                    return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registration_register', array('reentranttoken' => $reentrantTokenReceived), RouterInterface::ABSOLUTE_URL));
                                }
                            }

                            if (!$request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
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

            return new Response($this->view->assign($templateArgs)
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
        $login_redirect = $this->getVar('login_redirect');
        $returnpage     = $request->query->get('returnpage', System::getHomepageUrl());

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
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! You have not been logged out.'));
        }

        return new PlainResponse();
    }

    /**
     * @Route("/verify-registration")
     *
     * Render and process a registration e-mail verification code.
     *
     * @param Request $request
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
     * @return Response|RedirectResponse Symfony response object
     *
     * @throws AccessDeniedException Thrown if there are no arguments in either GET or POST
     */
    public function verifyRegistrationAction(Request $request)
    {
        if (UserUtil::isLoggedIn()) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! An account cannot be verified while you are logged in.'));
        }

        if ($request->isMethod('GET')) {
            $uname      = $request->query->get('uname', '');
            $verifycode = $request->query->get('verifycode', '');
        } elseif ($request->isMethod('POST')) {
            $this->checkCsrfToken();
            $uname          = $request->request->get('uname', '');
            $verifycode     = $request->request->get('verifycode', '');
            $newpass        = $request->request->get('newpass', '');
            $newpassagain   = $request->request->get('newpassagain', '');
            $newpassreminder = $request->request->get('newpassreminder', '');
        } else {
            throw new AccessDeniedException();
        }

        if ($uname) {
            $uname = mb_strtolower($uname);
        }
        $setPass = false;

        if ($uname && $verifycode) {
            // Both a user name and verification code were submitted

//            $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uname' => $uname));
            $reginfo = $this->get('zikulausersmodule.helper.registration_helper')->get(null, $uname);

            if ($reginfo) {
                if (!isset($reginfo['pass']) || empty($reginfo['pass'])) {
                    $setPass = true;

                    if ($request->isMethod('POST')) {
                        $passwordErrors = ModUtil::apiFunc($this->name, 'registration', 'getPasswordErrors', array(
                            'uname'         => $uname,
                            'pass'          => $newpass,
                            'passagain'     => $newpassagain,
                            'passreminder'  => $newpassreminder,
                        ));

                        if (empty($passwordErrors)) {
                            $newpassHash = UserUtil::getHashedPassword($newpass);
                            $passSaved = UserUtil::setVar('pass', $newpassHash, $reginfo['uid']);
                            if (!$passSaved) {
                                $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! There was an error while trying to save your new password and reminder.'));
                            } else {
                                $reginfo['pass'] = $newpassHash;
                            }

                            $passReminderSaved = UserUtil::setVar('passreminder', $newpassreminder, $reginfo['uid']);
                            if (!$passReminderSaved) {
                                $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! There was an error while trying to save your new password and reminder.'));
                            } else {
                                $reginfo['passreminder'] = $newpassreminder;
                            }
                        }
                    }
                }

                if ($verifycode && $reginfo && isset($reginfo['pass']) && !empty($reginfo['pass'])) {
//                    $verifyChg = ModUtil::apiFunc($this->name, 'registration', 'getVerificationCode', array(
//                        'uid'   => $reginfo['uid'],
//                    ));
                    $verifyChg = $this->get('zikulausersmodule.helper.registration_verification_helper')->getVerificationCode($reginfo['uid']);

                    if ($verifyChg) {
                        $codesMatch = UserUtil::passwordsMatch($verifycode, $verifyChg['verifycode']);

                        if ($codesMatch) {
//                            $verified = ModUtil::apiFunc($this->name, 'registration', 'verify', array('reginfo' => $reginfo));
                            $verified = $this->get('zikulausersmodule.helper.registration_verification_helper')->verify($reginfo);

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
                                        $request->getSession()->getFlashBag()->add('status', $message);
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $request->getSession()->getFlashBag()->add('status', $regErrorsMessage);
                                        }

                                        return new Response($this->view->fetch('User/displaystatusmsg.tpl'));
                                        break;
                                    case UsersConstant::ACTIVATED_ACTIVE:
                                        if (!$extAuthModuleUsed) {
                                            // The users module was used to register that account.
                                            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Your account has been verified. You may now log in with your user name and password.'));
                                        } else {
                                            // A third party module was used to register that account.
                                            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Your account has been verified. You may now log in.'));
                                        }
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $request->getSession()->getFlashBag()->add('status', $regErrorsMessage);

                                            return new Response($this->view->fetch('User/displaystatusmsg.tpl'));
                                        } else {
                                            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_login', array(), RouterInterface::ABSOLUTE_URL));
                                        }
                                        break;
                                    default:
                                        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Your account has been verified.'));
                                        $request->getSession()->getFlashBag()->add('status', $this->__('Your new account is not active yet. Please contact the site administrator for more information.'));
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $request->getSession()->getFlashBag()->add('status', $regErrorsMessage);
                                        }

                                        return new Response($this->view->fetch('User/displaystatusmsg.tpl'));
                                        break;
                                }
                            } else {
                                if (!$request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                    $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! There was an error while marking your registration as verifed. Please contact an administrator.'));
                                } else {
                                    return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
                                }
                            }
                        } else {
                            $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! The verification code you provided does not match our records. Please check the code, and also check your e-mail for a newer verification code that might have been sent.'));
                        }
                    } elseif ($verifyChg === false) {
                        $request->getSession()->getFlashBag()->add('error', $this->__('Error! There was a problem retrieving the verification code for comparison.'));

                        return new Response();
                    } else {
                        $request->getSession()->getFlashBag()->add('error', $this->__f('Error! There is no pending verification code for \'%1$s\'. Please contact the site administrator.', array($reginfo['uname'])));

                        return new Response();
                    }
                }
                // No code, or no password. Pass down through to the template rendering.
            } else {
                $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! A registration does not exist for the user name you provided. Maybe your request has expired? Please check the user name, or contact an administrator.'));
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

        return new Response($this->view->assign($rendererArgs)
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
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Could not activate your account. Please contact the site administrator.'));
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
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Could not activate your account. Please contact the site administrator.'));
            }
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Account activated.'));

            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_login', array(), RouterInterface::ABSOLUTE_URL));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Sorry! You entered an invalid confirmation code. Please correct your entry and try again.'));

            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_activation', array(), RouterInterface::ABSOLUTE_URL));
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
            $url = 'http'.(System::serverGetVar('HTTPS') == 'on' ? 's' : '').'://'.System::serverGetVar('HTTP_HOST').$url;
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
     * @Route("/usersblock")
     *
     * Display the configuration options for the users block.
     *
     * @return Response symfony response object
     *
     * @throws NotFoundHttpException Thrown if the users block isn't found
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
            throw new NotFoundHttpException();
        }

        return new Response($this->view->assign(UserUtil::getVars(UserUtil::getVar('uid')))
                ->fetch('User/usersblock.tpl'));
    }

    /**
     * @Route("/updateusersblock")
     * @Method("POST")
     *
     * Update the custom users block.
     *
     * @param Request $request
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
     * @return AccessDeniedException Thrown if the user isn't logged in or
     *                                          if there are no post parameters
     * @throws NotFoundHttpException Thrown if the users block isn't found
     */
    public function updateUsersBlockAction(Request $request)
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
            throw new NotFoundHttpException();
        }

        $ublockon = (bool)$request->request->get('ublockon', false);
        $ublock = (string)$request->request->get('ublock', '');

        $uid = UserUtil::getVar('uid');

        UserUtil::setVar('ublockon', $ublockon);
        UserUtil::setVar('ublock', $ublock);

        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved custom block.'));

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/password")
     *
     * Display the change password form.
     *
     * @param Request $request
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
     * @throws FatalErrorException|\InvalidArgumentException Thrown if there are no arguments provided or
     *                                    if the user is logged in but the user is coming from the login process or
     *                                    if the authentication information is invalid
     * @throws AccessDeniedException Thrown if the user isn't logged in and isn't coming from the login process
     */
    public function changePasswordAction(Request $request)
    {
        // Retrieve and delete any session variables being sent in before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $sessionVars = $request->getSession()->get('User_changePassword', null, UsersConstant::SESSION_VAR_NAMESPACE);
        $request->getSession()->remove('User_changePassword', UsersConstant::SESSION_VAR_NAMESPACE);

        $loginAfterChange = $request->get('login', false);

        // In order to change one's password, the user either must be logged in already, or specifically
        // must be coming from the login process. This is an exclusive-or. It is an error if neither is set,
        // and likewise if both are set. One or the other, please!
        if (!$loginAfterChange && !UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        } elseif ($loginAfterChange && UserUtil::isLoggedIn()) {
            throw new FatalErrorException();
        }

        // If we are coming here from the login process, then there are certain things that must have been
        // send along in the session variable. If not, then error.
        if ($loginAfterChange && (!isset($sessionVars['user_obj']) || !is_array($sessionVars['user_obj'])
                || !isset($sessionVars['authentication_info']) || !is_array($sessionVars['authentication_info'])
                || !isset($sessionVars['authentication_method']) || !is_array($sessionVars['authentication_method']))
                ) {
            throw new \InvalidArgumentException();
        }

        if ($this->getVar('changepassword', 1) != 1) {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        $passwordErrors = array();
        if (isset($sessionVars['password_errors'])) {
            if (!empty($sessionVars['password_errors'])) {
                $passwordErrors = $sessionVars['password_errors'];
            }
            unset($sessionVars['password_errors']);
        }

        if ($loginAfterChange) {
            // Pass along the session vars to updatePassword. We didn't want to just keep them in the session variable
            // /Users_Controller_User_changePassword because if we hit an exception or got redirected, then the data
            // would have been orphaned, and it contains some sensitive information.
            SessionUtil::requireSession();
            $request->getSession()->set('User_updatePassword', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);
        }

        // Return the output that has been generated by this function
        return new Response($this->view->assign('password_errors', $passwordErrors)
                          ->assign('login', (bool)$loginAfterChange)
                          ->assign('user_obj', ($loginAfterChange ? $sessionVars['user_obj'] : null))
                          ->assign('authentication_method', ($loginAfterChange ? $sessionVars['authentication_method'] : null))
                          ->fetch('User/changepassword.tpl'));
    }

    /**
     * @Route("/password/update")
     * @Method("POST")
     *
     * @param Request $request
     *
     * Update the user's password.
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
     * @throws \RuntimeException if there's a problem saving the new password
     */
    public function updatePasswordAction(Request $request)
    {
        $sessionVars = $request->getSession()->get('User_updatePassword', null, UsersConstant::SESSION_VAR_NAMESPACE);
        $request->getSession()->remove('User_updatePassword', UsersConstant::SESSION_VAR_NAMESPACE);

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
        $currentPassword    = $request->request->get('oldpassword', '');
        $newPassword        = $request->request->get('newpassword', '');
        $newPasswordAgain   = $request->request->get('newpasswordconfirm', '');
        $newPasswordReminder = $request->request->get('passreminder', '');
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
                    $request->getSession()->getFlashBag()->add('error', $this->__('Warning! Your new password was saved, however there was a problem saving your new password reminder.'));
                } else {
                    $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved your new password.'));
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
                throw new \RuntimeException($this->__('Sorry! There was a problem saving your new password.'));
            }
        }

        if ($passwordChanged) {
            if ($login) {
                $sessionVars = $request->getSession()->get('User_login', array(), UsersConstant::SESSION_VAR_NAMESPACE);
                $post['authentication_method'] = $sessionVars['authentication_method'];
                $post['authentication_info'] = $sessionVars['authentication_info'];
                $post['rememberme'] = $sessionVars['rememberme'];
                $post['from_password_change'] = true;

                $subRequest = $request->duplicate(array(), $post, ['_controller' => 'ZikulaUsersModule:User:login']);
                $httpKernel = $this->get('http_kernel');
                $response = $httpKernel->handle(
                    $subRequest,
                    HttpKernelInterface::SUB_REQUEST
                );

                return $response;
            } else {
                return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
            }
        } else {
            $sessionVars['password_errors'] = $passwordErrors;
            SessionUtil::requireSession();
            $request->getSession()->set('User_changePassword', $sessionVars, UsersConstant::SESSION_VAR_NAMESPACE);

            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_changepassword', array('login' => $login), RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/email")
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
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        return new Response($this->view->fetch('User/changeemail.tpl'));
    }

    /**
     * @Route("/email/update")
     *
     * Update the email address.
     *
     * @param Request $request
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
    public function updateEmailAction(Request $request)
    {
        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $this->checkCsrfToken();

        $uservars = $this->getVars();
        if ($uservars['changeemail'] != 1) {
            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        $newemail = $request->request->get('newemail', '');
        $newemailagain = $request->request->get('newemailagain', '');

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
                        $request->getSession()->getFlashBag()->add('error', $errorMessage);
                    }
                } else {
                    // Only one error.
                    $request->getSession()->getFlashBag()->add('error', $errorList);
                }
            }

            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_changeemail', array(), RouterInterface::ABSOLUTE_URL));
        }

        // save the provisional email until confimation
        $verificationSent = ModUtil::apiFunc($this->name, 'user', 'savePreEmail', array('newemail' => $newemail));

        if (!$verificationSent) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! There was a problem saving your new e-mail address or sending you a verification message.'));
        }

        $request->getSession()->getFlashBag()->add('status', $this->__('Done! You will receive an e-mail to your new e-mail address to confirm the change. You must follow the instructions in that message in order to verify your new address.'));

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/lang")
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
        return new Response($this->view->assign('languages', \ZLanguage::getInstalledLanguageNames())
                ->assign('usrlang', \ZLanguage::getLanguageCode())
                ->fetch('User/changelang.tpl'));
    }

    /**
     * @Route("/email/confirm/{confirmcode}")
     * @Method("GET")
     *
     * Confirm the update of the email address.
     *
     * @param Request $request
     * @param $confirmcode
     *
     * @return RedirectResponse
     *
     * @throws \RuntimeException Thrown if the user isn't logged in or
     *                                  if the e-mail address hasn't be found
     */
    public function confirmChEmailAction(Request $request, $confirmcode = null)
    {
        if (!UserUtil::isLoggedIn()) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Please log into your account in order to confirm your change of e-mail address.'));
        }

        // get user new email that is waiting for confirmation
        $preemail = ModUtil::apiFunc($this->name, 'user', 'getUserPreEmail');

        $validCode = UserUtil::passwordsMatch($confirmcode, $preemail['verifycode']);

        if (!$preemail || !$validCode) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! Your e-mail has not been found. After your request you have five days to confirm the new e-mail address.'));
        }

        // user and confirmation code are correct. set the new email
        UserUtil::setVar('email', $preemail['newemail']);

        // the preemail record is deleted
        ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array(
            'uid'       => $preemail['uid'],
            'changetype' => UsersConstant::VERIFYCHGTYPE_EMAIL,
        ));

        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Changed your e-mail address.'));

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * Display the login screen
     *
     * @param array $args parameters for this function
     *
     * @see \Zikula\UsersModule\Controller\UserController::login
     *
     * @return RedirectResponse
     *
     * @deprecated since 1.4.0 use loginAction instead
     */
    public function loginScreenAction($args)
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_login', array(), RouterInterface::ABSOLUTE_URL), 301);
    }
}
