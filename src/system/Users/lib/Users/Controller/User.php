<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Access to (non-administrative) user-initiated actions for the Users module.
 *
 * Note: $this->throw...() functions are not used because they hide where the
 * exception actually happened. (The exception thrown in the superclass is recorded
 * as the file and line were the exception occurred.
 */
class Users_Controller_User extends Zikula_AbstractController
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
        // Set caching to false by default.
        $this->view->setCaching(false);
    }

    /**
     * Render and display the user's account panel. If he is not logged in, then redirect to the login screen.
     *
     * @return string The rendered template.
     *
     * @throws Zikula_Exception_Forbidden if the current user does not have adequate permissions to perform
     *          this function.
     */
    public function main()
    {
        // Security check
        $this->redirectUnless(UserUtil::isLoggedIn(), ModUtil::url($this->name, 'user', 'login'));

        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        // The API function is called.
        $accountLinks = ModUtil::apiFunc($this->name, 'user', 'accountLinks');

        if ($accountLinks == false) {
            throw new Zikula_Exception_NotFound($this->__('Error! No account links available.'));
        }

        $this->view->add_core_data();

        return $this->view->assign('accountLinks', $accountLinks)
                ->fetch('users_user_main.tpl');
    }

    /**
     * Display the base user form (login/lostpassword/register options).
     *
     * If the user is logged in, then he is redirected to the home page.
     *
     * @return string The rendered template.
     */
    public function view()
    {
        // If has logged in, head to index.php
        $this->redirectIf(UserUtil::isLoggedIn(), System::getHomepageUrl());

        $this->view->assign($this->getVars())
                ->fetch('users_user_view.tpl');
    }

    /**
     * Set an underage error message and route the user back to the first user page.
     *
     * @return bool True, and the user is redirected to the view function.
     */
    public function underAge()
    {
        $this->registerError($this->__f('Sorry! You must be %s or over to register for a user account here.', $this->getVar('minage')))
            ->redirect(ModUtil::url($this->name, 'user', 'view'));
    }

    /**
     * Display the registration form.
     *
     * @return string The rendered template.
     */
    public function register()
    {
        // If has logged in, header to index.php
        $this->redirectIf(UserUtil::isLoggedIn(), System::getHomepageUrl());

        // check permisisons
        if (!SecurityUtil::checkPermission($this->name .'::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }
        
        if (!$this->getVar(Users_Constant::MODVAR_REGISTRATION_ENABLED, Users_Constant::DEFAULT_REGISTRATION_ENABLED)) {
            return $this->view->fetch('users_user_registration_disabled.tpl');
        }
        
        // Check for illegal user agents trying to register.
        $userAgent = $this->request->getServer()->get('HTTP_USER_AGENT', '');
        $illegalUserAgents = $this->getVar(Users_Constant::MODVAR_REGISTRATION_ILLEGAL_AGENTS, '');
        // Convert the comma-separated list into a regexp pattern.
        $pattern = array('/^(\s*,\s*)+/D', '/\b(\s*,\s*)+\b/D', '/(\s*,\s*)+$/D');
        $replace = array('', '|', '');
        $illegalUserAgents = preg_replace($pattern, $replace, preg_quote($illegalUserAgents, '/'));
        // Check for emptiness here, in case there were just spaces and commas in the original string.
        if (!empty($illegalUserAgents) && preg_match("/^({$illegalUserAgents})/iD", $userAgent)) {
            throw new Zikula_Exception_Forbidden($this->__('Sorry! The user agent you are using (the browser or other software you are using to access this site) is banned from the registration process.'));
        }
        
        $proceedToForm = true;
        $canLogIn = false;
        $formData = new Users_Controller_FormData_RegistrationForm('users_register', $this->serviceManager);
        $errorFields = array();
        $errorMessages = array();

        if ($this->request->isPost()) {
            $this->checkCsrfToken();
            
            $formData->setFromRequestCollection($this->request->getPost());
            $formData->getField('uname')->setData(mb_strtolower($formData->getField('uname')->getData()));
            $formData->getField('email')->setData(mb_strtolower($formData->getField('email')->getData()));
            
            $antispamAnswer = $formData->getFieldData('antispamanswer');
            $reginfo = $formData->toUserArray();
            $registrationArgs = array(
                'checkmode'         => 'new',
                'reginfo'           => $reginfo,
                'passagain'         => $formData->getFieldData('passagain'),
                'emailagain'        => $formData->getFieldData('emailagain'),
                'antispamanswer'    => isset($antispamAnswer) ? $antispamAnswer : '',
            );

            if ($formData->isValid()) {
                $errorFields = ModUtil::apiFunc($this->name, 'registration', 'getRegistrationErrors', $registrationArgs);
            } else {
                $errorFields = $formData->getErrorMessages();
            }
            
            $validators = $this->notifyHooks('users.hook.user.validate.edit', $reginfo, null, array(), new Zikula_Hook_ValidationProviders())->getData();

            if (empty($errorFields) && !$validators->hasErrors()) {
                $currentUserEmail = UserUtil::getVar('email');
                $adminNotifyEmail = $this->getVar('reg_notifyemail', '');
                $adminNotification = (strtolower($currentUserEmail) != strtolower($adminNotifyEmail));

                $registeredObj = ModUtil::apiFunc($this->name, 'registration', 'registerNewUser', array(
                    'reginfo'           => $reginfo,
                    'usernotification'  => true,
                    'adminnotification' => true
                ));

                if (isset($registeredObj) && $registeredObj) {
                    $this->notifyHooks('users.hook.user.process.edit', $registeredObj, $registeredObj['uid']);

                    if (!empty($registeredObj['regErrors'])) {
                        $this->view->assign('regErrors', $registeredObj['regErrors']);
                    }

                    if ($registeredObj['activated'] == Users_Constant::ACTIVATED_PENDING_REG) {
                        $moderation = $this->getVar(Users_Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, Users_Constant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
                        $moderationOrder = $this->getVar(Users_Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, Users_Constant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
                        $verifyEmail = $this->getVar(Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE, Users_Constant::DEFAULT_REGISTRATION_VERIFICATION_MODE);

                        if (!empty($registeredObj['regErrors'])) {
                            $this->registerError($this->__('Your registration request has been saved, however the problems listed below were detected during the registration process. Please contact the site administrator regarding the status of your request.'));
                        } elseif ($moderation && ($verifyEmail != Users_Constant::VERIFY_NO)) {
                            if ($moderationOrder == Users_Constant::APPROVAL_AFTER) {
                                $this->registerStatus($this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified and your request must be approved before you will be able to log in. Please check your e-mail for an e-mail address verification message. Your account will not be approved until after the verification process is completed.'));
                            } elseif ($moderationOrder == Users_Constant::APPROVAL_BEFORE) {
                                $this->registerStatus($this->__('Done! Your registration request has been saved. Remember that your request must be approved and your e-mail address must be verified before you will be able to log in. Please check your e-mail periodically for a message from us. You will receive a message after we have reviewed your request.'));
                            } else {
                                $this->registerStatus($this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified and your request must be approved before you will be able to log in. Please check your e-mail for an e-mail address verification message.'));
                            }
                        } elseif ($moderation) {
                            $this->registerStatus($this->__('Done! Your registration request has been saved. Remember that your request must be approved before you will be able to log in. Please check your e-mail periodically for a message from us. You will receive a message after we have reviewed your request.'));
                        } elseif ($verifyEmail != Users_Constant::VERIFY_NO) {
                            $this->registerStatus($this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified before you will be able to log in. Please check your e-mail for an e-mail address verification message.'));
                        } else {
                            // Some unknown state! Should never get here, but just in case...
                            $this->registerError($this->__('Your registration request has been saved, however your current registration status could not be determined. Please contact the site administrator regarding the status of your request.'));
                        }
                    } elseif ($registeredObj['activated'] == Users_Constant::ACTIVATED_ACTIVE) {
                        // The user has a status that allows him to log in
                        if (!empty($registeredObj['regErrors'])) {
                            $this->registerError($this->__('Your account has been created and you may now log in, however the problems listed below were detected during the registration process. Please contact the site administrator for more information.'));
                        } else {
                            $this->registerStatus($this->__('Done! Your account has been created and you may now log in.'));
                        }
                        $canLogIn = true;
                    } else {
                        // Shouldn't really get here out of the registration process, but cover all the bases.
                        $this->registerError($this->__('Your registration request has been saved, however the problems listed below were detected during the registration process. Please contact the site administrator regarding the status of your request.'));
                        $registeredObj['regErrors'][] = $this->__('Your account status will not permit you to log in at this time. Please contact the site administrator for more information.');
                    }

                    $proceedToForm = false;
                    
                    // Notify that we are completing a registration session.
                    $event = new Zikula_Event('user.registration.succeeded');
                    EventUtil::notify($event);
                } else {
                    $this->registerError($this->__('Error! Could not create the new user account or registration application. Please check with a site administrator before re-registering.'));

                    $proceedToForm = false;

                    // Notify that we are completing a registration session.
                    $event = new Zikula_Event('user.registration.failed');
                    EventUtil::notify($event);
                }
            }
        } elseif ($this->request->isGet()) {
            // Notify that we are beginning a registration session.
            $event = new Zikula_Event('user.registration.started');
            EventUtil::notify($event);
            $registeredObj = array();
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if ($proceedToForm || !$registeredObj) {
            $rendererArgs = array(
                'errorFields'   => isset($errorFields) ? $errorFields : array(),
                'errorMessages' => isset($errorMessages) ? $errorMessages : array(),
            );

            return $this->view->assign_by_ref('formData', $formData)
                    ->assign($rendererArgs)
                    ->fetch('users_user_register.tpl');
        } elseif (!empty($registeredObj['regErrors']) || !$canLogIn) {
            return $this->view->fetch('users_user_displaystatusmsg.tpl');
        } elseif ($this->getVar(Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN, Users_Constant::DEFAULT_REGISTRATION_AUTO_LOGIN)) {
            $loginMethod = $this->getVar(Users_Constant::MODVAR_LOGIN_METHOD, Users_Constant::DEFAULT_LOGIN_METHOD);
            if (($loginMethod == Users_Constant::LOGIN_METHOD_UNAME) || ($loginMethod == Users_Constant::LOGIN_METHOD_ANY)) {
                $loginArgs = array(
                    'authentication_method' => array(
                        'modname'   => $this->name,
                        'method'    => 'uname',
                    ),
                    'authentication_info'   => array(
                        'login_id'  => $registeredObj['uname'],
                        'pass'      => $reginfo['pass'],
                    ),
                    'rememberme'            => false,
                    'returnurl'             => System::getHomepageUrl(),
                );
            } else {
                $loginArgs = array(
                    'authentication_method' => array(
                        'modname'   => $this->name,
                        'method'    => 'email',
                    ),
                    'authentication_info'   => array(
                        'login_id'  => $registeredObj['email'],
                        'pass'      => $reginfo['pass'],
                    ),
                    'rememberme'            => false,
                    'returnurl'             => System::getHomepageUrl(),
                );
            }
            return ModUtil::func($this->name, 'user', 'login', $loginArgs);
        } else {
            $this->redirect(ModUtil::url($this->name, 'user', 'login'));
        }
    }

    /**
     * Create a new user.
     *
     * Available Post Parameters:
     *
     * @return bool True if successful, false otherwise.
     */
    public function registerNewUser()
    {

    }

    /**
     * Display the lost user name / password choices.
     *
     * @return string The rendered template.
     */
    public function lostPwdUname()
    {
        // we shouldn't get here if logged in already....
        $this->redirectIf(UserUtil::isLoggedIn(), ModUtil::url($this->name, 'user', 'main'));

        return $this->view->fetch('users_user_lostpwduname.tpl');
    }

    /**
     * Display the lost user name form.
     *
     * @return string The rendered template.
     */
    public function lostUname()
    {
        // we shouldn't get here if logged in already....
        $this->redirectIf(UserUtil::isLoggedIn(), ModUtil::url($this->name, 'user', 'main'));

        $sessionVars = $this->request->getSession()->get('Users_Controller_User_mailUname', array(), 'Zikula_Users');
        $this->request->getSession()->del('Users_Controller_User_mailUname', 'Zikula_Users');

        $email = isset($sessionVars['email']) ? $sessionVars['email'] : '';

        return $this->view->assign('email', $email)
                ->fetch('users_user_lostuname.tpl');
    }

    /**
     * Send the user a lost uname.
     *
     * Available Post Parameters:
     * - email (string) The user's e-mail address.
     * - code  (string) The confirmation code.
     *
     * @return bool True if successful request or expected error, false if unexpected error.
     */
    public function mailUname()
    {
        $emailMessageSent = false;

        $this->checkCsrfToken();

        $email = $this->request->getPost()->get('email', null);


        if (empty($email)) {
            $this->registerError($this->__('Error! E-mail address field is empty.'));
        } else {
            // save username and password for redisplay
            SessionUtil::requireSession();
            $this->request->getSession()->del('Users_Controller_User_mailUname', 'Zikula_Users');
            $sessionVars = array(
                'email' => $email,
            );
            $this->request->getSession()->set('Users_Controller_User_mailUname', $sessionVars, 'Zikula_Users');

            $emailMessageSent = ModUtil::apiFunc($this->name, 'user', 'mailUname', array(
                'idfield'   => 'email',
                'id'        => $email
            ));
        }

        if ($emailMessageSent) {
            $this->request->getSession()->del('Users_Controller_User_mailUname', 'Zikula_Users');
            $this->registerStatus($this->__f('Done! The user name for %s has been sent via e-mail.', $email))
                    ->redirect(ModUtil::url($this->name, 'user', 'login'));
        } else {
            $this->registerError($this->__('Sorry! We are unable to send a user name reminder for that e-mail address. Please contact an administrator.'))
                    ->redirect(ModUtil::url($this->name, 'user', 'lostUname'));
        }
    }

    /**
     * Display the lost password form.
     *
     * @return string The rendered template.
     */
    public function lostPassword()
    {
        // we shouldn't get here if logged in already....
        $this->redirectIf(UserUtil::isLoggedIn(), ModUtil::url($this->name, 'user', 'main'));

        $sessionVars = $this->request->getSession()->get('Users_Conroller_User_mailConfirmationCode', array(), 'Zikula_Users');
        $templateVariables = array(
            'uname' => isset($sessionVars['uname']) ? $sessionVars['uname'] : '',
            'email' => isset($sessionVars['email']) ? $sessionVars['email'] : '',
        );
        $this->request->getSession()->del('Users_Conroller_User_mailConfirmationCode', 'Zikula_Users');

        return $this->view->assign($templateVariables)
                ->fetch('users_user_lostpassword.tpl');
    }

    /**
     * Send the user a confirmation code in order to reset a lost password.
     *
     * Available Post Parameters:
     * - uname (string) The user's user name.
     * - email (string) The user's e-mail address.
     * - code  (string) The confirmation code.
     *
     * @return bool True if successful request or expected error, false if unexpected error.
     */
    public function mailConfirmationCode()
    {
        $emailMessageSent = false;

        $this->checkCsrfToken();

        $uname = $this->request->getPost()->get('uname', null);
        $email = $this->request->getPost()->get('email', null);

        if (empty($uname) && empty($email)) {
            $this->registerError($this->__('Error! User name and e-mail address fields are empty.'));
        } elseif (!empty($email) && !empty($uname)) {
            $this->registerError($this->__('Error! Please enter either a user name OR an e-mail address, but not both of them.'));
        } else {
            SessionUtil::requireSession();
            $this->request->getSession()->del('Users_Conroller_User_mailConfirmationCode', 'Zikula_Users');
            $sessionVars = array();
            if (!empty($uname)) {
                $idfield = 'uname';
                $idvalue = $uname;
                // save username for redisplay
                $sessionVars['uname'] = $uname;
            } else {
                $idfield = 'email';
                $idvalue = $email;
                // save email for redisplay
                $sessionVars['email'] = $email;
            }
            $this->request->getSession()->set('Users_Conroller_User_mailConfirmationCode', $sessionVars, 'Zikula_Users');

            $emailMessageSent = ModUtil::apiFunc($this->name, 'user', 'mailConfirmationCode', array(
                'idfield' => $idfield,
                'id' => $idvalue
            ));
        }

        if ($emailMessageSent) {
            $this->request->getSession()->del('Users_Conroller_User_mailConfirmationCode', 'Zikula_Users');
            $this->registerStatus($this->__f('Done! The confirmation code for %s has been sent via e-mail.', $idvalue))
                    ->redirect(ModUtil::url($this->name, 'user', 'lostPasswordCode'));
        } else {
            if ($idfield == 'email') {
                $errorMessage = $this->__('Sorry! We are unable to send a password recovery code for that e-mail address. Please try your user name, or contact an administrator.');
            } else {
                $errorMessage = $this->__('Sorry! We are unable to send a password recovery code for that user name. Please try your e-mail address, contact an administrator.');
            }
            $this->registerError($errorMessage, null, ModUtil::url($this->name, 'user', 'lostPassword'));
            return false;
        }
    }

    /**
     * Display the lost password confirmation code entry form.
     *
     * @return string The rendered template.
     */
    public function lostPasswordCode()
    {
        // we shouldn't get here if logged in already....
        $this->redirectIf(UserUtil::isLoggedIn(), ModUtil::url($this->name, 'user', 'main'));

        $sessionVars = $this->request->getSession()->get('Users_Controller_User_passwordReminder', array(), 'Zikula_Users');
        $this->request->getSession()->del('Users_Controller_User_passwordReminder', 'Zikula_Users');

        $templateVariables = array(
            'uname' => $this->request->getGet()->get('uname', isset($sessionVars['uname']) ? $sessionVars['uname'] : null),
            'email' => $this->request->getGet()->get('email', isset($sessionVars['email']) ? $sessionVars['email'] : null),
            'code'  => $this->request->getGet()->get('code',  isset($sessionVars['code']) ? $sessionVars['code'] : null),
        );

        return $this->view->assign($templateVariables)
                ->fetch('users_user_lostpasswordcode.tpl');
    }

    /**
     * Show the user his password reminder.
     *
     * Available Post Parameters:
     * - uname (string) The user's user name.
     * - email (string) The user's e-mail address.
     * - code  (string) The confirmation code.
     *
     * @return bool True if successful request or expected error, false if unexpected error.
     */
    public function passwordReminder()
    {
        $emailMessageSent = false;

        $this->checkCsrfToken();

        if ($this->request->isPost()) {
            $uname = $this->request->getPost()->get('uname', null);
            $email = $this->request->getPost()->get('email', null);
            $code  = $this->request->getPost()->get('code',  null);
        } elseif ($this->request->isGet()) {
            $uname = $this->request->getGet()->get('uname', null);
            $email = $this->request->getGet()->get('email', null);
            $code  = $this->request->getGet()->get('code',  null);
        }

        if (empty($uname) && empty($email)) {
            $this->registerError($this->__('Error! User name and e-mail address fields are empty.'));
        } elseif (!empty($email) && !empty($uname)) {
            $this->registerError($this->__('Error! Please enter either a user name OR an e-mail address, but not both of them.'));
        } else {
            if (!empty($uname)) {
                $idfield = 'uname';
                $idvalue = $uname;
            } else {
                $idfield = 'email';
                $idvalue = $email;
            }

            $checkConfArgs =array(
                'idfield' => $idfield,
                'id'      => $idvalue,
                'code'    => $code,
            );
            if (ModUtil::apiFunc($this->name, 'user', 'checkConfirmationCode', $checkConfArgs)) {
                $this->request->getSession()->del('Users_Controller_User_passwordReminder', 'Zikula_Users');
                $userInfo = UserUtil::getVars($idvalue, true, $idfield);
                $passwordReminder = $userInfo['passreminder'];
            } else {
                $this->registerError($this->__("Error! The code that you've enter is invalid."));
            }
        }

        if (!isset($userInfo)) {
            // $userInfo is not set, so there was an error prior to an attempt to get the user.
            // save username and password for redisplay
            SessionUtil::requireSession();
            $this->request->getSession()->del('Users_Controller_User_passwordReminder', 'Zikula_Users');
            $sessionVars = array(
                'uname' => $uname,
                'email' => $email,
                'code'  => $code,
            );
            $this->request->getSession()->set('Users_Controller_User_passwordReminder', $sessionVars, 'Zikula_Users');

            $this->redirect(ModUtil::url($this->name, 'user', 'lostPasswordCode'));
        } elseif (isset($userInfo) && !$userInfo) {
            // $userInfo is set, but false. There was a database error retrieving the user.
            $this->redirect(ModUtil::url($this->name, 'user', 'lostPasswordCode'));
        } else {
            // $userInfo is set, and not false, and $passwordReminder is available. Show it.
            $rendererArgs = array(
                'uname'             => $userInfo['uname'],
                'passreminder'      => $passwordReminder,
                'newpassreminder'   => '',
                'errormessages'     => array(),
            );

            return $this->view->assign($rendererArgs)
                    ->fetch('users_user_passwordreminder.tpl');
        }
    }

    /**
     * Render and process a password-reset, showing the password reminder if available.
     *
     * This function, as a result of successfully providing a verification code, will display
     * to the user his user name and password reminder, and give him the opportunity to reset his
     * password.
     *
     * @return string|bool The rendered template; true on redirect; false on error.
     */
    public function resetPassword()
    {
        $this->checkCsrfToken();

        if ($this->request->isPost()) {
            $uname          = $this->request->getPost()->get('uname', '');
            $newpass        = $this->request->getPost()->get('newpass', '');
            $newpassagain   = $this->request->getPost()->get('newpassagain', '');
            $newpassreminder= $this->request->getPost()->get('newpassreminder', '');
        } elseif ($this->request->isGet()) {
            $uname = $this->request->getGet()->get('lostpassword_uname', '');
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        $userinfo = UserUtil::getVars($uname, false, 'uname');

        if ($userinfo) {
            if ($this->request->isPost()) {
                $passwordErrors = ModUtil::apiFunc($this->name, 'registration', 'getPasswordErrors', array(
                    'uname'         => $uname,
                    'pass'          => $newpass,
                    'passagain'     => $newpassagain,
                    'passreminder'  => $newpassreminder,
                ));

                if (empty($passwordErrors)) {
                    $passwordSet = UserUtil::setPassword($newpass, $userinfo['uid']);

                    if ($passwordSet) {
                        $reminderSet = UserUtil::setVar('passreminder', $newpassreminder, $userinfo['uid']);

                        if (!$reminderSet) {
                            $this->registerError($this->__('Warning! Your new password has been saved, but there was an error while trying to save your new password reminder.'))
                                    ->redirect(ModUtil::url($this->name, 'user', 'login'));
                        } else {
                            $this->registerStatus($this->__('Done! Your password has been reset, and you may now log in. Please keep your password in a safe place!'))
                                    ->redirect(ModUtil::url($this->name, 'user', 'login'));
                        }
                    } else {
                        $this->registerError($this->__('Error! Your new password could not be saved.'))
                                ->redirect(ModUtil::url($this->name, 'user', 'lostPwdUname'));
                    }
                }
            }
        } else {
            $this->registerError($this->__('Sorry! Could not load that user account.'))
                    ->redirect(ModUtil::url($this->name, 'user', 'lostPwdUname'));
        }

        if (isset($passwordErrors) && !empty($passwordErrors)) {
            $errorInfo = ModUtil::apiFunc($this->name, 'user', 'processRegistrationErrorsForDisplay', array('registrationErrors' => $passwordErrors));
        } else {
            $errorInfo = array();
        }

        $rendererArgs = array(
            'uname'             => $uname,
            'passreminder'      => isset($userinfo['passreminder']) ? $userinfo['passreminder'] : '',
            'newpassreminder'   => isset($newpassreminder) ? $newpassreminder : '',
            'errormessages'     => (isset($errorInfo['errorMessages']) && !empty($errorInfo['errorMessages'])) ? $errorInfo['errorMessages'] : array(),
        );

        return $this->view->assign($rendererArgs)
                ->fetch('users_user_passwordreminder.tpl');
    }

    /**
     * Display the login form.
     *
     * @deprecated Since 1.3.0
     *
     * @param array $args All parameters passed to this function.
     *
     * @return string The rendered template.
     */
    public function loginScreen($args)
    {
        LogUtil::log(__('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);
        return $this->new_login($args);
    }

    /**
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
     * @throws Zikula_Exception_Redirect If the user is already logged in, or upon successful login with the redirect
     *                                   option set to send the user to the appropriate page, or...
     *
     *
     * @return boolean|string True on successful authentication and login, the rendered output of the appropriate
     *                        template to display the log-in form.
     */
    public function login($args)
    {
        // we shouldn't get here if logged in already....
        $this->redirectIf(UserUtil::isLoggedIn(), ModUtil::url($this->name, 'user', 'main'));

        $loggedIn = false;

        // Need to check for $args first, since isPost() and isGet() will have been set on the original call
        if (isset($args) && is_array($args) && !empty($args)) {
            // We are coming back (reentering) from someplace else via a direct call to this function. It is likely that
            // we are coming back from a user.login.veto event handler that redirected the user to a page where he had to provide
            // more information.
            $authenticationInfo     = isset($args['authentication_info']) ? $args['authentication_info'] : array();
            $selectedAuthenticationMethod = isset($args['authentication_method']) ? $args['authentication_method'] : array();
            $rememberMe             = isset($args['rememberme']) ? $args['rememberme'] : false;
            $returnUrl              = isset($args['returnurl']) ? $args['returnurl'] : $this->request->getGet()->get('returnurl', '');
        } elseif (isset($args) && !is_array($args)) {
            // Coming from a function call, but bad $args
            throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
        } elseif ($this->request->isPost()) {
            // We got here from a POST, either from the login, the login block, or some reasonable facsimile thereof.
            if (System::getVar('anonymoussessions', false)) {
                $this->checkCsrfToken();
            }

            $authenticationInfo     = $this->request->getPost()->get('authentication_info', array());
            $selectedAuthenticationMethod = $this->request->getPost()->get('authentication_method', array());
            $rememberMe             = $this->request->getPost()->get('rememberme', false);
            $returnUrl              = $this->request->getPost()->get('returnurl', $this->request->getGet()->get('returnurl', ''));
        } elseif ($this->request->isGet()) {
            if ($this->request->getSession()->has('Users_Controller_User_login', 'Zikula_Users')) {
                // We are coming back (reentering) from someplace else. It is likely that we are coming back from an external
                // authentication process initiated by an authentication module such as OpenID.
                $sessionVars = $this->request->getSession()->get('Users_Controller_User_login', array(), 'Zikula_Users');
                $this->request->getSession()->del('Users_Controller_User_login', 'Zikula_Users');

                $authenticationInfo     = isset($sessionVars['authentication_info']) ? $sessionVars['authentication_info'] : array();
                $selectedAuthenticationMethod = isset($sessionVars['authentication_method']) ? $sessionVars['authentication_method'] : array();
                $rememberMe             = isset($sessionVars['rememberme']) ? $sessionVars['rememberme'] : false;
                $returnUrl              = isset($sessionVars['returnurl']) ? $sessionVars['returnurl'] : $this->request->getGet()->get('returnurl', '');
                $user                   = isset($sessionVars['user_obj']) ? $sessionVars['user_obj'] : null;
            } else {
                $authenticationInfo     = array();
                $selectedAuthenticationMethod = array();
                $rememberMe             = false;
                $returnUrl              = $this->request->getGet()->get('returnurl', '');
            }
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        // Any authentication information for use in this pass through login is gathered, so ensure any session variable
        // is cleared, even if we are coming in through a post or a function call that didn't gather info from the session.
        $this->request->getSession()->del('Users_Controller_User_login', 'Zikula_Users');

        if ($this->request->isPost() || (isset($args) && is_array($args) && !empty($args))) {
            if (isset($authenticationInfo) && is_array($authenticationInfo) && !empty($authenticationInfo)) {
                // A form submission, or a simulated submission as a function call.

                // Save the submitted information in case the authentication method is external and reentrant.
                //
                // We're using sessions here, even though anonymous sessions might be turned off for anonymous users.
                // If the user is trying to log in, then he's going to get a session if he's successful,
                // so using sessions on the anonymous user just before logging in should be ok.
                SessionUtil::requireSession();
                $sessionVars = array(
                    'returnurl'             => $returnUrl,
                    'authentication_info'   => $authenticationInfo,
                    'authentication_method' => $selectedAuthenticationMethod,
                    'rememberme'            => $rememberMe,
                );
                $this->request->getSession()->set('Users_Controller_User_login', $sessionVars, 'Zikula_Users');

                // The authentication method selected might be reentrant (it might send the user out to an external web site
                // for authentication, and then send us back to finish the job). We need to tell the external system to where
                // we would like to return.
                $reentrantURL = System::getBaseUrl() . ModUtil::url($this->name, 'user', 'login', array('csrftoken', SecurityUtil::generateCsrfToken($this->serviceManager)));

                // There may be hook providers that need to be validated, so we cannot yet log in. The hook providers will
                // need a user object to make sure they know who they're dealing with. Authenticate (so we are sure that
                // the user is who he says he is) and get a user.
                //
                // The chosen authentication method might be reentrant, and this is the point were the user might be directed
                // outside the Zikula system for external authentication.
                $user = UserUtil::authenticateUserUsing($selectedAuthenticationMethod, $authenticationInfo, $reentrantURL);

                // If we have gotten to this point in the same call to login(), then the authentication method was not external
                // and reentrant, so we should not need the session variable any more. If it is external and reentrant, and the
                // user was required to exit the Zikula system for authentication on the external system, then we will not get
                // to this point until the reentrant call back to login() (at which point the variable should, again, not be needed
                // anymore).
                $this->request->getSession()->del('Users_Controller_User_login', 'Zikula_Users');

                // Did we get a good user? If so, then we can proceed to hook validation.
                if (isset($user) && $user && is_array($user) && isset($user['uid']) && is_numeric($user['uid'])) {
                    $validators = new Zikula_Hook_ValidationProviders();
                    $validationEvent = $this->notifyHooks('users.hook.login.validate.edit', $user, $user['uid'], array(), $validators);
                    $validators = $validationEvent->getData();

                    if (!$validators->hasErrors()) {
                        // Process the edit hooks BEFORE we log in, so that any changes to the user record are recorded before we re-check
                        // the user's ability to log in. If we don't do this, then user.login.veto might trap and cancel the login attempt again.
                        $this->notifyHooks('users.hook.login.process.edit', $user, $user['uid'], array('formType' => 'page'));

                        // Because we are passing a $user and setting checkPassword false, this call back into the authentication
                        // chain should not trigger an external re-authentication, so it should not need preparation for reentry.
                        $loggedIn = UserUtil::loginUsing($selectedAuthenticationMethod, $authenticationInfo, $rememberMe, $reentrantURL, false, $user);

                        // A successful login.
                        if ($this->getVar('login_redirect', 1) == 1) {
                            // WCAG compliant login
                            $this->redirect($returnUrl);
                        } else {
                            // meta refresh
                            $this->printRedirectPage($this->__('You are being logged-in. Please wait...'), $returnUrl);
                        }

                    } elseif (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                        $this->registerError($this->__('Your log-in request was not completed.'));
                    }
                } else {
                    $this->registerError($this->__('There is no user account matching that information, or the password you gave does not match the password on file for that account.'));
                }
            } elseif (isset($authenticationInfo) && (!is_array($authenticationInfo))) {
                $this->registerError($this->__('Error! Invalid authentication information received.'));
            }
        }

        if (!$loggedIn) {
            // Either a GET request type to initially display the login form, or a failed login attempt
            // which means the login form should be displayed anyway.
            $authenticationMethodList = new Users_Helper_AuthenticationMethodList($this);

            if ((!isset($selectedAuthenticationMethod) || empty($selectedAuthenticationMethod))
                    && ($authenticationMethodList->countEnabledForAuthentication() <= 1)
                    ) {
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

            return $this->view->assign(array (
                        'returnurl'                             => isset($returnUrl) ? $returnUrl : '',
                        'authentication_info'                   => isset($authenticationInfo) ? $authenticationInfo : array(),
                        'selected_authentication_method'        => $selectedAuthenticationMethod,
                        'authentication_method_display_order'   => $authenticationMethodDisplayOrder,
                        'user_obj'                              => isset($user) ? $user : array(),
                    ))
                    ->fetch('users_user_login.tpl');
        } else {
            // We only get here if the user logged in and the site is configured for meta refresh.
            return true;
        }
    }

    /**
     * Log a user out.
     *
     * The user is redirected to the entry point of the site, or to a redirect
     * page if specified in the site configuration.
     *
     * @return bool True (whether successfully logged out or not.)
     */
    public function logout()
    {
        $login_redirect = $this->getVar('login_redirect');

        // start logout event
        $uid = UserUtil::getVar('uid');
        if (UserUtil::logout()) {
            
            if ($login_redirect == 1) {
                // WCAG compliant logout - we redirect to index.php because
                // we might no have the permission for the recent site any longer
                $this->redirect(System::getHomepageUrl());
            } else {
                // meta refresh
                $this->printRedirectPage($this->__('Done! You have been logged out.'), System::getHomepageUrl());
            }
        } else {
            $this->registerError($this->__('Error! You have not been logged out.'))
                    ->redirect(System::getHomepageUrl());
        }

        return true;
    }

    /**
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
     * @return string|bool The rendered template; true on redirect; false on error.
     */
    public function verifyRegistration()
    {
        if (UserUtil::isLoggedIn()) {
            $this->registerError($this->__('Sorry! An account cannot be verified while you are logged in.'))
                    ->redirect(ModUtil::url($this->name, 'user', 'main'));
        }

        if ($this->request->isGet()) {
            $uname      = $this->request->getGet()->get('uname', '');
            $verifycode = $this->request->getGet()->get('verifycode', '');
        } elseif ($this->request->isPost()) {
            $this->checkCsrfToken();
            $uname          = $this->request->getPost()->get('uname', '');
            $verifycode     = $this->request->getPost()->get('verifycode', '');
            $newpass        = $this->request->getPost()->get('newpass', '');
            $newpassagain   = $this->request->getPost()->get('newpassagain', '');
            $newpassreminder= $this->request->getPost()->get('newpassreminder', '');
        } else {
            throw new Zikula_Exception_Forbidden();
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

                    if ($this->request->isPost()) {
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
                                $this->registerError($this->__('Sorry! There was an error while trying to save your new password and reminder.'));
                            } else {
                                $reginfo['pass'] = $newpassHash;
                            }

                            $passReminderSaved = UserUtil::setVar('passreminder', $newpassreminder, $reginfo['uid']);
                            if (!$passReminderSaved) {
                                $this->registerError($this->__('Sorry! There was an error while trying to save your new password and reminder.'));
                            } else {
                                $reginfo['passreminder'] = $newpassreminder;
                            }
                        }
                    }
                }

                if ($verifycode && $reginfo && isset($reginfo['pass']) && !empty($reginfo['pass'])
                        && isset($reginfo['passreminder']) && !empty($reginfo['passreminder'])) {

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

                                switch ($verified['activated']) {
                                    case Users_Constant::ACTIVATED_PENDING_REG:
                                        if (empty($verified['approved_by'])) {
                                            $message = $this->__('Done! Your account has been verified, and is awaiting administrator approval.');
                                        } else {
                                            $message = $this->__('Done! Your account has been verified. Your registration request is still pending completion. Please contact the site administrator for more information.');
                                        }
                                        $this->registerStatus($message);
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $this->registerStatus($regErrorsMessage);
                                        }
                                        return $this->view->fetch('users_user_displaystatusmsg.tpl');
                                        break;
                                    case Users_Constant::ACTIVATED_ACTIVE:
                                        $this->registerStatus($this->__('Done! Your account has been verified. You may now log in with your user name and password.'));
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $this->registerStatus($regErrorsMessage);
                                            return $this->view->fetch('users_user_displaystatusmsg.tpl');
                                        } else {
                                            $this->redirect(ModUtil::url($this->name, 'user', 'login'));
                                        }
                                        break;
                                    default:
                                        $this->registerStatus($this->__('Done! Your account has been verified.'));
                                        $this->registerStatus($this->__('Your new account is not active yet. Please contact the site administrator for more information.'));
                                        if (isset($verified['regErrors']) && count($verified['regErrors']) > 0) {
                                            $this->registerStatus($regErrorsMessage);
                                        }
                                        return $this->view->fetch('users_user_displaystatusmsg.tpl');
                                        break;
                                }
                            } else {
                                if (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                                    $this->registerError($this->__('Sorry! There was an error while marking your registration as verifed. Please contact an administrator.'))
                                            ->redirect(ModUtil::url($this->name, 'user', 'main'));
                                } else {
                                    $this->redirect(ModUtil::url($this->name, 'user', 'main'));
                                }
                            }
                        } else {
                            $this->registerError($this->__('Sorry! The verification code you provided does not match our records. Please check the code, and also check your e-mail for a newer verification code that might have been sent.'));
                        }
                    } elseif ($verifyChg === false) {
                        $this->registerError($this->__('Error! There was a problem retrieving the verification code for comparison.'));
                        return false;
                    } else {
                        $this->registerError($this->__f('Error! There is no pending verification code for \'%1$s\'. Please contact the site administrator.', array($reginfo['uname'])));
                        return false;
                    }
                }
                // No code, or no password. Pass down through to the template rendering.
            } else {
                $this->registerError($this->__('Sorry! A registration does not exist for the user name you provided. Maybe your request has expired? Please check the user name, or contact an administrator.'));
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

        return $this->view->add_core_data()
                ->assign($rendererArgs)
                ->fetch('users_user_verifyregistration.tpl');
    }

    /**
     * LEGACY user account activation.
     *
     * We must keep this function, because there is no way to know whether an inactive account
     * is inactive because it needs activation, or for some other reason set manually by the site admin.
     *
     * Available Get/Post Parameters;
     * - code (string) Confirmation/Activation code.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['code'] (string) Used as a default if the get/post parameter 'code' is not set.
     *
     * @return bool True on success, otherwise false.
     */
    public function activation($args)
    {
        $code = base64_decode(FormUtil::getPassedValue('code', (isset($args['code']) ? $args['code'] : null), 'GETPOST'));
        $code = explode('#', $code);

        if (!isset($code[0]) || !isset($code[1])) {
            $this->registerError($this->__('Error! Could not activate your account. Please contact the site administrator.'));
            return false;
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
                $this->registerError($this->__('Error! Could not activate your account. Please contact the site administrator.'));
                return false;
            }
            $this->registerStatus($this->__('Done! Account activated.'))
                    ->redirect(ModUtil::url($this->name, 'user', 'login'));
        } else {
            $this->registerError($this->__('Sorry! You entered an invalid confirmation code. Please correct your entry and try again.'));
            return false;
        }
    }

    /**
     * Print a (legacy) login/logout redirect page. Internal use only, not intended to be called through the API.
     *
     * @param string $message The message to display on the redirect page.
     * @param string $url     The URL of the page to redirect to after this redirect page has been displayed.
     *
     * @access private
     *
     * @return bool True.
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
                ->display('users_user_redirectpage.tpl');

        return true;
    }

    /**
     * Log into a site that is currently "off" (normal logins are not allowed).
     *
     * Allows the administrator to access the site during maintenance.
     *
     * Available Post Parameters:
     * - user       (string) The user name of the user attempting to log in.
     * - pass       (string) The password of the user attempting to log in.
     * - rememberme (int)    Whether the login session should persist.
     *
     * @return bool True.
     */
    public function siteOffLogin()
    {
        // do not process if the site is enabled
        $this->redirectIf(!System::getVar('siteoff', false), System::getHomepageUrl());

        if ($this->request->isPost()) {
            $user = $this->request->getPost()->get('user', null);
            $pass = $this->request->getPost()->get('pass', null);
            $rememberme = $this->request->getPost()->get('rememberme', false);
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        $authenticationInfo = array(
            'login_id'  => $user,
            'pass'      => $pass
        );
        $authenticationMethod = array(
            'modname'   => $this->name,
            'method'    => 'uname',
        );

        if (UserUtil::loginUsing($authenticationMethod, $authenticationInfo, $rememberme)) {
            if (!SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN)) {
                UserUtil::logout();
            }
        }

        $this->redirect(System::getHomepageUrl());
    }

    /**
     * Display the configuration options for the users block.
     *
     * @return string The rendered template.
     */
    public function usersBlock()
    {
        $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall');
        $mid = ModUtil::getIdFromName($this->name);
        $found = false;
        foreach ($blocks as $block) {
            if ($block['mid'] == $mid && $block['bkey'] == 'user') {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Zikual_Exception_Fatal();
        }

        return $this->view->assign(UserUtil::getVars(UserUtil::getVar('uid')))
                ->fetch('users_user_usersblock.tpl');
    }

    /**
     * Update the custom users block.
     *
     * Available Post Parameters:
     * - ublockon (int)   Whether the block is displayed or not.
     * - ublock   (mixed) ?.
     *
     * @return bool True on success, otherwise false.
     */
    public function updateUsersBlock()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden();
        }

        $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall');
        $mid = ModUtil::getIdFromName($this->name);
        $found = false;
        foreach ($blocks as $block) {
            if ($block['mid'] == $mid && $block['bkey'] == 'user') {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Zikula_Exception_Fatal();
        }

        if ($this->request->isPost()) {
            $ublockon = (bool)$this->request->getPost()->get('ublockon', false);
            $ublock = (string)$this->request->getPost()->get('ublock', '');
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        $uid = UserUtil::getVar('uid');

        UserUtil::setVar('ublockon', $ublockon);
        UserUtil::setVar('ublock', $ublock);

        $this->registerStatus($this->__('Done! Saved custom block.'))
                ->redirect(ModUtil::url($this->name));
    }

    /**
     * Display the change password form.
     *
     * @return string The rendered template.
     */
    public function changePassword($args)
    {
        // Retrieve and delete any session variables being sent in before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $sessionVars = $this->request->getSession()->get('Users_Controller_User_changePassword', null, 'Zikula_Users');
        $this->request->getSession()->del('Users_Controller_User_changePassword', 'Zikula_Users');

        // The check for $args must be first, because isPost() and isGet() will be set for the function that called this one
        if (isset($args) && !empty($args) && is_array($args)) {
            // Arrived via function call
            
            if (!isset($args['login'])) {
                $args['login'] = false;
            }
        } elseif (isset($args) && !is_array($args)) {
            // Arrived via function call with bad $args
            throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
        } elseif ($this->request->isPost()) {
            // Arrived from a form post
            $args['login'] = $this->request->getPost()->get('login', false);
        } elseif ($this->request->isGet()) {
            // Arrived from a simple URL
            $args['login'] = $this->request->getGet()->get('login', false);
        }

        // In order to change one's password, the user either must be logged in already, or specifically
        // must be coming from the login process. This is an exclusive-or. It is an error if neither is set,
        // and likewise if both are set. One or the other, please!
        if (!$args['login'] && !UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden();
        } elseif ($args['login'] && UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Fatal();
        }

        // If we are coming here from the login process, then there are certain things that must have been
        // send along in the session variable. If not, then error.
        if ($args['login'] && (!isset($sessionVars['user_obj']) || !is_array($sessionVars['user_obj'])
                || !isset($sessionVars['authentication_info']) || !is_array($sessionVars['authentication_info'])
                || !isset($sessionVars['authentication_method']) || !is_array($sessionVars['authentication_method']))
                ) {
            throw new Zikula_Exception_Fatal();
        }

        if ($this->getVar('changepassword', 1) != 1) {
            $this->redirect($this->name, 'user', 'main');
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
            $this->request->getSession()->set('Users_Controller_User_updatePassword', $sessionVars, 'Zikula_Users');
        }

        // Return the output that has been generated by this function
        return $this->view->add_core_data()
                ->assign('password_errors', $passwordErrors)
                ->assign('login', (bool)$args['login'])
                ->assign('user_obj', ($args['login'] ? $sessionVars['user_obj'] : null))
                ->assign('authentication_method', ($args['login'] ? $sessionVars['authentication_method'] : null))
                ->fetch('users_user_changepassword.tpl');
    }

    /**
     * Update the user's password.
     *
     * Available Post Parameters:
     * - oldpassword        (string) The original password.
     * - newpassword        (string) The new password to be stored for the user.
     * - newpasswordconfirm (string) Verification of the new password to be stored for the user.
     *
     * @return bool True on success, otherwise false.
     */
    public function updatePassword()
    {
        $sessionVars = $this->request->getSession()->get('Users_Controller_User_updatePassword', null, 'Zikula_Users');
        $this->request->getSession()->del('Users_Controller_User_updatePassword', 'Zikula_Users');

        if (!$this->request->isPost()) {
            throw new Zikula_Exception_Forbidden();
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
            throw new Zikula_Exception_Forbidden();
        } elseif ($login && UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Fatal();
        }

        $passwordChanged    = false;
        $currentPassword    = $this->request->getPost()->get('oldpassword', '');
        $newPassword        = $this->request->getPost()->get('newpassword', '');
        $newPasswordAgain   = $this->request->getPost()->get('newpasswordconfirm', '');
        $newPasswordReminder= $this->request->getPost()->get('passreminder', '');
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
                $passwordChanged = true;

                // Clear the forced change of password flag, if it exists.
                UserUtil::delVar('_Users_mustChangePassword', $uid);

                if (!UserUtil::setVar('passreminder', $newPasswordReminder, $uid)) {
                    $this->registerError($this->__('Warning! Your new password was saved, however there was a problem saving your new password reminder.'));
                } else {
                    $this->registerStatus($this->__('Done! Saved your new password.'));
                }

                $userObj = UserUtil::getVars(UserUtil::getVar('uid'), true);
                if ($login) {
                    $sessionVars['user_obj'] = $userObj;
                    if ($sessionVars['authentication_method']['modname'] == $this->name) {
                        // The password for Users module authentication was just changed.
                        // In order to successfully log in the user, we need to change it on the authentication_info.
                        $sessionVars['authentication_info']['pass'] = $newPassword;
                    }
                }
            } else {
                throw new Zikula_Exception_Fatal($this->__('Sorry! There was a problem saving your new password.'));
            }
        }

        if ($passwordChanged) {
            if ($login) {
                $loginArgs = $this->request->getSession()->get('Users_Controller_User_login', array(), 'Zikula_Users');
                $loginArgs['authentication_method'] = $sessionVars['authentication_method'];
                $loginArgs['authentication_info']   = $sessionVars['authentication_info'];
                $loginArgs['rememberme']            = $sessionVars['rememberme'];
                return ModUtil::func($this->name, 'user', 'login', $loginArgs);
            } else {
                return $this->redirect(ModUtil::url($this->name, 'user', 'main'));
            }
        } else {
            $sessionVars['password_errors'] = $passwordErrors;
            SessionUtil::requireSession();
            $this->request->getSession()->set('Users_Controller_User_changePassword', $sessionVars, 'Zikula_Users');
            $this->redirect(ModUtil::url($this->name, 'user', 'changePassword', array('login' => $login)));
        }
    }

    /**
     * Display the change email address form.
     *
     * @return string The rendered template.
     */
    public function changeEmail()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($this->getVar('changeemail', 1) != 1) {
            $this->redirect($this->name, 'user', 'main');
        }

        return $this->view->add_core_data()
                ->fetch('users_user_changeemail.tpl');
    }

    /**
     * Update the email address.
     *
     * Available Post Parameters:
     * - newemail (string) The new e-mail address to store for the user.
     *
     * @return bool True on success, otherwise false.
     */
    public function updateEmail()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden();
        }

        $this->checkCsrfToken();

        $uservars = $this->getVars();
        if ($uservars['changeemail'] <> 1) {
            $this->redirect($this->name, 'user', 'main');
        }

        $newemail = FormUtil::getPassedValue('newemail', '', 'POST');
        $newemailagain = FormUtil::getPassedValue('newemailagain', '', 'POST');

        $emailErrors = ModUtil::apiFunc($this->name, 'registration', 'getEmailErrors', array(
            'uid'           => $uservars['uid'],
            'email'         => $newemail,
            'emailagain'    => $newemailagain,
            'checkmode'     => 'modify',
        ));

        if (!empty($emailErrors)) {
            foreach ($emailErrors as $field => $errorList) {
                foreach ($errorList as $errorMessage) {
                    $this->registerError($errorMessage);
                }
            }
            $this->redirect(ModUtil::url($this->name, 'user', 'changeEmail'));
        }

        // save the provisional email until confimation
        $verificationSent = ModUtil::apiFunc($this->name, 'user', 'savePreEmail', array('newemail' => $newemail));

        if (!$verificationSent) {
            $this->registerError($this->__('Error! There was a problem saving your new e-mail address or sending you a verification message.'))
                    ->redirect(ModUtil::url($this->name, 'user', 'changeEmail'));
        }

        $this->registerStatus($this->__('Done! You will receive an e-mail to your new e-mail address to confirm the change. You must follow the instructions in that message in order to verify your new address.'))
                ->redirect(ModUtil::url($this->name, 'user', 'main'));
    }

    /**
     * Display the form that allows the user to change the language displayed to him on the site.
     *
     * @return string The rendered template.
     */
    public function changeLang()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden();
        }

        // Assign the languages
        return $this->view->assign('languages', ZLanguage::getInstalledLanguageNames())
                ->assign('usrlang', ZLanguage::getLanguageCode())
                ->fetch('users_user_changelang.tpl');
    }

    /**
     * Confirm the update of the email address.
     *
     * Available Get Parameters:
     * - confirmcode (string) The confirmation code.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['confirmcode'] (string) Default value for the 'confirmcode' get parameter. Allows this function to be called internally.
     *
     * @return bool True on success, otherwise false.
     */
    public function confirmChEmail($args)
    {
        $confirmcode = FormUtil::getPassedValue('confirmcode', isset($args['confirmcode']) ? $args['confirmcode'] : null, 'GET');

        if (!UserUtil::isLoggedIn()) {
            $this->registerError($this->__('Please log into your account in order to confirm your change of e-mail address.'))
                    ->redirect(ModUtil::url($this->name, 'user', 'login', array('returnurl' => urlencode(ModUtil::url($this->name, 'user', 'confirmChEmail', array('confirmcode' => $confirmcode))))));
        }

        // get user new email that is waiting for confirmation
        $preemail = ModUtil::apiFunc($this->name, 'user', 'getUserPreEmail');

        $validCode = UserUtil::passwordsMatch($confirmcode, $preemail['verifycode']);

        if (!$preemail || !$validCode) {
            $this->registerError($this->__('Error! Your e-mail has not been found. After your request you have five days to confirm the new e-mail address.'))
                    ->redirect(ModUtil::url($this->name, 'user', 'main'));
        }

        // user and confirmation code are correct. set the new email
        UserUtil::setVar('email', $preemail['newemail']);

        // the preemail record is deleted
        ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array(
            'uid'       => $preemail['uid'],
            'changetype'=> Users_Constant::VERIFYCHGTYPE_EMAIL,
        ));

        $this->registerStatus($this->__('Done! Changed your e-mail address.'))
                ->redirect(ModUtil::url($this->name, 'user', 'main'));
    }
}
