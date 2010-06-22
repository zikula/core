<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 */

/**
 * Controllers provide users access to actions that they can perform on the system;
 * this class provides access to administrator-initiated actions for the Users module.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Admin extends Zikula_Controller
{
    /**
     * Determines if the user currently logged in has administrative access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrator access for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN);
    }

    /**
     * Determines if the user currently logged in has add access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrative permission for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdminOrSubAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD);
    }

    /**
     * Redirects users to the "view" page.
     *
     * @return string HTML string containing the rendered view template.
     */
    public function main()
    {
        // Security check will be done in view()
        return System::redirect(ModUtil::url('Users', 'admin', 'view'));
    }

    /**
     * Display a form to add a new user account.
     *
     * Available Request Parameters:
     * - userinfo (array) An associative array of initial values for the form fields. The elements of the array correspond to the
     *      post parameters expected by $this->createUser().
     *
     * @return string HTML string containing the rendered template.
     */
    public function newUser()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if (ModUtil::getVar('Users', 'reg_allowreg', false) && !SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $registrationUnavailableReason = ModUtil::getVar('Users', 'reg_noregreasons',
                $this->__('Sorry! New user registration is currently disabled.'));
            return LogUtil::registerError($registrationUnavailableReason, 403, System::getHomepageUrl());
        }

        $reginfo = array();
        $reginfo['dynadata'] = array();
        $setPassword = false;
        $passwordAgain = '';
        $emailAgain = '';
        $sendPassword = false;

        // If we are returning here from validation errors detected in createUser, then get the data already entered
        $args = SessionUtil::getVar('Users_Admin_newUser', array(), '/', false);
        SessionUtil::delVar('Users_Admin_newUser');

        if (!empty($args)) {
            $reginfo = $args['reginfo'];

            $setPassword = $args['setpass'];
            //$passwordAgain = $args['passagain'];
            $emailAgain = $args['emailagain'];
            $sendPassword = $args['sendpass'];

            $registrationErrors = isset($args['registrationErrors']) ? $args['registrationErrors'] : array();
            // For now do it this way. Later maybe show the messages with the field--and if that's
            // done, then $errorFields and $errorMessages not needed--we'd just pass $registrationErrors directly.
            $errorInfo = ModUtil::apiFunc('Users', 'user', 'processRegistrationErrorsForDisplay', array('registrationErrors' => $registrationErrors));
        }

        $modVars = ModUtil::getVar('Users');
        $profileModName = System::getVar('profilemodule', '');
        $profileModAvailable = !empty($profileModName) && ModUtil::available($profileModName);

        $rendererArgs = array();
        $rendererArgs['reginfo'] = $reginfo;
        $rendererArgs['setpass'] = $setPassword;
        //$rendererArgs['passagain'] = $passwordAgain;
        $rendererArgs['emailagain'] = $emailAgain;
        $rendererArgs['sendpass'] = $sendPassword;
        $rendererArgs['sitename'] = System::getVar('sitename', System::getHost());
        $rendererArgs['regAllowed'] = (isset($modVars['reg_allowreg']) && !empty($modVars['reg_allowreg']))
            ? $modVars['reg_allowreg']
            : false;
        $rendererArgs['regOffReason'] = (isset($modVars['$reg_noregreasons']) && !empty($modVars['$reg_noregreasons']))
            ? $modVars['$reg_noregreasons']
            : $this->__('We will begin accepting new registrations again as quickly as possible. Please check back with us soon!');
        $rendererArgs['userMustAccept'] = $rendererArgs['touActive'] || $rendererArgs['ppActive'];
        $rendererArgs['errorMessages'] = (isset($errorInfo['errorMessages']) && !empty($errorInfo['errorMessages'])) ? $errorInfo['errorMessages'] : array();
        $rendererArgs['errorFields'] = (isset($errorInfo['errorFields']) && !empty($errorInfo['errorFields'])) ? $errorInfo['errorFields'] : array();
        $rendererArgs['registrationErrors'] = (isset($registrationErrors) && !empty($registrationErrors)) ? $registrationErrors : array();
        $rendererArgs['usePwdStrengthMeter'] = (isset($modVars['use_password_strength_meter']) && !empty($modVars['use_password_strength_meter'])) ? $modVars['use_password_strength_meter'] : false;
        $rendererArgs['showProps'] = $profileModAvailable && isset($modVars['reg_optitems']) && $modVars['reg_optitems'];
        $rendererArgs['profileModName'] = $profileModName;

        // Return the output that has been generated by this function
        $this->renderer->setCaching(false);
        
        $this->renderer->assign($rendererArgs);
        return $this->renderer->fetch('users_admin_newuser.htm');
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
        // check permisisons
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if (!SecurityUtil::confirmAuthKey('Users')) {
            return LogUtil::registerAuthidError(ModUtil::url('Users', 'admin', 'view'));
        }
        
        // get arguments
        $reginfo = FormUtil::getPassedValue('reginfo', null, 'POST');
        if (isset($reginfo['uname']) && !empty($reginfo['uname'])) {
            $reginfo['uname'] = mb_strtolower($reginfo['uname']);
        }
        if (isset($reginfo['email']) && !empty($reginfo['email'])) {
            $reginfo['email'] = mb_strtolower($reginfo['email']);
        }
        $reginfo['dynadata'] = FormUtil::getPassedValue('dynadata', array(), 'POST');

        $checkMode = 'new';
        $setPassword = FormUtil::getPassedValue('setpass', true, 'POST');
        $emailAgain = FormUtil::getPassedValue('emailagain', null, 'POST');
        $passwordAgain = !$setPassword ? null : FormUtil::getPassedValue('passagain', null, 'POST');
        $userMustVerify = !$setPassword || FormUtil::getPassedValue('usermustverify', false, 'POST');
        $sendPassword = $setPassword && FormUtil::getPassedValue('sendpass', false, 'POST');

        if ($setPassword) {
            if (!isset($reginfo['pass'])) {
                // Ensure set and empty for validation.
                $reginfo['pass'] = '';
            }
            if (!isset($reginfo['passreminder'])) {
                $reginfo['passreminder'] = $this->__('(Password provided by site administrator)');
            }
        } else {
            // The fields may have had values but were hidden. Ensure they are not set.
            unset($reginfo['pass']);
            unset($reginfo['passreminder']);
        }

        // Set agreetoterms property, so we know to ask the user to agree on activation or login.
        $reginfo['agreetoterms'] = false;

        $registrationErrors = ModUtil::apiFunc('Users', 'registration', 'getRegistrationErrors', array(
            'checkmode'     => $checkMode,
            'reginfo'       => $reginfo,
            'setpass'       => $setPassword,
            'sendpass'      => $sendPassword,
            'passagain'     => $passwordAgain,
            'emailagain'    => $emailAgain,
        ));

        if ($registrationErrors) {
            SessionUtil::setVar('reginfo', $reginfo, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('setpass', $setPassword, 'Users_Admin_newUser', true, true);
            //SessionUtil::setVar('passagain', $passwordAgain, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('emailagain', $emailAgain, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('usermustverify', $userMustVerify, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('sendpass', $sendPassword, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('registrationErrors', $registrationErrors, 'Users_Admin_newUser', true, true);

            return System::redirect(ModUtil::url('Users', 'admin', 'newUser'));
        }

        $currentUserEmail = UserUtil::getVar('email');
        $adminNotifyEmail = ModUtil::getVar('Users', 'reg_notifyemail', '');
        $adminNotification = (strtolower($currentUserEmail) != strtolower($adminNotifyEmail));

        $registeredObj = ModUtil::apiFunc('Users', 'registration', 'registerNewUser', array(
            'reginfo'           => $reginfo,
            'usermustverify'    => $userMustVerify,
            'sendpass'          => $sendPassword,
            'usernotification'  => true,
            'adminnotification' => true,
        ));

        if ($registeredObj) {
            if (isset($registeredObj['uid'])) {
                LogUtil::registerStatus($this->__('Done! Created new user account.'));
            } elseif (isset($registeredObj['id'])) {
                LogUtil::registerStatus($this->__('Done! Created new registration application.'));
            } else {
                LogUtil::log($this->__('Internal Warning! Unknown return type from Users_Api_Registration#registerUser().'), 'DEBUG');
                LogUtil::registerError($this->__('Warning! New user information has been saved, however there may have been an issue saving it properly. Please check with a site administrator before re-registering.'));
            }
        } else {
            LogUtil::registerError($this->__('Error! Could not create the new user account or registration application. Please check with a site administrator before re-registering.'));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'view'));
    }

    /**
     * Shows all items and lists the administration options.
     *
     * Available Get Parameters:
     * - startnum (int)    The ordinal number at which to start displaying user records.
     * - letter   (string) The first letter of the user names to display.
     *
     * @param array $args All parameters passed to the function.
     *                    $args['startnum'] (int) The ordinal number at which to start displaying user records. Used as a default if
     *                      the get parameter is not set. Allows the function to be called internally.
     *                    $args['letter'] (string) The first letter of the user names to display. Used as a default if
     *                      the get parameter is not set. Allows the function to be called internally.
     *
     * @return string HTML string containing the rendered template.
     */
    public function view($args = array())
    {
        // Get parameters from whatever input we need.
        $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');
        $letter = FormUtil::getPassedValue('letter', isset($args['letter']) ? $args['letter'] : null, 'GET');

        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);

        // we need this value multiple times, so we keep it
        $itemsperpage = ModUtil::getVar('Users', 'itemsperpage');

        // Get all users
        $items = ModUtil::apiFunc('Users', 'user', 'getAll', array(
            'startnum'  => $startnum,
            'numitems'  => $itemsperpage,
            'letter'    => $letter
        ));

        // Get all groups
        $groups = ModUtil::apiFunc('Groups', 'user', 'getall');

        // check what groups can access the user
        $userGroupsAccess = array();
        $groupsArray = array();
        $canSeeGroups = (!empty($groups));
        foreach ($groups as $group) {
            $userGroupsAccess[$group['gid']] = array('gid' => $group['gid']);

            // rewrite the groups array with the group id as key and the group name as value
            $groupsArray[$group['gid']] = array('name' => DataUtil::formatForDisplayHTML($group['name']));
        }

        $profileModule = System::getVar('profilemodule', '');
        $useProfileModule = (!empty($profileModule) && ModUtil::available($profileModule));

        // if module Legal is not available show the equivalent states for user activation value
        $adaptState = (!ModUtil::available('legal') || (!ModUtil::getVar('legal', 'termsofuse') && !ModUtil::getVar('legal', 'privacypolicy'))) ? 1 : 0;

        // Loop through each returned item adding in the options that the user has over
        // each item based on the permissions the user has.
        foreach ($items as $key => $item) {
            $options = array();
            $authId = SecurityUtil::generateAuthKey('Users');
            if (SecurityUtil::checkPermission('Users::', "$item[uname]::$item[uid]", ACCESS_READ) && $item['uid'] != 1) {

                // Options for the item.
                if ($useProfileModule) {
                    $options[] = array('url'   => ModUtil::url($profileModule, 'user', 'view', array('uid' => $item['uid'])),
                                       'image' => 'personal.gif',
                                       'title' => $this->__('View the profile'));
                }
                if (SecurityUtil::checkPermission('Users::', "{$item['uname']}::{$item['uid']}", ACCESS_MODERATE)) {
                    $options[] = array('url'   => ModUtil::url('Users', 'admin', 'lostUsername', array('uid' => $item['uid'], 'authid' => $authId)),
                                       'image' => 'lostusername.png',
                                       'title' => $this->__('Send user name'));

                    $options[] = array('url'   => ModUtil::url('Users', 'admin', 'lostPassword', array('uid' => $item['uid'], 'authid' => $authId)),
                                       'image' => 'lostpassword.png',
                                       'title' => $this->__('Send password recovery code'));

                    if (SecurityUtil::checkPermission('Users::', "$item[uname]::$item[uid]", ACCESS_EDIT)) {
                        $options[] = array('url'   => ModUtil::url('Users', 'admin', 'modify', array('userid' => $item['uid'])),
                                           'image' => 'xedit.gif',
                                           'title' => $this->__('Edit'));

                        if (SecurityUtil::checkPermission('Users::', "$item[uname]::$item[uid]", ACCESS_DELETE)) {
                            $options[] = array('url'   => ModUtil::url('Users', 'admin', 'deleteUsers', array('userid' => $item['uid'])),
                                               'image' => '14_layer_deletelayer.gif',
                                               'title' => $this->__('Delete'));
                        }
                    }
                }
                // get user groups
                $userGroups = ModUtil::apiFunc('Groups', 'user', 'getusergroups',
                                            array('uid' => $item['uid'],
                                                  'clean' => 1));
                // we need an associative array by the key to compare with the groups that the user can see
                $userGroupsByKey = array();
                foreach ($userGroups as $userGroup) {
                    $userGroupsByKey[$userGroup['gid']] = array('gid' => $userGroup['gid']);
                }
                $userGroupsView = array_intersect_key($userGroupsAccess, $userGroupsByKey);
            }

            if ($item['uid'] == 1) {
                $userGroupsView = array();
            }
            // format the dates
            if (!empty($item['user_regdate']) && ($item['user_regdate'] != '0000-00-00 00:00:00')
                && ($item['user_regdate'] != '1970-01-01 00:00:00'))
            {
                $items[$key]['user_regdate'] = DateUtil::formatDatetime($item['user_regdate'], $this->__('%m-%d-%Y'));
            } else {
                $items[$key]['user_regdate'] = '---';
            }

            if (!empty($item['lastlogin']) && ($item['lastlogin'] != '0000-00-00 00:00:00')
                && ($item['lastlogin'] != '1970-01-01 00:00:00'))
            {
                $items[$key]['lastlogin'] = DateUtil::formatDatetime($item['lastlogin'], $this->__('%m-%d-%Y'));
            } else {
                $items[$key]['lastlogin'] = '---';
            }

            // show user's activation state
            $activationImg = '';
            $activationTitle = '';
            // adapt states if it is necessary
            if ($adaptState) {
                if ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_TOUPP) {
                    $items[$key]['activated'] = UserUtil::ACTIVATED_ACTIVE;
                } else if ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP) {
                    $items[$key]['activated'] = UserUtil::ACTIVATED_INACTIVE_PWD;
                }
            }
            // show user's activation state
            if ($items[$key]['activated'] == UserUtil::ACTIVATED_ACTIVE) {
                $activationImg = 'greenled.gif';
                $activationTitle = $this->__('Active');
            } else if ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_TOUPP) {
                $activationImg = 'yellowled.gif';
                $activationTitle = $this->__('Inactive until Legal terms accepted');
            } else if ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_PWD) {
                $activationImg = 'yellowled.gif';
                $activationTitle = $this->__('Inactive until changing password');
            } else if ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP) {
                $activationImg = 'yellowled.gif';
                $activationTitle = $this->__('Inactive until change password and accept legal terms');
            } else {
                $activationImg = 'redled.gif';
                $activationTitle = $this->__('Inactive');
            }
            $items[$key]['activation'] = array('image' => $activationImg,
                                               'title' => $activationTitle);

            // Add the calculated menu options to the item array
            $items[$key]['options'] = $options;
            // Add the groups that the user can see to the item array
            $items[$key]['userGroupsView'] = $userGroupsView;
        }

        // Assign the items to the template
        $this->renderer->assign('usersitems', $items);

        // assign the values for the smarty plugin to produce a pager in case of there
        // being many items to display.
        $this->renderer->assign('pager', array('numitems'     => ModUtil::apiFunc('Users', 'user', 'countItems', array('letter' => $letter)),
                                               'itemsperpage' => $itemsperpage));

        // Assign the groups to the template
        $this->renderer->assign('allGroups', $groupsArray);

        // Inform to the template that user can see users' groups
        $this->renderer->assign('canSeeGroups', $canSeeGroups);

        // Return the output that has been generated by this function
        return $this->renderer->fetch('users_admin_view.htm');
    }

    /**
     * Displays a user account search form.
     *
     * @return string HTML string containing the rendered template.
     */
    public function search()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);

        // get group items
        $groups = ModUtil::apiFunc('Groups', 'user', 'getall');
        $this->renderer->assign('groups', $groups);

        return $this->renderer->fetch('users_admin_search.htm');
    }

    /**
     * List the users as a result of a form post.
     *
     * Available Post Parameters:
     * - uname         (string) A fragment of a user name on which to search using an SQL LIKE clause. The user name will be surrounded by wildcards.
     * - ugroup        (int)    A group id in which to search (only users who are members of the specified group are returned).
     * - email         (string) A fragment of an e-mail address on which to search using an SQL LIKE clause. The e-mail address will be surrounded by wildcards.
     * - regdateafter  (string) An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date after the date specified will be returned.
     * - regdatebefore (string) An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date before the date specified will be returned.
     * - dynadata      (array)  An array of search values to be passed to the designated profile module. Only those user records also satisfying the profile module's search of its data
     *                          are returned.
     *
     * @return string HTML string containing the rendered template.
     */
    public function listUsers()
    {
        $uname         = FormUtil::getPassedValue('uname', null, 'POST');
        $ugroup        = FormUtil::getPassedValue('ugroup', null, 'POST');
        $email         = FormUtil::getPassedValue('email', null, 'POST');
        $regdateafter  = FormUtil::getPassedValue('regdateafter', null, 'POST');
        $regdatebefore = FormUtil::getPassedValue('regdatebefore', null, 'POST');

        $dynadata      = FormUtil::getPassedValue('dynadata', null, 'POST');

        // call the api
        $items = ModUtil::apiFunc('Users', 'admin', 'findUsers', array(
            'uname'         => $uname,
            'email'         => $email,
            'ugroup'        => $ugroup,
            'regdateafter'  => $regdateafter,
            'regdatebefore' => $regdatebefore,
            'dynadata'      => $dynadata
        ));

        if (!$items) {
            LogUtil::registerError($this->__('Sorry! No matching users found.'), 404, ModUtil::url('Users', 'admin', 'search'));
        }

        // create output object
        $pnRender = Renderer::getInstance('Users', false);

        $pnRender->assign('mailusers', SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_COMMENT));
        $pnRender->assign('deleteusers', SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN));

        // assign the matching results
        $pnRender->assign('items', $items);

        return $pnRender->fetch('users_admin_listusers.htm');
    }

    /**
     * Perform one of several possible operations on a user as a result of a form post.
     *
     * Available Post Parameters:
     * - op                 (string)  The operation. One of: 'edit', 'delete', 'mail', 'approve', or 'deny'.
     * - do                 (string)  Used only for 'edit' or 'delete' operations; either the value 'yes' or null. Controls whether
     *                                  a confirmation page is displayed for the operation (value of null) or the operation is actually
     *                                  performed (value 'yes').
     * - userid             (numeric) The user id of the user record on which the operation is to be performed.
     * - uname              (string)  Used only for 'edit' operations; the user name to be saved to the user record.
     * - email              (string)  Used only for 'edit' operations; the e-mail address to be saved to the user record.
     * - activated          (bool)    Used only for 'edit' operations; the activation state to be saved to the user record.
     * - pass               (string)  Used only for 'edit' operations; the new password to be saved to the user record.
     * - vpass              (string)  Used only for 'edit' operations; the confirmation of the new password to be saved to the user record.
     * - theme              (string)  Used only for 'edit' operations; the name of the theme to be saved to the user record.
     * - access_permissions (array)   Used only for 'edit' operations; an array of group ids to which the user should belong.
     * - dynadata           (array)   Used only for 'edit' operations; an array of dynamic user data to be stored with the designated profile module for the user account.
     * - sendmail           (array)   Used only for 'mail' operations; an array containing the e-mail to be sent.
     * - tag                (int)     Used only for 'approve' and 'deny' operations; if not 1, then a confirmation page is displayed; if 1 the operation is carried out.
     * - action             (string)  Used only for 'approve' and 'deny' operations; a fragment of the name of the function to call, either 'approve' or 'deny'.
     *
     * @return mixed true successful, false or string otherwise
     */
    public function processUsers()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // get the arguments from our input
        $op     = FormUtil::getPassedValue('op', null, 'GETPOST');
        $do     = FormUtil::getPassedValue('do', null, 'POST');
        $userid = FormUtil::getPassedValue('userid', null, 'POST');

        if ($op == 'edit' && !empty($userid)) {
            if ($do != 'yes') {
                return System::redirect(ModUtil::url('Users', 'admin', 'modify', array('userid' => $userid)));
            } else {
                $uname              = FormUtil::getPassedValue('uname', null, 'POST');
                $email              = FormUtil::getPassedValue('email', null, 'POST');
                $activated          = FormUtil::getPassedValue('activated', null, 'POST');
                $pass               = FormUtil::getPassedValue('pass', null, 'POST');
                $vpass              = FormUtil::getPassedValue('vpass', null, 'POST');
                $theme              = FormUtil::getPassedValue('theme', null, 'POST');
                $access_permissions = FormUtil::getPassedValue('access_permissions', null, 'POST');
                $dynadata           = FormUtil::getPassedValue('dynadata', null, 'POST');

                $return = ModUtil::apiFunc('Users', 'admin', 'saveUser',
                                       array('uid'                => $userid,
                                             'uname'              => $uname,
                                             'email'              => $email,
                                             'activated'          => $activated,
                                             'pass'               => $pass,
                                             'vpass'              => $vpass,
                                             'theme'              => $theme,
                                             'dynadata'           => $dynadata,
                                             'access_permissions' => $access_permissions));

                if ($return == true) {
                    LogUtil::registerStatus($this->__("Done! Saved user's account information."));
                    return System::redirect(ModUtil::url('Users', 'admin', 'main'));
                } else {
                    return false;
                }
            }

        } elseif ($op == 'delete' && !empty($userid)) {
            $userid = FormUtil::getPassedValue('userid', null, 'POST');
            if ($do != 'yes') {
                return System::redirect(ModUtil::url('Users', 'admin', 'deleteUsers', array('userid' => $userid)));
            } else {
                $return = ModUtil::apiFunc('Users', 'admin', 'deleteUser', array('uid' => $userid));

                if ($return == true) {
                    return LogUtil::registerStatus($this->__('Done! Deleted user account.'), ModUtil::url('Users', 'admin', 'main'));
                }
                return false;
            }

        } elseif ($op == 'mail' && !empty($userid) && SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_COMMENT)) {
            $userid   = FormUtil::getPassedValue('userid', array(), 'POST');
            $sendmail = FormUtil::getPassedValue('sendmail', array(), 'POST');
            if (empty($sendmail['from']) || empty($sendmail['rpemail']) || empty($sendmail['subject']) || empty($sendmail['message'])) {
                return LogUtil::registerError($this->__('Error! One or more information items needed to send an e-mail message are missing.'),
                                              null,
                                              ModUtil::url('Users', 'admin', 'search'));
            }

            $bcclist = array();
            $mailssent = 0;
            $recipientscount = 0;
            foreach ($sendmail['recipientsemail'] as $uid => $recipient) {
                if (in_array($uid, $userid)) {
                    $bcclist[] = array('name'    => $sendmail['recipientsname'][$uid],
                                       'address' => $recipient);
                }
                if (count($bcclist) == $sendmail['batchsize']) {
                    if (ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                                     array('fromname'       => $sendmail['from'],
                                           'fromaddress'    => $sendmail['rpemail'],
                                           'toname'         => UserUtil::getVar('uname'),
                                           'toaddress'      => UserUtil::getVar('email'),
                                           'replytoname'    => UserUtil::getVar('uname'),
                                           'replytoaddress' => $sendmail['rpemail'],
                                           'subject'        => $sendmail['subject'],
                                           'body'           => $sendmail['message'],
                                           'bcc'            => $bcclist)) == true) {
                        $mailssent++;
                        $recipientscount += count($bcclist);
                        $bcclist = array();
                    } else {
                        return LogUtil::registerError($this->__('Error! Could not send the e-mail message.'),
                                                      null,
                                                      ModUtil::url('Users', 'admin', 'main'));
                    }
                }
            }
            if (count($bcclist) <> 0) {
                if (ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                                 array('fromname'       => $sendmail['from'],
                                       'fromaddress'    => $sendmail['rpemail'],
                                       'toname'         => UserUtil::getVar('uname'),
                                       'toaddress'      => UserUtil::getVar('email'),
                                       'replytoname'    => UserUtil::getVar('uname'),
                                       'replytoaddress' => $sendmail['rpemail'],
                                       'subject'        => $sendmail['subject'],
                                       'body'           => $sendmail['message'],
                                       'bcc'            => $bcclist)) == true) {
                    $mailssent++;
                    $recipientscount += count($bcclist);
                } else {
                    return LogUtil::registerError($this->__('Error! Could not send the e-mail message.'),
                                                  null,
                                                  ModUtil::url('Users', 'admin', 'main'));
                }
            }
            if ($mailssent > 0) {
                LogUtil::registerStatus($this->_fn(
                    'Done! %1$c e-mail message has been sent to %2$c user.',
                    'Done! %1$c e-mail messages have been sent to %2$c users.',
                    $mailssent,
                    array($mailssent, $recipientscount)));
            }
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));

        } else {
            return LogUtil::registerError($this->__('Error! No users were selected.'));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'search'));
    }

    /**
     * Display a form to edit one user account.
     *
     * Available Get Parameters:
     * - userid (numeric) The user id of the user to be modified.
     * - uname  (string)  The user name of the user to be modified.
     *
     * @param array $args All arguments passed to the function.
     *                    $args['userid'] (numeric) the user id of the user to be modified. Used as a default value if the get parameter
     *                      is not set. Allow the function to be called internally.
     *                    $args['uname'] (string) the user name of the user to be modified. Used as a default value if the get parameter
     *                      is not set. Allow the function to be called internally.
     *
     * @return string HTML string containing the rendered template.
     */
    public function modify($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // get arguments
        $userid = FormUtil::getPassedValue('userid', (isset($args['userid']) ? $args['userid'] : null), 'GET');
        $uname  = FormUtil::getPassedValue('uname', (isset($args['uname']) ? $args['uname'] : null), 'GET');

        // check arguments
        if (is_null($userid) && is_null($uname)) {
            LogUtil::registerError($this->__('Sorry! No such user found.'));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // retreive userid from uname
        if (is_null($userid) && !empty($uname)) {
            $userid = UserUtil::getIdFromName($uname);
        }

        // warning for guest account
        if ($userid == 1) {
            LogUtil::registerError($this->__("Error! You can't edit the guest account."));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // get the user vars
        $uservars = UserUtil::getVars($userid);
        if ($uservars == false) {
            LogUtil::registerError($this->__('Sorry! No such user found.'));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // if module Legal is not available show the equivalent states for user activation value
        if (!ModUtil::available('legal') || (!ModUtil::getVar('legal', 'termsofuse') && !ModUtil::getVar('legal', 'privacypolicy'))) {
            if ($uservars['activated'] == UserUtil::ACTIVATED_INACTIVE_TOUPP) {
                $uservars['activated'] = UserUtil::ACTIVATED_ACTIVE;
            } else if ($uservars['activated'] == UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP) {
                $uservars['activated'] = UserUtil::ACTIVATED_INACTIVE_PWD;
            }
        }

        $this->renderer->setCaching(false);

        // urls
        $this->renderer->assign('urlprocessusers', ModUtil::url('Users', 'admin', 'processUsers', array('op' => 'edit', 'do' => 'yes')))
                       ->assign('op', 'edit')
                       ->assign('userid', $userid)
                       ->assign('userinfo', $uservars);

        // groups
        $groups_infos = array();
        $user_groups_register = array();
        $user_groups = ModUtil::apiFunc('Groups', 'user', 'getusergroups', array('uid' => $userid));
        $all_groups = ModUtil::apiFunc('Groups', 'user', 'getall');

        foreach ($user_groups as $user_group) {
            $user_groups_register[] = $user_group['gid'];
        }

        foreach ($all_groups as $group) {
            if (SecurityUtil::checkPermission('Groups::', "$group[gid]::", ACCESS_EDIT)) {
                $groups_infos[$group['gid']] = array();
                $groups_infos[$group['gid']]['name'] = $group['name'];

                if (in_array($group['gid'], $user_groups_register)) {
                    $groups_infos[$group['gid']]['access'] = true;
                } else {
                    $groups_infos[$group['gid']]['access'] = false;
                }
            }
        }

        $this->renderer->assign('groups_infos', $groups_infos)
                       ->assign('legal', ModUtil::available('legal'))
                       ->assign('tou_active', ModUtil::getVar('legal', 'termsofuse', true))
                       ->assign('pp_active',  ModUtil::getVar('legal', 'privacypolicy', true));

        return $this->renderer->fetch('users_admin_modify.htm');
    }

    /**
     * Allows an administrator to send a user his user name via email.
     *
     * @return bool True on success and redirect; otherwise false.
     */
    public function lostUsername()
    {
        if (!SecurityUtil::confirmAuthKey('Users')) {
            return LogUtil::registerAuthidError(ModUtil::url('Users', 'admin', 'view'));
        }

        $uid = FormUtil::getPassedValue('uid', null, 'GET');

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            return LogUtil::registerArgsError(ModUtil::url('Users', 'admin', 'view'));
        }

        $user = UserUtil::getVars($uid);
        if (!$user) {
            return LogUtil::registerError($this->__('Sorry! Unable to retrieve information for that user id.'));
        }

        if (!SecurityUtil::checkPermission('Users::', "{$user['uname']}::{$user['uid']}", ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $userNameSent = ModUtil::apiFunc('Users', 'user', 'mailUname', array(
            'idfield'       => 'uid',
            'id'            => $user['uid'],
            'adminRequest'  => true,
        ));

        if ($userNameSent) {
            LogUtil::registerStatus($this->__f('Done! The user name for %s has been sent via e-mail.', $user['uname']));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'view'));
    }

    /**
     * Allows an administrator to send a user a password recovery verification code.
     *
     * @return bool True on success and redirect; otherwise false.
     */
    public function lostPassword()
    {
        if (!SecurityUtil::confirmAuthKey('Users')) {
            return LogUtil::registerAuthidError(ModUtil::url('Users', 'admin', 'view'));
        }

        $uid = FormUtil::getPassedValue('uid', null, 'GET');

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            return LogUtil::registerArgsError(ModUtil::url('Users', 'admin', 'view'));
        }

        $user = UserUtil::getVars($uid);
        if (!$user) {
            return LogUtil::registerError($this->__('Sorry! Unable to retrieve information for that user id.'));
        }

        if (!SecurityUtil::checkPermission('Users::', "{$user['uname']}::{$user['uid']}", ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $confirmationCodeSent = ModUtil::apiFunc('Users', 'user', 'mailConfirmationCode', array(
            'idfield'       => 'uid',
            'id'            => $user['uid'],
            'adminRequest'  => true,
        ));

        if ($userNameSent) {
            LogUtil::registerStatus($this->__f('Done! The user name for %s has been sent via e-mail.', $user['uname']));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'view'));
    }

    /**
     * Display a form to confirm the deletion of one user.
     *
     * Available Get Parameters:
     * - userid (numeric) The user id of the user to be deleted.
     * - uname  (string)  The user name of the user to be deleted.
     *
     * @param array $args All arguments passed to the function.
     *                    $args['userid'] (numeric) the user id of the user to be deleted. Used as a default value if the get parameter
     *                      is not set. Allow the function to be called internally.
     *                    $args['uname'] (string) the user name of the user to be deleted. Used as a default value if the get parameter
     *                      is not set. Allow the function to be called internally.
     *
     * @return string HTML string containing the rendered template.
     */
    public function deleteUsers($args)
    {
        // check permissions
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // get arguments
        $userid = FormUtil::getPassedValue('userid', (isset($args['userid']) ? $args['userid'] : null), 'GET');
        $uname  = FormUtil::getPassedValue('uname', (isset($args['uname']) ? $args['uname'] : null), 'GET');

        // check arguments
        if (is_null($userid) && is_null($uname)) {
            LogUtil::registerError($this->__('Sorry! No such user found.'));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // retreive userid from uname
        if (is_null($userid) && !empty($uname)) {
            $userid = UserUtil::getIdFromName($uname);
        }

        // warning for guest account
        if ($userid == 1) {
            LogUtil::registerError($this->__("Error! You can't delete the guest account."));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // get the user vars
        $uname = UserUtil::getVar('uname', $userid);
        if ($uname == false) {
            LogUtil::registerError($this->__('Sorry! No such user found.'));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        $this->renderer->setCaching(false);

        $this->renderer->assign('userid', $userid)
                       ->assign('uname', $uname);

        // return output
        return $this->renderer->fetch('users_admin_deleteusers.htm');
    }

    /**
     * Internal function to construct a list of various actions for a list of registrations appropriate
     * for the current user.
     *
     * @param  array  $reglist      The list of registration records.
     * @param  string $restoreView  Indicates where the calling function expects to return to; 'view' indicates
     *                                  that the calling function expects to return to the registration list
     *                                  and 'display' indicates that the calling function expects to return
     *                                  to an individual registration record.
     *
     * @return array An array of valid action URLs for each registration record in the list.
     */
    protected function getActionsForRegistrations(array $reglist, $restoreView='view')
    {
        $actions = array();
        if (!empty($reglist)) {
            $approvalOrder = ModUtil::getVar('Users', 'moderation_order', UserUtil::APPROVAL_BEFORE);

            // Don't try to put any visual elements here (images, titles, colors, css classes, etc.). Leave that to
            // the template, so that they can be customized without hacking the core code. In fact, all we really need here
            // is what options are enabled. The template could build everything else. We will put the URL for the action
            // in the array for convenience, but that could be done in the template too, really.
            //
            // Make certain that the following goes from most restricted to least (ADMIN...NONE order).  Having the
            // security check as the outer if statement, and similar foreach loops within each saves on repeated checking
            // of permissions, speeding things up a bit.
            if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
                $actions['count'] = 6;
                foreach ($reglist as $key => $reginfo) {
                    $enableVerify = !$reginfo['isverified'];
                    $enableApprove = !$reginfo['isapproved'];
                    $enableForced = !$reginfo['isverified'] && isset($reginfo['pass']) && !empty($reginfo['pass']);
                    $actions['list'][$reginfo['id']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('id' => $reginfo['id'])),
                        'modify'        =>                  ModUtil::url('Users', 'admin', 'modifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url('Users', 'admin', 'approveRegistration',   array('id' => $reginfo['id'])) : false,
                        'deny'          =>                  ModUtil::url('Users', 'admin', 'denyRegistration',      array('id' => $reginfo['id'])),
                        'approveForce'  => $enableForced ?  ModUtil::url('Users', 'admin', 'approveRegistration',   array('id' => $reginfo['id'], 'force' => true)) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
                $actions['count'] = 5;
                foreach ($reglist as $key => $reginfo) {
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $enableApprove = !$reginfo['isapproved'] && (($approvalOrder != UserUtil::APPROVAL_AFTER) || $reginfo['isverified']);
                    $actions['list'][$reginfo['id']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('id' => $reginfo['id'])),
                        'modify'        =>                  ModUtil::url('Users', 'admin', 'modifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url('Users', 'admin', 'approveRegistration',   array('id' => $reginfo['id'])) : false,
                        'deny'          =>                  ModUtil::url('Users', 'admin', 'denyRegistration',      array('id' => $reginfo['id'])),
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
                $actions['count'] = 4;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['id'] = $reginfo['id'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $enableApprove = !$reginfo['isapproved'] && (($approvalOrder != UserUtil::APPROVAL_AFTER) || $reginfo['isverified']);
                    $actions['list'][$reginfo['id']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('id' => $reginfo['id'])),
                        'modify'        =>                  ModUtil::url('Users', 'admin', 'modifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url('Users', 'admin', 'approveRegistration',   array('id' => $reginfo['id'])) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
                $actions['count'] = 3;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['id'] = $reginfo['id'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $actions['list'][$reginfo['id']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('id' => $reginfo['id'])),
                        'modify'        =>                  ModUtil::url('Users', 'admin', 'modifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
                $actions['count'] = 2;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['id'] = $reginfo['id'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $actions['list'][$reginfo['id']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('id' => $reginfo['id'])),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('id' => $reginfo['id'], 'restoreview' => $restoreView)) : false,
                    );
                }
            }
        }
        
        return $actions;
    }

    /**
     * Shows all the registration requests (applications), and the options available to the current user.
     *
     * Available Request Parameters:
     * - startnum (int) The ordinal number of the first record to display, especially if using itemsperpage to limit the number of records on a single page.
     *
     * @return string HTML string containing the rendered template.
     */
    public function viewRegistrations()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $regCount = ModUtil::apiFunc('Users', 'registration', 'countAll');
        $limitNumRows = ModUtil::getVar('Users', 'itemsperpage', 25);
        if (!is_numeric($limitNumRows) || ((int)$limitNumRows != $limitNumRows) || (($limitNumRows < 1) && ($limitNumRows != -1))) {
            $limitNumRows = 25;
        }

        $backFromAction = FormUtil::getPassedValue('restoreview', false, 'GET');

        if ($backFromAction) {
            $returnArgs = SessionUtil::getVar('Users_admin_viewRegistrations', array('startnum' => 1), '/', false);
            SessionUtil::delVar('Users_admin_viewRegistrations');

            if ($limitNumRows < 1) {
                unset($returnArgs['startnum']);
            } elseif (!isset($returnArgs['startnum']) || !is_numeric($returnArgs['startnum']) || empty($returnArgs['startnum'])
                || ((int)$returnArgs['startnum'] != $returnArgs['startnum']) || ($returnArgs['startnum'] < 1))
            {
                $returnArgs['startnum'] = 1;
            } elseif ($returnArgs['startnum'] > $regCount) {
                // Probably deleted something. Reset to last page.
                $returnArgs['startnum'] = $regCount - ($regCount % $limitNumRows) + 1;
            } elseif (($returnArgs['startnum'] % $limitNumRows) != 1) {
                // Probably deleted something. Reset to last page.
                $returnArgs['startnum'] = $returnArgs['startnum'] - ($returnArgs['startnum'] % $limitNumRows) + 1;
            }

            // Reset the URL and load the proper page.
            return System::redirect(ModUtil::url('Users', 'admin', 'viewRegistrations', $returnArgs));
        } else {
            $reset = false;

            $startNum = FormUtil::getPassedValue('startnum', 1);
            if (!is_numeric($startNum) || empty($startNum)  || ((int)$startNum != $startNum) || ($startNum < 1)) {
                $limitOffset = -1;
                $reset = true;
            } elseif ($limitNumRows < 1) {
                $limitOffset = -1;
            } elseif ($startNum > $regCount) {
                // Probably deleted something. Reset to last page.
                $limitOffset = $regCount - ($regCount % $limitNumRows);
                $reset = (($regCount == 0) && ($startNum != 1));
            } elseif (($startNum % $limitNumRows) != 1) {
                // Reset to page boundary
                $limitOffset = $startNum - ($startNum % $limitOffset);
                $reset = true;
            } else {
                $limitOffset = $startNum - 1;
            }

            if ($reset) {
                $returnArgs = array();
                if ($limitOffset >= 0) {
                    $returnArgs['startnum'] = $limitOffset + 1;
                }
                System::redirect(ModUtil::url('Users', 'admin', 'viewRegistrations', $returnArgs));
            }
        }

        SessionUtil::setVar('startnum', ($limitOffset + 1), 'Users_admin_viewRegistrations');

        $reglist = ModUtil::apiFunc('Users', 'registration', 'getAll', array('limitoffset' => $limitOffset, 'limitnumrows' => $limitNumRows));

        if (($reglist === false) || !is_array($reglist)) {
            if (!LogUtil::hasErrors()) {
                LogUtil::registerError($this->__('An error occurred while trying to retrieve the registration records.'));
            }
            return System::redirect(ModUtil::url('Users', 'admin'), null, 500);
        }

        $actions = $this->getActionsForRegistrations($reglist, 'view');

        $pager = array();
        if ($limitNumRows > 0) {
            $pager = array(
                'rowcount'  => $regCount,
                'limit'     => $limitNumRows,
                'posvar'    => 'startnum',
            );
        }

        $this->renderer->setCaching(false);

        $this->renderer->assign('reglist', $reglist)
                       ->assign('actions', $actions)
                       ->assign('pager', $pager);

        return $this->renderer->fetch('users_admin_viewregistrations.htm');
    }

    /**
     * Displays the information on a single registration request (users_registration).
     *
     * Available Get Parameters:
     * - userid (numeric) The id of the registration request (id) to retrieve and display.
     *
     * @return string HTML string containing the rendered template.
     */
    public function displayRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters from whatever input we need.
        // (Note that the name of the passed parameter is 'userid' but that it
        // is actually a registration application id.)
        $id = FormUtil::getPassedValue('id', null, 'GET');

        if (empty($id) || !is_numeric($id)) {
            return LogUtil::registerArgsError(ModUtil::url('Users', 'admin', 'viewRegistrations', array('return' => true)));
        }

        $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $id));
        if (!$reginfo) {
            // get application could fail (return false) because of a nonexistant
            // record, no permission to read an existing record, or a database error
            return LogUtil::registerError($this->__('Unable to retrieve registration record. '
                . 'The record with the specified id might not exist, or you might not have permission to access that record.'));
        }

        // ...for the Profile module's display of dud items (it assumes a full user).
        // Be sure that this $reginfo is never used to update the database!
        $reginfo['__ATTRIBUTES__'] = $reginfo['dynadata'];

        if (ModUtil::available('legal')) {
            $touActive = ModUtil::getVar('legal', 'termsofuse', true);
            $ppActive = ModUtil::getVar('legal', 'privacypolicy', true);
        } else {
            $touActive = false;
            $ppActive = false;
        }

        $actions = $this->getActionsForRegistrations(array($reginfo), 'display');

        $this->renderer->setCaching(false);

        $this->renderer->assign('reginfo', $reginfo)
                       ->assign('actions', $actions)
                       ->assign('touActive', $touActive)
                       ->assign('ppActive', $ppActive);

        return $this->renderer->fetch('users_admin_displayregistration.htm');
    }

    /**
     * Display a form to edit one tegistration account.
     *
     * @return string|bool The rendered template; false on error.
     */
    public function modifyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $id = FormUtil::getPassedValue('id', null, 'GET');

        if (isset($id)) {
            if (!is_numeric($id) || ((int)$id != $id)) {
                return LogUtil::registerError($this->__('Error! Invalid registration id.'),
                    ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true)));
            }

            $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $id));

            if (!$reginfo) {
                return LogUtil::registerError($this->__('Error! Unable to load registration record.'),
                    ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true)));
            }

            $emailAgain = $reginfo['email'];
        } else {
            $args = SessionUtil::getVar('Users_Admin_modifyRegistration', array(), '/', false);
            SessionUtil::delVar('Users_Admin_modifyRegistration');
            
            if (!isset($args) || empty($args) || !isset($args['reginfo']) || empty($args['reginfo']) || !isset($args['registrationErrors']) || empty($args['registrationErrors'])) {
                return LogUtil::registerError($this->__('Error! Invalid registration id, or invalid arguments returned after validation.'),
                    ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true)));
            }

            $reginfo = $args['reginfo'];
            $emailAgain = $args['emailagain'];

            $registrationErrors = $args['registrationErrors'];
            // For now do it this way. Later maybe show the messages with the field--and if that's
            // done, then $errorFields and $errorMessages not needed--we'd just pass $registrationErrors directly.
            $errorInfo = ModUtil::apiFunc('Users', 'user', 'processRegistrationErrorsForDisplay', array('registrationErrors' => $registrationErrors));
        }

        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'GET');
        if ($restoreView == 'view') {
            $cancelUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        } else {
            $cancelUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('id' => $reginfo['id']));
        }

        $modVars = ModUtil::getVar('Users');
        $profileModName = System::getVar('profilemodule', '');
        $profileModAvailable = !empty($profileModName) && ModUtil::available($profileModName);

        $rendererArgs['reginfo'] = $reginfo;
        $rendererArgs['emailagain'] = $emailAgain;
        $rendererArgs['sitename'] = System::getVar('sitename', System::getHost());
        $rendererArgs['errorMessages'] = (isset($errorInfo['errorMessages']) && !empty($errorInfo['errorMessages'])) ? $errorInfo['errorMessages'] : array();
        $rendererArgs['errorFields'] = (isset($errorInfo['errorFields']) && !empty($errorInfo['errorFields'])) ? $errorInfo['errorFields'] : array();
        $rendererArgs['registrationErrors'] = (isset($registrationErrors) && !empty($registrationErrors)) ? $registrationErrors : array();
        $rendererArgs['usePwdStrengthMeter'] = (isset($modVars['use_password_strength_meter']) && !empty($modVars['use_password_strength_meter'])) ? $modVars['use_password_strength_meter'] : false;
        $rendererArgs['showProps'] = $profileModAvailable && isset($modVars['reg_optitems']) && $modVars['reg_optitems'];
        $rendererArgs['profileModName'] = $profileModName;
        $rendererArgs['restoreview'] = $restoreView;
        $rendererArgs['cancelurl'] = $cancelUrl;

        // Return the output that has been generated by this function
        $this->renderer->setCaching(false);

        $this->renderer->assign($rendererArgs);
        return $this->renderer->fetch('users_admin_modifyregistration.htm');
    }

    /**
     * Processes the results of modifyRegistration.
     *
     * @return bool True on success; otherwise false.
     */
    public function updateRegistration()
    {
        // check permisisons
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if (!SecurityUtil::confirmAuthKey('Users')) {
            return LogUtil::registerAuthidError(ModUtil::url('Users', 'admin', 'view'));
        }
        
        $reginfo = FormUtil::getPassedValue('reginfo', null, 'POST');
        $reginfo['dynadata'] = FormUtil::getPassedValue('dynadata', array(), 'POST');

        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'POST');
        if ($restoreView == 'display') {
            $doneUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('id' => $reginfo['id']));
        } else {
            $doneUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        $checkMode = 'modify';
        $setPassword = false;
        $emailAgain = FormUtil::getPassedValue('emailagain', null, 'POST');
        $sendPassword = false;

        $registrationErrors = ModUtil::apiFunc('Users', 'registration', 'getRegistrationErrors', array(
            'checkmode'     => $checkMode,
            'reginfo'       => $reginfo,
            'emailagain'    => $emailAgain,
        ));

        if ($registrationErrors) {
            SessionUtil::setVar('reginfo', $reginfo, 'Users_Admin_modifyRegistration', true, true);
            SessionUtil::setVar('emailagain', $emailAgain, 'Users_Admin_modifyRegistration', true, true);
            SessionUtil::setVar('registrationErrors', $registrationErrors, 'Users_Admin_modifyRegistration', true, true);

            return System::redirect(ModUtil::url('Users', 'admin', 'modifyRegistration'));
        }

        $reginfo = ModUtil::apiFunc('Users', 'registration', 'update', array('reginfo' => $reginfo));

        if ($reginfo) {
            LogUtil::registerStatus($this->__('Done! Updated registration.'));
        } else {
            LogUtil::registerError($this->__('Error! Could not update the registration.'));
        }

        return System::redirect($doneUrl);
    }

    /**
     * Renders and processes a form confirming an administrators desire to skip verification for
     * a registration record, approve it and add it to the users table.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     */
    public function verifyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $id = FormUtil::getPassedValue('id', null, 'GETPOST');
        $forceVerification = $this->currentUserIsAdmin() && FormUtil::getPassedValue('force', false, 'GETPOST');
        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'GETPOST');

        if (!isset($id) || !is_numeric($id) || ((int)$id != $id)) {
            return LogUtil::registerArgsError();
        }

        // Got just an id.
        $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $id));
        if (!$reginfo) {
            return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with id \'%1$s\'', $id));
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('id' => $reginfo['id']));
        } else {
            $cancelUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        $approvalOrder = ModUtil::getVar('Users', 'moderation_order', UserUtil::APPROVAL_BEFORE);

        if ($reginfo['isverified']) {
            return LogUtil::registerError(
                $this->__f('Error! A verification code cannot be sent for the registration record with id \'%1$s\'. It is already verified.', $reginfo['id']),
                null,
                $cancelUrl);
        } elseif (!$forceVerification && ($approvalOrder == UserUtil::APPROVAL_BEFORE) && !$reginfo['isapproved']) {
            return LogUtil::registerError(
                $this->__f('Error! A verification code cannot be sent for the registration record with id \'%1$s\'. It must first be approved.', $reginfo['id']),
                null,
                $cancelUrl);
        }

        if (!FormUtil::getPassedValue('confirmed', false, 'GETPOST') || !SecurityUtil::confirmAuthKey('Users')) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // ...for the Profile module's display of dud items (it assumes a full user).
            // Be sure that this $reginfo is never used to update the database!
            $reginfo['__ATTRIBUTES__'] = array_merge($reginfo['__ATTRIBUTES__'], $reginfo['dynadata']);

            if (ModUtil::available('legal')) {
                $touActive = ModUtil::getVar('legal', 'termsofuse', true);
                $ppActive = ModUtil::getVar('legal', 'privacypolicy', true);
            } else {
                $touActive = false;
                $ppActive = false;
            }

            $this->renderer->setCaching(false);

            $this->renderer->assign('reginfo', $reginfo)
                           ->assign('restoreview', $restoreView)
                           ->assign('force', $forceVerification)
                           ->assign('cancelurl', $cancelUrl)
                           ->assign('touActive', $touActive)
                           ->assign('ppActive', $ppActive);

            return $this->renderer->fetch('users_admin_verifyregistration.htm');
        } else {
            $codeSent = ModUtil::apiFunc('Users', 'registration', 'sendVerificationCode', array(
                'reginfo'   => $reginfo,
                'force'     => $forceVerification,
            ));

            if (!$codeSent) {
                return LogUtil::registerError($this->__f('Sorry! There was a problem sending a verification code to \'%1$s\'.', $reginfo['uname']), null, $cancelUrl);
            } else {
                return LogUtil::registerStatus($this->__f('Done! Verification code sent to \'%1$s\'.', $reginfo['uname']), $cancelUrl);
            }
        }
    }

    /**
     * Renders and processes a form confirming an administrators desire to approve a registration.
     *
     * If the registration record is also verified (or verification is not needed) a users table
     * record is created.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     */
    public function approveRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $id = FormUtil::getPassedValue('id', null, 'GETPOST');
        $forceVerification = $this->currentUserIsAdmin() && FormUtil::getPassedValue('force', false, 'GETPOST');
        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'GETPOST');

        if (!isset($id) || !is_numeric($id) || ((int)$id != $id)) {
            return LogUtil::registerArgsError();
        }

        // Got just an id.
        $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $id));
        if (!$reginfo) {
            return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with id \'%1$s\'', $id));
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('id' => $reginfo['id']));
        } else {
            $cancelUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        $approvalOrder = ModUtil::getVar('Users', 'moderation_order', UserUtil::APPROVAL_BEFORE);

        if ($reginfo['isapproved'] && !$forceVerification) {
            return LogUtil::registerError(
                $this->__f('Warning! Nothing to do! The registration record with id \'%1$s\' is already approved.', $reginfo['id']),
                null,
                $cancelUrl);
        } elseif (!$forceVerification && ($approvalOrder == UserUtil::APPROVAL_AFTER) && !$reginfo['isapproved']) {
            return LogUtil::registerError(
                $this->__f('Error! The registration record with id \'%1$s\' cannot be approved. The registration\'s e-mail address must first be verified.', $reginfo['id']),
                null,
                $cancelUrl);
        } elseif ($forceVerification && (!isset($reginfo['pass']) || empty($reginfo['pass']))) {
            return LogUtil::registerError(
                $this->__f('Error! E-mail verification cannot be skipped for \'%1$s\'. The user must establish a password as part of the verification process.', $reginfo['uname']),
                null,
                $cancelUrl);
        }

        if (!FormUtil::getPassedValue('confirmed', false, 'GETPOST') || !SecurityUtil::confirmAuthKey('Users')) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // ...for the Profile module's display of dud items (it assumes a full user).
            // Be sure that this $reginfo is never used to update the database!
            $reginfo['__ATTRIBUTES__'] = array_merge($reginfo['__ATTRIBUTES__'], $reginfo['dynadata']);

            if (ModUtil::available('legal')) {
                $touActive = ModUtil::getVar('legal', 'termsofuse', true);
                $ppActive = ModUtil::getVar('legal', 'privacypolicy', true);
            } else {
                $touActive = false;
                $ppActive = false;
            }

            $this->renderer->setCaching(false);

            $this->renderer->assign('reginfo', $reginfo)
                           ->assign('restoreview', $restoreView)
                           ->assign('force', $forceVerification)
                           ->assign('cancelurl', $cancelUrl)
                           ->assign('touActive', $touActive)
                           ->assign('ppActive', $ppActive);

            return $this->renderer->fetch('users_admin_approveregistration.htm');
        } else {
            $approved = ModUtil::apiFunc('Users', 'registration', 'approve', array(
                'reginfo'   => $reginfo,
                'force'     => $forceVerification,
            ));

            if (!$approved) {
                return LogUtil::registerError($this->__f('Sorry! There was a problem approving the registration for \'%1$s\'.', $reginfo['uname']), null, $cancelUrl);
            } else {
                if (isset($approved['uid'])) {
                    return LogUtil::registerStatus($this->__f('Done! The registration for \'%1$s\' has been approved and a new user account has been created.', $reginfo['uname']), $cancelUrl);
                } else {
                    return LogUtil::registerStatus($this->__f('Done! The registration for \'%1$s\' has been approved and is awaiting e-mail verification.', $reginfo['uname']), $cancelUrl);
                }
            }
        }
    }

    /**
     * Render and process a form confirming the administrator's rejection of a registration.
     *
     * If the denial is confirmed, the registration is deleted from the database.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     */
    public function denyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        $id = FormUtil::getPassedValue('id', null, 'GETPOST');
        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'GETPOST');

        if (!isset($id) || !is_numeric($id) || ((int)$id != $id)) {
            return LogUtil::registerArgsError();
        }

        // Got just an id.
        $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $id));
        if (!$reginfo) {
            return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with id \'%1$s\'', $id));
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('id' => $reginfo['id']));
        } else {
            $cancelUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        if (!FormUtil::getPassedValue('confirmed', false, 'GETPOST') || !SecurityUtil::confirmAuthKey('Users')) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // ...for the Profile module's display of dud items (it assumes a full user).
            // Be sure that this $reginfo is never used to update the database!
            $reginfo['__ATTRIBUTES__'] = array_merge($reginfo['__ATTRIBUTES__'], $reginfo['dynadata']);

            if (ModUtil::available('legal')) {
                $touActive = ModUtil::getVar('legal', 'termsofuse', true);
                $ppActive = ModUtil::getVar('legal', 'privacypolicy', true);
            } else {
                $touActive = false;
                $ppActive = false;
            }

            $this->renderer->setCaching(false);

            $this->renderer->assign('reginfo', $reginfo)
                           ->assign('restoreview', $restoreView)
                           ->assign('force', $forceVerification)
                           ->assign('cancelurl', $cancelUrl)
                           ->assign('touActive', $touActive)
                           ->assign('ppActive', $ppActive);

            return $this->renderer->fetch('users_admin_denyregistration.htm');
        } else {
            $sendNotification = FormUtil::getPassedValue('usernotify', false, 'POST');
            $reason = FormUtil::getPassedValue('reason', '', 'POST');

            $denied = ModUtil::apiFunc('Users', 'registration', 'remove', array(
                'reginfo'   => $reginfo,
            ));

            if (!$denied) {
                return LogUtil::registerError($this->__f('Sorry! There was a problem deleting the registration for \'%1$s\'.', $reginfo['uname']), null, $cancelUrl);
            } else {
                if ($sendNotification) {
                    $siteurl   = System::getBaseUrl();
                    $rendererArgs = array(
                        'sitename'  => System::getVar('sitename'),
                        'siteurl'   => substr($siteurl, 0, strlen($siteurl)-1),
                        'reginfo'   => $reginfo,
                        'reason'    => $reason,
                    );

                    $sent = ModUtil::apiFunc('Users', 'user', 'sendNotification', array(
                        'toAddress'         => $reginfo['email'],
                        'notificationType'  => 'deny',
                        'templateArgs'      => $rendererArgs
                    ));
                }
                return LogUtil::registerStatus($this->__f('Done! The registration for \'%1$s\' has been denied and deleted.', $reginfo['uname']), $cancelUrl);
            }
        }
    }

    /**
     * Edit user configuration settings.
     *
     * @see    function settings_admin_main()
     *
     * @return string HTML string containing the rendered template.
     */
    public function modifyConfig()
    {
        // Security check
        if (!(SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN))) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);

        // assign the module vars
        $this->renderer->assign('config', ModUtil::getVar('Users'));

        $profileModule = System::getVar('profilemodule', '');
        $this->renderer->assign('profile', (!empty($profileModule) && ModUtil::available($profileModule)));

        $this->renderer->assign('legal', ModUtil::available('legal'))
                       ->assign('tou_active', ModUtil::getVar('legal', 'termsofuse', true))
                       ->assign('pp_active',  ModUtil::getVar('legal', 'privacypolicy', true));

        $authmodules = array();
        $modules = ModUtil::getAllMods();
        foreach ($modules as $modinfo) {
            if (ModUtil::available($modinfo['name']) && ModUtil::hasApi($modinfo['name'], 'auth')) {
                $authmodules[] = $modinfo;
            }
        }
        $this->renderer->assign('authmodules', $authmodules);

        // Return the output that has been generated by this function
        return $this->renderer->fetch('users_admin_modifyconfig.htm');
    }

    /**
     * Update user configuration settings.
     *
     * Available Post Parameters:
     * - config (array) An associative array of configuration settings for the Users module.
     *
     * @see    function settings_admin_main()
     *
     * @return bool True if configuration saved; false if permission error.
     */
    public function updateConfig()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get our input
        $config = FormUtil::getPassedValue('config', '', 'POST');

        if (!isset($config['reg_noregreasons'])) {
            $config['reg_noregreasons'] = '';
        }

        ModUtil::setVar('Users', 'itemsperpage', $config['itemsperpage']);
        ModUtil::setVar('Users', 'accountdisplaygraphics', $config['accountdisplaygraphics']);
        ModUtil::setVar('Users', 'accountitemsperpage', $config['accountitemsperpage']);
        ModUtil::setVar('Users', 'accountitemsperrow', $config['accountitemsperrow']);
        ModUtil::setVar('Users', 'changepassword', $config['changepassword']);
        ModUtil::setVar('Users', 'changeemail', $config['changeemail']);
        ModUtil::setVar('Users', 'userimg', $config['userimg']);
        ModUtil::setVar('Users', 'reg_uniemail', $config['reg_uniemail']);
        ModUtil::setVar('Users', 'reg_optitems', $config['reg_optitems']);
        ModUtil::setVar('Users', 'reg_allowreg', $config['reg_allowreg']);
        ModUtil::setVar('Users', 'reg_noregreasons', $config['reg_noregreasons']);
        ModUtil::setVar('Users', 'moderation', $config['moderation']);
        ModUtil::setVar('Users', 'reg_verifyemail', $config['reg_verifyemail']);
        ModUtil::setVar('Users', 'reg_notifyemail', $config['reg_notifyemail']);
        ModUtil::setVar('Users', 'reg_Illegaldomains', $config['reg_Illegaldomains']);
        ModUtil::setVar('Users', 'reg_Illegalusername', $config['reg_Illegalusername']);
        ModUtil::setVar('Users', 'reg_Illegaluseragents', $config['reg_Illegaluseragents']);
        ModUtil::setVar('Users', 'minage', $config['minage']);
        ModUtil::setVar('Users', 'minpass', $config['minpass']);
        ModUtil::setVar('Users', 'anonymous', $config['anonymous']);
        ModUtil::setVar('Users', 'loginviaoption', $config['loginviaoption']);
        ModUtil::setVar('Users', 'hash_method', $config['hash_method']);
        ModUtil::setVar('Users', 'login_redirect', $config['login_redirect']);
        ModUtil::setVar('Users', 'reg_question', $config['reg_question']);
        ModUtil::setVar('Users', 'reg_answer', $config['reg_answer']);
        ModUtil::setVar('Users', 'use_password_strength_meter', $config['use_password_strength_meter']);
        ModUtil::setVar('Users', 'avatarpath', $config['avatarpath']);
        ModUtil::setVar('Users', 'allowgravatars', $config['allowgravatars']);
        ModUtil::setVar('Users', 'gravatarimage', $config['gravatarimage']);
        ModUtil::setVar('Users', 'default_authmodule', $config['default_authmodule']);

        if (ModUtil::available('legal')) {
            ModUtil::setVar('Legal', 'termsofuse', $config['termsofuse']);
            ModUtil::setVar('Legal', 'privacypolicy', $config['privacypolicy']);
        }
        // Let any other modules know that the modules configuration has been updated
        $this->callHooks('module', 'updateconfig', 'Users', array('module' => 'Users'));

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        return System::redirect(ModUtil::url('Users', 'admin', 'modifyConfig'));
    }

    /**
     * Show the form to choose a CSV file and import several users from this file.
     *
     * Available Post Parameters:
     * - confirmed  (int|bool) True if the user has confirmed the upload/import.
     * - importFile (array)    Structured information about the file to import, from <input type="file" name="fileFieldName" ... /> and stored in $_FILES['fileFieldName'].
     *                         See http://php.net/manual/en/features.file-upload.post-method.php .
     * - delimiter  (int)      A code indicating the type of delimiter found in the import file. 1 = comma, 2 = semicolon, 3 = colon.
     *
     * @param array $args All arguments passed to the function.
     *                    $args['confirmed'] (int|bool) True if the user has confirmed the upload/import. Used
     *                      as the default if $_POST['confirmed'] is not set. Allows this function to be called
     *                      internally, rather than as a result of a form post.
     *                    $args['importFile'] (array) Information about the file to import. Used as the default
     *                      if $_FILES['importFile'] is not set. Allows this function to be called internally,
     *                      rather than as a result of a form post.
     *                    $args['delimiter'] (int) A code indicating the delimiter used in the file. Used as the
     *                      default if $_POST['delimiter'] is not set. Allows this function to be called internally,
     *                      rather than as a result of a form post.
     *
     * @return redirect user to admin main page if success and show again the forn otherwise
     */
    public function import($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // get input values
        $confirmed     = FormUtil::getPassedValue('confirmed', (isset($args['confirmed']) ? $args['confirmed'] : null), 'POST');

        // set default parameters
        $minpass = ModUtil::getVar('Users', 'minpass');
        $defaultGroup = ModUtil::getVar('Groups', 'defaultgroup');

        if ($confirmed == 1) {
            // get other import values
            $importFile = FormUtil::getPassedValue('importFile', (isset($args['importFile']) ? $args['importFile'] : null), 'FILES');
            $delimiter = FormUtil::getPassedValue('delimiter', (isset($args['delimiter']) ? $args['delimiter'] : null), 'POST');
            $importResults = ModUtil::func('Users', 'admin', 'uploadImport',
                                       array('importFile' => $importFile,
                                             'delimiter' => $delimiter));
            if ($importResults == '') {
                // the users have been imported successfully
                LogUtil::registerStatus($this->__('Done! Users imported successfully.'));
                return System::redirect(ModUtil::url('Users', 'admin', 'main'));
            }
        }

        // shows the form
        $post_max_size = ini_get('post_max_size');
        // get default group
        $group = ModUtil::apiFunc('Groups','user','get',
                            array('gid' => $defaultGroup));
        $defaultGroup = $defaultGroup . ' (' . $group['name'] . ')';
        // Create output object
        $pnRender = Renderer::getInstance('Users', false);
        $pnRender->assign('importResults', $importResults);
        $pnRender->assign('post_max_size', $post_max_size);
        $pnRender->assign('minpass', $minpass);
        $pnRender->assign('defaultGroup', $defaultGroup);

        return $pnRender->fetch('users_admin_import.htm');
    }

    /**
     * Show the form to export a CSV file of users.
     *
     * Available Post Parameters:
     * - confirmed       (int|bool) True if the user has confirmed the export.
     * - exportFile      (array)    Filename of the file to export (optional) (default=users.csv)
     * - delimiter       (int)      A code indicating the type of delimiter found in the export file. 1 = comma, 2 = semicolon, 3 = colon, 4 = tab.
     * - exportEmail     (int)      Flag to export email addresses, 1 for yes.
     * - exportTitles    (int)      Flag to export a title row, 1 for yes.
     * - exportLastLogin (int)      Flag to export the last login date/time, 1 for yes.
     * - exportRegDate   (int)      Flag to export the registration date/time, 1 for yes.
     *
     * @param array $args All arguments passed to the function.
     *
     * @return redirect user to the form if confirmed not 1, else export the csv file.
     */
    public function export($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get input values
        $confirmed     = FormUtil::getPassedValue('confirmed', (isset($args['confirmed']) ? $args['confirmed'] : null), 'POST');

        if ($confirmed == 1) {
            // get other import values
            $exportFile = FormUtil::getPassedValue('exportFile', (isset($args['exportFile']) ? $args['exportFile'] : null), 'POST');
            $delimiter = FormUtil::getPassedValue('delimiter', (isset($args['delimiter']) ? $args['delimiter'] : null), 'POST');
            $email = FormUtil::getPassedValue('exportEmail', (isset($args['exportEmail']) ? $args['exportEmail'] : null), 'POST');
            $titles = FormUtil::getPassedValue('exportTitles', (isset($args['exportTitles']) ? $args['exportTitles'] : null), 'POST');
            $lastLogin = FormUtil::getPassedValue('exportLastLogin', (isset($args['exportLastLogin']) ? $args['exportLastLogin'] : null), 'POST');
            $regDate = FormUtil::getPassedValue('exportRegDate', (isset($args['exportRegDate']) ? $args['exportRegDate'] : null), 'POST');
            $groups = FormUtil::getPassedValue('exportGroups', (isset($args['exportGroups']) ? $args['exportGroups'] : null), 'POST');

            $email = (!isset($email) || $email !=='1') ? false : true;
            $titles = (!isset($titles) || $titles !== '1') ? false : true;
            $lastLogin = (!isset($lastLogin) || $lastLogin !=='1') ? false : true;
            $regDate = (!isset($regDate) || $regDate !== '1') ? false : true;
            $groups = (!isset($groups) || $groups !== '1') ? false : true;

            if (!isset($delimiter) || $delimiter == '') {
                $delimiter = 1;
            }
            switch ($delimiter) {
            case 1:
                $delimiter = ",";
                break;
            case 2:
                $delimiter = ";";
                break;
            case 3:
                $delimiter = ":";
                break;
            case 4:
                $delimiter = chr(9);
            }
            if (!isset($exportFile) || $exportFile == '') {
                $exportFile = 'users.csv';
            }
            if (!strrpos($exportFile, '.csv')) {
                $exportFile .= '.csv';
            }

            //export the csv file
            ModUtil::func('Users', 'admin', 'exportCSV',
                                   array(   'exportFile'=> $exportFile,
                                            'delimiter' => $delimiter,
                                            'email'     => $email,
                                            'titles'    => $titles,
                                            'lastLogin' => $lastLogin,
                                            'regDate'   => $regDate,
                                            'groups'    => $groups));
        }

        $pnRender = Renderer::getInstance('Users', false);
        if (SecurityUtil::checkPermission('Groups::', '::', ACCESS_READ)) {
            $pnRender->assign('groups', '1');
        }
        return $pnRender->fetch('users_admin_export.htm');
    }

    /**
     * This function does the actual export of the csv file.
     * the args array contains all information needed to export the csv file.
     * the options for the args array are:
     *  - exportFile (string)  Filename for the new csv file.
     *  - delimiter  (string)  The delimiter to use in the csv file.
     *  - email      (boolean) Flag, true to export emails.
     *  - titles     (boolean) Flag true to export a title row.
     *  - LastLogin  (boolean) Flag to export the users last login.
     *  - regDate    (boolean) Flag to export the users registration date.
     *
     * @param array $args all arguments sent to this function.
     *
     * @return displays download to user then exits.
     */
    public function exportCSV($args)
    {
        //make sure we have a delimiter
        if (!isset($args['delimiter']) || $args['delimiter'] == '') {
            $args['delimiter'] = ',';
        }
        //Security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)){
            return LogUtil::registerPermissionError();
        }

        //disable compression and set headers
        ob_end_clean();
        ini_set('zlib.output_compression', 0);
        header('Cache-Control: no-store, no-cache');
        header("Content-type: text/csv");
        header('Content-Disposition: attachment; filename="'.$args['exportFile'].'"');
        header("Content-Transfer-Encoding: binary");

        //get all user fields
        $userfields = ModUtil::apiFunc('Profile', 'user', 'getallactive');

        $colnames=array();
        foreach ($userfields as $item) {
          $colnames[] = $item['prop_attribute_name'];
        }

        //get all users
        $users = ModUtil::apiFunc('Users', 'user', 'getAll');

        //open a file for csv writing
        $out = fopen("php://output", 'w');

        //write out title row if asked for
        if ($args['titles']) {
            $titles = array('id','uname');
            //titles for optional data
            if ($args['email']) {
                array_push($titles, 'email');
            }
            if ($args['regDate']) {
                array_push($titles, 'user_regdate');
            }
            if ($args['lastLogin']) {
                array_push($titles, 'lastlogin');
            }
            if ($args['groups']) {
                array_push($titles, 'groups');
            }
            array_merge($titles, $colnames);
            fputcsv($out, $titles, $args['delimiter']);
        }

        //loop every user gettin user id and username and all user fields and push onto result array.
        foreach ($users as $user) {
            $uservars = UserUtil::getVars($user['uid']);
            $result = array();
            array_push($result,$uservars['uid'],$uservars['uname']);
            //checks for optional data
            if ($args['email']) {
                array_push($result,$uservars['email']);
            }
            if ($args['regDate']) {
                array_push($result, $uservars['user_regdate']);
            }
            if ($args['lastLogin']) {
                array_push($result, $uservars['lastlogin']);
            }
            if ($args['groups']) {
                $groups = ModUtil::apiFunc('Groups', 'user', 'getusergroups',
                   array(  'uid'   => $uservars['uid'],
                           'clean' => true));
                $groupstring = "";
                foreach ($groups as $group) {
                    $groupstring .= $group . chr(124);
                }
                $groupstring = rtrim($groupstring, chr(124));
                array_push($result,$groupstring);
            }
            foreach ($colnames as $colname) {
                array_push($result,$uservars['__ATTRIBUTES__'][$colname]);
            }
          //csv write the result array to the out file
          fputcsv($out, $result, $args['delimiter']);
        }
        //close the out file
        $length = filesize($out);
        fclose($out);
        // the users have been exported successfully
        LogUtil::registerStatus($this->__('Done! Users exported successfully.'));
        exit;
    }

    /**
     * Import several users from a CSV file. Checks needed values and format.
     *
     * Available Parameters:
     * - importFile (array) Structured information about the file to import, from <input type="file" name="fileFieldName" ... /> and stored in $_FILES['fileFieldName'].
     *                        See http://php.net/manual/en/features.file-upload.post-method.php .
     * - delimiter  (int)   A code indicating the type of delimiter found in the import file. 1 = comma, 2 = semicolon, 3 = colon.
     *
     * @param array $args All arguments passed to the function.
     *                    $args['importFile'] (array) Information about the file to import. Used as the default
     *                      if $_FILES['importFile'] is not set. Allows this function to be called internally,
     *                      rather than as a result of a form post.
     *                    $args['delimiter'] (int) A code indicating the delimiter used in the file. Used as the
     *                      default if $_POST['delimiter'] is not set. Allows this function to be called internally,
     *                      rather than as a result of a form post.
     *
     * @return a empty message if success or an error message otherwise
     */
    public function uploadImport($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // get import values
        $importFile = FormUtil::getPassedValue('importFile', (isset($args['importFile']) ? $args['importFile'] : null), 'FILES');
        $delimiter = FormUtil::getPassedValue('delimiter', (isset($args['delimiter']) ? $args['delimiter'] : null), 'POST');

        // get needed values
        $is_admin = (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) ? true : false;
        $minpass = ModUtil::getVar('Users', 'minpass');
        $defaultGroup = ModUtil::getVar('Groups', 'defaultgroup'); // Create output object;
        // calcs $pregcondition needed to verify illegal usernames
        $reg_illegalusername = ModUtil::getVar('Users', 'reg_Illegalusername');
        $pregcondition = '';
        if (!empty($reg_illegalusername)) {
            $usernames = explode(" ", $reg_illegalusername);
            $count = count($usernames);
            $pregcondition = "/((";
            for ($i = 0; $i < $count; $i++) {
                if ($i != $count-1) {
                    $pregcondition .= $usernames[$i] . ")|(";
                } else {
                    $pregcondition .= $usernames[$i] . "))/iAD";
                }
            }
        }

        // get available groups
        $allGroups = ModUtil::apiFunc('Groups','user','getall');

        // create an array with the groups identities where the user can add other users
        $allGroupsArray = array();
        foreach ($allGroups as $group) {
            if (SecurityUtil::checkPermission('Groups::', $group['gid'] . '::', ACCESS_EDIT)) {
                $allGroupsArray[] = $group['gid'];
            }
        }

        // check if the user's email must be unique
        $reg_uniemail = ModUtil::getVar('Users', 'reg_uniemail');

        // get the CSV delimiter
        switch ($delimiter) {
            case 1:
                $delimiterChar = ",";
                break;
            case 2:
                $delimiterChar = ";";
                break;
            case 3:
                $delimiterChar = ":";
                break;
        }

        // check that the user have selected a file
        $fileName = $importFile['name'];
        if ($fileName == '') {
            return $this->__("Error! You have not chosen any file.");
        }

        // check if user have selected a correct file
        if (FileUtil::getExtension($fileName) != 'csv') {
            return $this->__("Error! The file extension is incorrect. The only allowed extension is csv.");
        }

        // read the choosen file
        if (!$lines = file($importFile['tmp_name'])) {
            return $this->__("Error! It has not been possible to read the import file.");
        }
        $expectedFields = array('uname', 'pass', 'email', 'activated', 'sendMail', 'groups');
        $counter = 0;
        $importValues = array();
        // read the lines and create an array with the values. Check if the values passed are correct and set the default values if it is necessary
        foreach ($lines as $line_num => $line) {
            if ($counter == 0) {
                // check the fields defined in the first row
                $firstLineArray = DataUtil::formatForOS(explode($delimiterChar, trim($line)));
                foreach ($firstLineArray as $field) {
                    if (!in_array(trim($field), $expectedFields)) {
                        return $this->__("Error! The import file does not have the expected fields in the first row. Please check your import file.");
                    }
                }
                $counter++;
                continue;
            }
            // get and check the second and following lines
            $lineArray = array();
            $lineArray = DataUtil::formatForOS(explode($delimiterChar, str_replace('"','',$line)));

            // check if the line have all the needed values
            if (count($lineArray) != count($firstLineArray)) {
                return $this->__f('Error! The number of parameters in line %s is not correct. Please check your import file.', $counter);
            }
            $importValues[] = array_combine($firstLineArray, $lineArray);

            // check all the obtained values
            // check user name
            $uname = trim($importValues[$counter - 1]['uname']);
            if ($uname == '' || strlen($uname) > 25) {
                return $this->__f('Sorry! The user name is not valid in line %s. The user name is mandatory and the maximum length is 25 characters. Please check your import file.',
                    $counter);
            }

            // check if it is a valid user name
            // admins are allowed to add any usernames, even those defined as being illegal
            if (!$is_admin && $pregcondition != '') {
                // check for illegal usernames
                if (preg_match($pregcondition, $uname)) {
                    return $this->__f('Sorry! The user name %1$s is reserved and cannot be registered in line %2$s. Please check your import file.', array($uname, $counter));
                }
            }

            // check if the user name is valid because spaces or invalid characters
            if (preg_match("/[[:space:]]/", $uname) || !System::varValidate($uname, 'uname')) {
                return $this->__f('Sorry! The user name %1$s cannot contain spaces in line %2$s. Please check your import file.', array($uname, $counter));
            }

            // check if the user name is repeated
            if (in_array($uname, $usersArray)) {
                return $this->__f('Sorry! The user name %1$s is repeated in line %2$s, and it cannot be used twice for creating accounts. Please check your import file.',
                    array($uname, $counter));
            }
            $usersArray[] = $uname;

            // check password
            $pass = trim($importValues[$counter - 1]['pass']);
            if ($pass == '') {
                return $this->__f('Sorry! You did not provide a password in line %s. Please check your import file.', $counter);
            }

            // check password lenght
            if (strlen($pass) <  $minpass) {
                return $this->__f('Sorry! The password must be at least %1$s characters long in line %2$s. Please check your import file.', array($minpass, $counter));
            }

            // check email
            $email = trim($importValues[$counter - 1]['email']);
            if ($email == '') {
                return $this->__f('Sorry! You did not provide a email in line %s. Please check your import file.', $counter);
            }

            // check email format
            if (!System::varValidate($email, 'email')) {
                return $this->__f('Sorry! The e-mail address you entered was incorrectly formatted or is unacceptable for other reasons in line %s. Please check your import file.', $counter);
            }

            // check if email is unique only if it is necessary
            if ($reg_uniemail == 1) {
                if (in_array($email, $emailsArray)) {
                    return $this->__f('Sorry! The %1$s e-mail address is repeated in line %2$s, and it cannot be used twice for creating accounts. Please check your import file.',
                        array($email, $counter));
                }
                $emailsArray[] = $email;
            }

            $activated = trim($importValues[$counter - 1]['activated']);
            // check activation value and set 1 as default if it is not defined or it is incorrect
            if (!$activated || ($activated != UserUtil::ACTIVATED_INACTIVE &&
                $activated != UserUtil::ACTIVATED_ACTIVE &&
                $activated != UserUtil::ACTIVATED_INACTIVE_TOUPP &&
                $activated != UserUtil::ACTIVATED_INACTIVE_PWD &&
                $activated != UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP)) {
                    $importValues[$counter - 1]['activated'] = UserUtil::ACTIVATED_ACTIVE;
            }

            // check send mail and set 0 as default if it is not defined
            $importValues[$counter - 1]['sendMail'] = ($importValues[$counter - 1]['sendMail'] != 0 || $importValues[$counter - 1]['sendMail'] == '') ? 1 : 0;

            // check groups and set defaultGroup as default if there are not groups defined
            $groups = trim($importValues[$counter - 1]['groups']);
            if ($groups == '') {
                $importValues[$counter - 1]['groups'] = $defaultGroup;
            } else {
                $groupsArray = explode('|', $groups);
                foreach ($groupsArray as $group) {
                    if (!in_array($group, $allGroupsArray)) {
                        return $this->__f('Sorry! The identity of the group %1$s is not not valid in line %2$s. Perhaps it do not exist. Please check your import file.', array($group, $counter));
                    }
                }
            }
            $counter++;
        }

        // seams that the import file is formated correctly and its values are valid
        if (empty($importValues)) {
            return $this->__("Error! The import file does not have values.");
        }

        // check if users exists in database
        $usersInDB = ModUtil::apiFunc('Users', 'admin', 'checkMultipleExistence',
                                      array('valuesArray' => $usersArray,
                                            'key' => 'uname'));
        if ($usersInDB === false) {
            return $this->__("Error! Trying to read the existing user names in database.");
        } else {
            if (count($usersInDB) > 0) {
                return $this->__("Sorry! One or more user names really exist in database. The user names must be uniques.");
            }
        }

        // check if emails exists in data base in case the email have to be unique
        if ($reg_uniemail == 1) {
            $emailsInDB = ModUtil::apiFunc('Users', 'admin', 'checkMultipleExistence',
                                          array('valuesArray' => $emailsArray,
                                                'key' => 'email'));
            if ($emailsInDB === false) {
                return $this->__("Error! Trying to read the existing users' email addressess in database.");
            } else {
                if (count($emailsInDB) > 0) {
                    return $this->__("Sorry! One or more users' email addresses exist in the database. Each user's e-mail address must be unique.");
                }
            }
        }

        // seems that the values in import file are ready. Procceed creating users
        if (!ModUtil::apiFunc('Users', 'admin', 'createImport', array('importValues' => $importValues))) {
            return $this->__("Error! The creation of users has failed.");
        }

        return '';
    }
}
