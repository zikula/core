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
 * Get all users (for which the current user has permission to read).
 *
 * @param array $args All parameters passed to this function.
 *                    $args['letter']   (string) The first letter of the set of user names to return.
 *                    $args['starnum']  (int)    First item to return (optional).
 *                    $args['numitems'] (int)    Number if items to return (optional).
 *
 * @return array An array of users, or false on failure.
 */
function users_userapi_getall($args)
{
    // Optional arguments.
    $startnum = (isset($args['startnum']) && is_numeric($args['startnum'])) ? $args['startnum'] : 1;
    $numitems = (isset($args['numitems']) && is_numeric($args['numitems'])) ? $args['numitems'] : -1;

    // Security check
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW)) {
        return false;
    }

    $permFilter = array();
    // corresponding filter permission to filter anonymous in admin view:
    // Administrators | Users:: | Anonymous:: | None
    $permFilter[] = array('realm' => 0,
                      'component_left'   => 'Users',
                      'component_middle' => '',
                      'component_right'  => '',
                      'instance_left'    => 'uname',
                      'instance_middle'  => '',
                      'instance_right'   => 'uid',
                      'level'            => ACCESS_READ);

    // form where clause
    $where = '';
    if (isset($args['letter'])) {
        $where = "WHERE pn_uname LIKE '".DataUtil::formatForStore($args['letter'])."%'";
    }

    $objArray = DBUtil::selectObjectArray('users', $where, 'uname', $startnum-1, $numitems, '', $permFilter);

    // Check for a DB error
    if ($objArray === false) {
        return LogUtil::registerError(__('Error! Could not load data.'));
    }

    return $objArray;
}

/**
 * Get a specific user record.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['uid']   (numeric) The id of user to get (required, unless uname specified).
 *                    $args['uname'] (string)  The user name of user to get (ignored if uid is specified, otherwise required).
 *
 * @return array The user record as an array, or false on failure.
 */
function users_userapi_get($args)
{
    // Argument check
    if (!isset($args['uid']) || !is_numeric($args['uid'])) {
        if (!isset($args['uname'])) {
            return LogUtil::registerArgsError();
        }
    }

    $pntable = pnDBGetTables();
    $userscolumn = $pntable['users_column'];

    // calculate the where statement
    if (isset($args['uid'])) {
        $where = "$userscolumn[uid]='" . DataUtil::formatForStore($args['uid']) . "'";
    } else {
        $where = "$userscolumn[uname]='" . DataUtil::formatForStore($args['uname']) . "'";
    }

    $obj = DBUtil::selectObject('users', $where);

    // Security check
    if ($obj && !SecurityUtil::checkPermission('Users::', "$obj[uname]::$obj[uid]", ACCESS_READ)) {
        return false;
    }

    // Return the item array
    return $obj;
}

/**
 * Count and return the number of users.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['letter'] (string) If specified, then only those user records whose user name begins with the specified letter are counted.
 *
 * @todo Shouldn't there be some sort of limit on the select/loop??
 *
 * @return int Number of users.
 */
function users_userapi_countitems($args)
{
    // form where clause
    $where = '';
    if (isset($args['letter'])) {
        $where = "WHERE pn_uname LIKE '".DataUtil::formatForStore($args['letter'])."%'";
    }

    return DBUtil::selectObjectCount('users', $where);
}

/**
 * Get user properties.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['proplabel'] (string) If specified only the value of the specified property (label) is returned.
 *
 * @return array An array of user properties, or false on failure.
 */
function users_userapi_optionalitems($args)
{
    $items = array();

    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        return $items;
    }

    if (!ModUtil::available('Profile') || !ModUtil::dbInfoLoad('Profile')) {
        return false;
    }

    $pntable = pnDBGetTables();
    $propertycolumn = $pntable['user_property_column'];

    $extrawhere = '';
    if (isset($args['proplabel']) && !empty($args['proplabel'])) {
        $extrawhere = "AND $propertycolumn[prop_label] = '".DataUtil::formatForStore($args['proplabel'])."'";
    }

    $where = "WHERE  $propertycolumn[prop_weight] != 0
              AND    $propertycolumn[prop_dtype] != '-1' $extrawhere";

    $orderby = "ORDER BY $propertycolumn[prop_weight]";

    $objArray = DBUtil::selectObjectArray('user_property', $where, $orderby);

    if ($objArray === false) {
        LogUtil::registerError(__('Error! Could not load data.'));
        return $objArray;
    }

    $ak = array_keys($objArray);
    foreach ($ak as $v) {
        $prop_validation = @unserialize($objArray[$v]['prop_validation']);
        $prop_array = array('prop_viewby'      => $prop_validation['viewby'],
                            'prop_displaytype' => $prop_validation['displaytype'],
                            'prop_listoptions' => $prop_validation['listoptions'],
                            'prop_note'        => $prop_validation['note'],
                            'prop_validation'  => $prop_validation['validation']);

        array_push($objArray[$v], $prop_array);
    }

    return $objArray;
}

/**
 * Validate new user information entered by the user.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['uname']        (string) The proposed user name for the new user record.
 *                    $args['email']        (string) The proposed e-mail address for the new user record.
 *                    $args['agreetoterms'] (int)    A flag indicating that the user has agreed to the site's terms and policies; 0 indicates no, otherwise yes.
 *
 * @return array An array containing an error code and a result message. Possible error codes are:
 *               -1=NoPermission 1=EverythingOK 2=NotaValidatedEmailAddr
 *               3=NotAgreeToTerms 4=InValidatedUserName 5=UserNameTooLong
 *               6=UserNameReserved 7=UserNameIncludeSpace 8=UserNameTaken
 *               9=EmailTaken 11=User Agent Banned 12=Email Domain banned
 *
 */
function users_userapi_checkuser($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        return -1;
    }

    if (!pnVarValidate($args['email'], 'email')) {
        return 2;
    }

    if (ModUtil::available('legal')) {
        if ($args['agreetoterms'] == 0) {
            return 3;
        }
    }

    if ((!$args['uname']) || !(!preg_match("/[[:space:]]/", $args['uname'])) || !pnVarValidate($args['uname'], 'uname')) {
        return 4;
    }

    if (strlen($args['uname']) > 25) {
        return 5;
    }

    // admins are allowed to add any usernames, even those defined as being illegal
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
        // check for illegal usernames
        $reg_illegalusername = ModUtil::getVar('Users', 'reg_Illegalusername');
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
            if (preg_match($pregcondition, $args['uname'])) {
                return 6;
            }
        }
    }

    if (strrpos($args['uname'], ' ') > 0) {
        return 7;
    }

    // check existing and active user
    $ucount = DBUtil::selectObjectCountByID('users', $args['uname'], 'uname', 'lower');
    if ($ucount) {
        return 8;
    }

    // check pending user
    $ucount = DBUtil::selectObjectCountByID('users_temp', $args['uname'], 'uname', 'lower');
    if ($ucount) {
        return 8;
    }

    if (ModUtil::getVar('Users', 'reg_uniemail')) {
        $ucount = DBUtil::selectObjectCountByID('users', $args['email'], 'email');
        if ($ucount) {
            return 9;
        }
    }

    if (ModUtil::getVar('Users', 'moderation')) {
        $ucount = DBUtil::selectObjectCountByID('users_temp', $args['uname'], 'uname');
        if ($ucount) {
            return 8;
        }

        $ucount = DBUtil::selectObjectCountByID('users_temp', $args['email'], 'email');
        if (ModUtil::getVar('Users', 'reg_uniemail')) {
            if ($ucount) {
                return 9;
            }
        }
    }

    $useragent = strtolower(pnServerGetVar('HTTP_USER_AGENT'));
    $illegaluseragents = ModUtil::getVar('Users', 'reg_Illegaluseragents');
    if (!empty($illegaluseragents)) {
        $disallowed_useragents = str_replace(', ', ',', $illegaluseragents);
        $checkdisallowed_useragents = explode(',', $disallowed_useragents);
        $count = count($checkdisallowed_useragents);
        $pregcondition = "/((";
        for ($i = 0; $i < $count; $i++) {
            if ($i != $count-1) {
                $pregcondition .= $checkdisallowed_useragents[$i] . ")|(";
            } else {
                $pregcondition .= $checkdisallowed_useragents[$i] . "))/iAD";
            }
        }
        if (preg_match($pregcondition, $useragent)) {
            return 11;
        }
    }

    $illegaldomains = ModUtil::getVar('Users', 'reg_Illegaldomains');
    if (!empty($illegaldomains)) {
        list($foo, $maildomain) = explode('@', $args['email']);
        $maildomain = strtolower($maildomain);
        $disallowed_domains = str_replace(', ', ',', $illegaldomains);
        $checkdisallowed_domains = explode(',', $disallowed_domains);
        if (in_array($maildomain, $checkdisallowed_domains)) {
            return 12;
        }
    }

    return 1;
}

/**
 * Complete the process of creating a new user or new user registration from a registration request form.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['isadmin']           (bool)   Whether the new user record is being submitted by a user with admin permissions or not.
 *                    $args['user_regdate']      (string) An SQL date-time to override the registration date and time.
 *                    $args['user_viewmail']     (int)    Whether the user has selected to allows his e-mail address to be viewed or not.
 *                    $args['storynum']          (int)    The number of News module stories to show on the main page.
 *                    $args['commentlimit']      (int)    The limit on the size of this user's comments.
 *                    $args['timezoneoffset']    (int)    The user's time zone offset.
 *                    $args['usermustconfirm']   (int)    Whether the user must activate his account or not.
 *                    $args['skipnotifications'] (bool)   Whether e-mail notifications should be skipped or not.
 *                    $args['moderated']         (bool)   If true, then this record is being added as a result of an admin approval of a pending registration.
 *                    $args['hash_method']       (int)    A code indicated what hash method was used to store the user's encrypted password in the users_temp table.
 *                    $args['uname']             (string) The user name to store on the new user record.
 *                    $args['email']             (string) The e-mail address to store on the new user record.
 *                    $args['pass']              (string) The new password to store on the new user record.
 *                    $args['dynadata']          (array)  An array of data to be stored by the designated profile module and associated with this user record.
 *
 * @return bool True on success, otherwise false.
 */
function users_userapi_finishnewuser($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        return false;
    }

    // arguments defaults
    if (!isset($args['isadmin'])) {
        $args['isadmin'] = false;
    }
    if (!isset($args['user_regdate'])) {
        $args['user_regdate'] = DateUtil::getDatetime();
    }
    if (!isset($args['user_viewemail'])) {
        $args['user_viewemail'] = '0';
    }
    if (!isset($args['storynum'])) {
        $args['storynum'] = '5';
    }
    if (!isset($args['commentlimit'])) {
        $args['commentlimit'] = '4096';
    }
    if (!isset($args['timezoneoffset'])) {
        $args['timezoneoffset'] = System::getVar('timezone_offset');
    }
    if (!isset($args['usermustconfirm'])) {
        $args['usermustconfirm'] = 0;
    }
    // allows to run without email notifications
    if (!isset($args['skipnotifications'])) {
        $args['skipnotifications'] = false;
    }

    // hash methods array
    $hashmethodsarray = ModUtil::apiFunc('Users', 'user', 'gethashmethods');

    // make password
    $hash_method = ModUtil::getVar('Users', 'hash_method');
    $hashmethod = $hashmethodsarray[$hash_method];

    if (isset($args['moderated']) && $args['moderated'] == true) {
        $makepass  = $args['pass'];
        $cryptpass = $args['pass'];
        $hashmethod = $args['hash_method'];
        $activated = 1;
    } else {
        if (ModUtil::getVar('Users', 'reg_verifyemail') == 1 && !$args['isadmin']) {
            $makepass = _users_userapi_makePass();
            $cryptpass = hash($hash_method, $makepass);
            $activated = 1;
        } elseif (ModUtil::getVar('Users', 'reg_verifyemail') == 2) {
            $makepass = $args['pass'];
            $cryptpass = hash($hash_method, $args['pass']);
            $activated = ($args['isadmin'] && isset($args['usermustconfirm']) && $args['usermustconfirm'] != 1) ? 1 : 0;
        } else {
            $makepass = $args['pass']; // for welcome email. [class007]
            $cryptpass = hash($hash_method, $args['pass']);
            $activated = 1;
        }
    }

    if (isset($args['moderated']) && $args['moderated']) {
        $moderation = false;
    } elseif (!$args['isadmin']) {
        $moderation = ModUtil::getVar('Users', 'moderation');
        $args['moderated'] = false;
    } else {
        $moderation = false;
    }

    $pntable = pnDBGetTables();

    // We keep dynata as is if moderation is on as all dynadata will go in one field
    if ($moderation) {
        $column     = $pntable['users_temp_column'];
        $columnid   = $column['tid'];
    } else {
        $column     = $pntable['users_column'];
        $columnid   = $column['uid'];
    }

    $sitename  = System::getVar('sitename');
    $siteurl   = System::getBaseUrl();

    // create output object
    $pnRender = Renderer::getInstance('Users', false);
    $pnRender->assign('sitename', $sitename);
    $pnRender->assign('siteurl', substr($siteurl, 0, strlen($siteurl)-1));

    $obj = array();
    // do moderation stuff and exit
    if ($moderation) {
        $dynadata = isset($args['dynadata']) ? $args['dynadata'] : FormUtil::getPassedValue('dynadata', array());

        $obj['uname']        = $args['uname'];
        $obj['email']        = $args['email'];
        $obj['pass']         = $cryptpass;
        $obj['dynamics']     = @serialize($dynadata);
        $obj['comment']      = ''; //$args['comment'];
        $obj['type']         = 1;
        $obj['tag']          = 0;
        $obj['hash_method']  = $hashmethod;

        $obj = DBUtil::insertObject($obj, 'users_temp', 'tid');

        if (!$obj) {
            return false;
        }
        if (!$args['skipnotifications']) {
            $pnRender->assign('email', $args['email']);
            $pnRender->assign('uname', $args['uname']);
            //$pnRender->assign('uid', $args['uid']);
            $pnRender->assign('makepass', $makepass);
            $pnRender->assign('moderation', $moderation);
            $pnRender->assign('moderated', $args['moderated']);

            // Password Email - Must be send now as the password will be encrypted and unretrievable later on.
            $message = $pnRender->fetch('users_userapi_welcomeemail.htm');

            $subject = __f('Password for %1$s from %2$s', array($args['uname'], $sitename));
            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('toaddress' => $args['email'], 'subject' => $subject, 'body' => $message, 'html' => true));

            // mail notify email to inform admin about registration
            if (ModUtil::getVar('Users', 'reg_notifyemail') != '' && $moderation == 1) {
                $email2 = ModUtil::getVar('Users', 'reg_notifyemail');
                $subject2 = __('New user account registered');
                $message2 = $pnRender->fetch('users_userapi_adminnotificationmail.htm');
                ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('toaddress' => $email2, 'subject' => $subject2, 'body' => $message2, 'html' => true));
            }
        }
        return $obj['tid'];
    }

    $obj['uname']           = $args['uname'];
    $obj['email']           = $args['email'];
    $obj['user_regdate']    = $args['user_regdate'];
    $obj['user_viewemail']  = $args['user_viewemail'];
    $obj['user_theme']      = '';
    $obj['pass']            = $cryptpass;
    $obj['storynum']        = $args['storynum'];
    $obj['ublockon']        = 0;
    $obj['ublock']          = '';
    $obj['theme']           = '';
    $obj['counter']         = 0;
    $obj['activated']       = $activated;
    $obj['hash_method']     = $hashmethod;

    $profileModule = System::getVar('profilemodule', '');
    $useProfileModule = (!empty($profileModule) && ModUtil::available($profileModule));

    // call the profile manager to handle dyndata if needed
    if ($useProfileModule) {
        $adddata = ModUtil::apiFunc($profileModule, 'user', 'insertdyndata', $args);
        if (is_array($adddata)) {
            $obj = array_merge($adddata, $obj);
        }
    }

    $res = DBUtil::insertObject($obj, 'users', 'uid');

    if (!$res) {
        return false;
    }

    $uid = $obj['uid'];

    // Add user to group
    // TODO - move this to a groups API calls
    $gid = ModUtil::getVar('Groups', 'defaultgroup');
    $group = DBUtil::selectObjectByID('groups', $gid, 'gid');
    if (!$group) {
        return false;
    }

    $obj = array();
    $obj['gid'] = $group['gid'];
    $obj['uid'] = $uid;
    $res = DBUtil::insertObject($obj, 'group_membership', 'dummy');
    if (!$res) {
        return false;
    }

    if (!$args['skipnotifications']) {
        $from = System::getVar('adminmail');

        // begin mail user
        $pnRender->assign('email', $args['email']);
        $pnRender->assign('uname', $args['uname']);
        $pnRender->assign('uid', $uid);
        $pnRender->assign('makepass', $makepass);
        $pnRender->assign('moderated', $args['moderated']);
        $pnRender->assign('moderation', $moderation);
        $pnRender->assign('user_regdate', $args['user_regdate']);

        if ($activated == 1) {
            // Password Email & Welcome Email
            $message = $pnRender->fetch('users_userapi_welcomeemail.htm');
            $subject = __f('Password for %1$s from %2$s', array($args['uname'], $sitename));
            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('toaddress' => $args['email'], 'subject' => $subject, 'body' => $message, 'html' => true));

        } else {
            // Activation Email
            $subject = __f('Activation of %s', $args['uname']);
            // add en encoded activation code. The string is split with a hash (this character isn't used by base 64 encoding)
            $pnRender->assign('code', base64_encode($uid . '#' . $args['user_regdate']));
            $message = $pnRender->fetch('users_userapi_activationemail.htm');
            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('toaddress' => $args['email'], 'subject' => $subject, 'body' => $message, 'html' => true));
        }

        // mail notify email to inform admin about activation
        if (ModUtil::getVar('Users', 'reg_notifyemail') != '') {
            $email2 = ModUtil::getVar('Users', 'reg_notifyemail');
            $subject2 = __('New user account activated');
            $message2 = $pnRender->fetch('users_userapi_adminnotificationemail.htm');
            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('toaddress' => $email2, 'subject' => $subject2, 'body' => $message2, 'html' => true));
        }
    }
    // Let other modules know we have created an item
    ModUtil::callHooks('item', 'create', $uid, array('module' => 'Users'));

    return $uid;
}

/**
 * Send the user a lost password.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['uname'] (string) The user's user name.
 *                    $args['email'] (string) The user's e-mail address.
 *                    $args['code']  (string) The confirmation code.
 *
 * @return int An error code: 0=DatabaseError 1=WrongCode 2=NoSuchUsernameOrEmailAddress 3=PasswordMailed 4=ConfirmationCodeMailed
 */
function users_userapi_mailpasswd($args)
{
    $pntable = pnDBGetTables();

    $column = $pntable['users_column'];
    $where  = '';
    if (!empty($args['email'])) {
        $where = "$column[email] = '" . DataUtil::formatForStore($args['email']) . "'";

    } elseif (!empty($args['uname'])) {
        $where = "$column[uname] = '" . DataUtil::formatForStore($args['uname']) . "'";
    }
    $user  = DBUtil::selectObject('users', $where);
    if (!$user) {
        return 2;
    }

    $pnRender = Renderer::getInstance('Users', false);
    $pnRender->assign('uname', $user['uname']);
    $pnRender->assign('sitename', $sitename);
    $pnRender->assign('hostname', pnServerGetVar('REMOTE_ADDR'));

    $areyou = substr($user['pass'], 0, 5);

    if (!$args['code']) {
        $pnRender->assign('code', $areyou);
        $pnRender->assign('url',  ModUtil::url('Users', 'user', 'lostpassword', array(), null, null, true));
        $message = $pnRender->fetch('users_userapi_lostpasscodemail.htm');
        $subject = __f('Confirmation code for %s', $user['uname']);
        ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                     array('toaddress' => $user['email'],
                           'subject'   => $subject,
                           'body'      => $message,
                           'html'      => true));
        return 4;
    }

    if ($areyou == $args['code']) {
        $pnRender->assign('password', $newpass = _users_userapi_makePass());
        $pnRender->assign('url',      ModUtil::url('Users', 'user', 'loginscreen', array(), null, null, true));
        $message = $pnRender->fetch('users_userapi_passwordmail.htm');
        $subject = __f('Password for %s', $user['uname']);
        ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                     array('toaddress' => $user['email'],
                           'subject'   => $subject,
                           'body'      => $message,
                           'html'      => true));

        // Next step: add the new password to the database
        $hash_method = ModUtil::getVar('Users', 'hash_method');
        $hashmethodsarray = ModUtil::apiFunc('Users', 'user', 'gethashmethods');
        $cryptpass = hash($hash_method, $newpass);
        $obj = array();
        $obj['uname'] = $user['uname'];
        $obj['pass']  = $cryptpass;
        $obj['hash_method'] = $hashmethodsarray[$hash_method];
        $res = DBUtil::updateObject ($obj, 'users', '', 'uname');
        return ($res ? 3 : 0);
    }

    return 1;
}

/**
 * Activate a user's account.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['regdate'] (string)  An SQL date-time containing the user's original registration date-time.
 *                    $args['uid']     (numeric) The id of the user account to activate.
 *
 * @return bool True on success, otherwise false.
 */
function users_userapi_activateuser($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        return false;
    }

    // Preventing reactivation from same link !
    $newregdate = DateUtil::getDatetime(strtotime($args['regdate'])+1);
    $obj = array('uid'          => $args['uid'],
                 'activated'    => '1',
                 'user_regdate' => DataUtil::formatForStore($newregdate));

    ModUtil::callHooks('item', 'update', $args['uid'], array('module' => 'Users'));

    return (boolean)DBUtil::updateObject($obj, 'users', '', 'uid');
}

/**
 * Display a message indicating that the user's session has expired.
 *
 * @return string The rendered template.
 */
function users_userapi_expiredsession()
{
    $pnRender = Renderer::getInstance('Users', false);
    return $pnRender->fetch('users_userapi_expiredsession.htm');
}

/**
 * Generate a password for the user.
 *
 * @return string The generated password.
 */
function _users_userapi_makePass()
{
    $minpass = (int)ModUtil::getVar('Users', 'minpass', 5);
    return RandomUtil::getString($minpass, 8, false, false, true, false, true, false, true, array('0', 'o', 'l', '1'));
}

/**
 * Retrieve an array of hash method codes indexed by hash method name, or an array of hash method names indexed by hash method codes.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['reverse'] (bool) If false, then an array of codes index by name; if true, then an array of names indexed by code.
 *
 * @return array The array of hash method codes and names.
 */
function users_userapi_gethashmethods($args)
{
    $reverse = isset($args['reverse']) ? $args['reverse'] : false;

    if ($reverse) {

        return array(1 => 'md5',
                     5 => 'sha1',
                     8 => 'sha256');
    } else {

        return array('md5'    => 1,
                     'sha1'   => 5,
                     'sha256' => 8);
    }
}

/**
 * Retrieve the account links for each user module.
 *
 * @return array An array of links for the user account page.
 */
function Users_userapi_accountlinks()
{
    // Get all user modules
    $mods = pnModGetAllMods();

    if ($mods == false) {
        return false;
    }

    $accountlinks = array();

    foreach ($mods as $mod) {
        // saves 17 system checks
        if ($mod['type'] == 3 && !in_array($mod['name'], array('Admin', 'Categories', 'Groups', 'Theme', 'Users'))) {
            continue;
        }

        $modpath = ($mod['type'] == 3) ? 'system' : 'modules';

        if (file_exists("$modpath/".DataUtil::formatForOS($mod['directory']).'/pnaccountapi.php')) {
            $items = ModUtil::apiFunc($mod['name'], 'account', 'getall');
            if ($items) {
                foreach ($items as $k => $item) {
                    // check every retured link for permissions
                    if (SecurityUtil::checkPermission('Users::', "$mod[name]::$item[title]", ACCESS_READ)) {
                        if (!isset($item['module'])) {
                            $item['module']  = $mod['name'];
                        }
                        // insert the indexed item
                        $accountlinks["$mod[name]{$k}"] = $item;
                    }
                }
            }
        } else {
            $items = false;
        }
    }

    return $accountlinks;
}

/**
 * Save the preliminary user e-mail until user's confirmation.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['newemail'] (string) The new e-mail address to store pending confirmation.
 *
 * @return bool True if success and false otherwise.
 */
function Users_userapi_savepreemail($args)
{
    if (!UserUtil::isLoggedIn()) {
        return LogUtil::registerPermissionError();
    }

    $pntable = pnDBGetTables();
    $column = $pntable['users_temp_column'];

    // delete all the records from e-mail confirmation that have more than five days
    $fiveDaysAgo =  time() - 5*24*60*60;
    $where = "$column[dynamics]<" . $fiveDaysAgo . " AND $column[type]=2";
    DBUtil::deleteWhere ('users_temp', $where);

    $uname = UserUtil::getVar('uname');

    // generate a randomize value of 7 characters needed to confirm the e-mail change
    $confirmValue = substr(md5(time() . rand(0, 30000)),0 ,7);;

    $obj = array('uname' => $uname,
                 'email' => DataUtil::formatForStore($args['newemail']),
                 'pass' => '',
                 'dynamics' => time(),
                 'comment' => $confirmValue,
                 'type' => 2,
                 'tag' => 0);

    // checks if user has request the change recently and it is not confirmed
    $exists = DBUtil::selectObjectCountByID('users_temp', $uname, 'uname', 'lower');

    if (!$exists) {
        // create a new insert
        $obj = DBUtil::insertObject($obj, 'users_temp', 'tid');
    } else {
        $where = "$column[uname]='" . $uname . "' AND $column[type]=2";
        // update the current insert
        $obj = DBUtil::updateObject($obj, 'users_temp', $where);
    }

    if (!$obj) {
        return false;
    }

    // send confirmation e-mail to user with the changing code
    $subject = __f('Confirmation change of e-mail for %s', $uname);

    $pnRender = Renderer::getInstance('Users', false);
    $pnRender->assign('uname', $uname);
    $pnRender->assign('email', UserUtil::getVar('email'));
    $pnRender->assign('newemail', $args['newemail']);
    $pnRender->assign('sitename', System::getVar('sitename'));
    $pnRender->assign('url',  ModUtil::url('Users', 'user', 'confirmchemail', array('confirmcode' => $confirmValue), null, null, true));

    $message = $pnRender->fetch('users_userapi_confirmchemail.htm');
    $sent = ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('toaddress' => $args['newemail'], 'subject' => $subject, 'body' => $message, 'html' => true));

    if (!$sent) {
        return false;
    }

    return true;
}

/**
 * Retrieve the user's new e-mail address that is awaiting his confirmation.
 *
 * @return string The e-mail address waiting for confirmation for the current user.
 */
function users_userapi_getuserpreemail()
{
    if (!UserUtil::isLoggedIn()) {
        return LogUtil::registerPermissionError();
    }
    $item = DBUtil::selectObjectById('users_temp', UserUtil::getVar('uname'), 'uname');
    if (!$item) {
        return false;
    }
    return $item;
}

/**
 * Get available user menu links.
 *
 * @return array An array of menu links.
 */
function Users_userapi_getlinks()
{

    $allowregistration = ModUtil::getVar('Users', 'reg_allowreg');

    $links = array();

    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        $links[] = array('url' => ModUtil::url('Users', 'user', 'loginscreen'), 'text' => __('Log in'), 'class' => 'z-icon-es-user');
        $links[] = array('url' => ModUtil::url('Users', 'user', 'lostpassword'), 'text' => __('Lost password'), 'class' => 'z-icon-es-password');
    }

    if ($allowregistration) {
        $links[] = array('url' => ModUtil::url('Users', 'user', 'register'), 'text' => __('New account'), 'class' => 'z-icon-es-adduser');
    }

    return $links;
}