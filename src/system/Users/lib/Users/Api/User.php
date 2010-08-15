<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * The system-level and database-level functions for user-initiated actions for the Users module.
 */
class Users_Api_User extends Zikula_Api
{
    /**
     * Get all users (for which the current user has permission to read).
     *
     * @param array $args All parameters passed to this function.
     *                      string  $args['letter']   The first letter of the set of user names to return.
     *                      integer $args['starnum']  First item to return (optional).
     *                      integer $args['numitems'] Number if items to return (optional).
     *                      array   $args['sort']     The field(s) on which to sort the result (optional).
     *
     * @return array An array of users, or false on failure.
     */
    public function getAll($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW)) {
            return false;
        }

        // Check validity of startnum arg, or set default
        if (!isset($args['startnum'])) {
            $limitOffset = -1;
        } else {
            if (is_numeric($args['startnum']) && ((int)$args['startnum'] == $args['startnum'])) {
                $limitOffset = (int)$args['startnum'] - 1;
            } else {
                return LogUtil::registerArgsError();
            }
        }

        // Check validity of numitems arg, or set default
        if (!isset($args['numitems'])) {
            $limitNumRows = -1;
        } else {
            if (is_numeric($args['numitems']) && ((int)$args['numitems'] == $args['numitems']) && ($args['numitems'] >= 1)) {
                $limitNumRows = (int)$args['numitems'];
            } else {
                return LogUtil::registerArgsError();
            }
        }

        // Check validity of letter arg.
        // $args['letter'] is really an SQL LIKE filter
        if (isset($args['letter']) && (empty($args['letter']) || !is_string($args['letter']) || strstr($args['letter'], '%'))) {
            return LogUtil::registerArgsError();
        }

        // Sort
        $table = DBUtil::getTables();
        $usersColumn = $table['users_column'];
        if (isset($args['sort']) && !empty($args['sort'])) {
            if (is_string($args['sort'])) {
                $sortBy = array($args['sort']);
            } elseif (is_array($args['sort'])) {
                $sortBy = $args['sort'];
            } else {
                return LogUtil::registerArgsError();
            }

            $orderBy = array();
            foreach ($sortBy as $key => $value) {
                if (is_numeric($key)) {
                    $fieldName = $value;
                    $direction = '';
                } else {
                    $fieldName = $key;
                    $direction = $value;
                }
                if (!empty($direction) && ($direction != 'ASC') && ($direction != 'DESC')) {
                    return LogUtil::registerArgsError();
                } elseif (isset($usersColumn[$fieldName])) {
                    $orderBy[] = $usersColumn[$fieldName] . (!empty($direction) ? ' ' . $direction : '');
                } else {
                    return LogUtil::registerArgsError();
                }
            }

            $orderBy = 'ORDER BY ' . implode(', ', $orderBy);
        } else {
            $orderBy = 'ORDER BY uname';
        }

        $permFilter = array();
        // corresponding filter permission to filter anonymous in admin view:
        // Administrators | Users:: | Anonymous:: | None
        $permFilter[] = array(
            'realm'             => 0,
            'component_left'    => 'Users',
            'component_middle'  => '',
            'component_right'   => '',
            'instance_left'     => 'uname',
            'instance_middle'   => '',
            'instance_right'    => 'uid',
            'level'             => ACCESS_READ
        );

        // form where clause
        $where = array();
        if (isset($args['letter'])) {
            $where[] = "({$usersColumn['uname']} LIKE '".DataUtil::formatForStore($args['letter'])."%')";
        }
        $where[] = "({$usersColumn['activated']} NOT IN (" . implode(', ', array(UserUtil::ACTIVATED_PENDING_REG, UserUtil::ACTIVATED_PENDING_DELETE)) . '))';
        $where = 'WHERE ' . implode(' AND ', $where);

        $objArray = DBUtil::selectObjectArray('users', $where, $orderBy, $limitOffset, $limitNumRows, null, $permFilter);

        // Check for a DB error
        if ($objArray === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
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
    public function get($args)
    {
        // Argument check
        if (isset($args['uid'])) {
            if (!is_numeric($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
                return LogUtil::registerArgsError();
            } else {
                $key = (int)$args['uid'];
                $field = 'uid';
            }
        } elseif (!isset($args['uname']) || !is_string($args['uname'])) {
            return LogUtil::registerArgsError();
        } else {
            $key = $args['uname'];
            $field = 'uname';
        }

        $obj = UserUtil::getVars($key, false, $field);

        // Check for a DB error
        if ($obj === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
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
    public function countItems($args)
    {
        // Check validity of letter arg.
        // $args['letter'] is really an SQL LIKE filter
        if (isset($args['letter']) && (empty($args['letter']) || !is_string($args['letter']) || strstr($args['letter'], '%'))) {
            return LogUtil::registerArgsError();
        }

        $table = DBUtil::getTables();
        $usersColumn = $table['users_column'];

        // form where clause
        $where = '';
        if (isset($args['letter'])) {
            $where = "WHERE {$usersColumn['uname']} LIKE '".DataUtil::formatForStore($args['letter'])."%'";
        }

        $objCount = DBUtil::selectObjectCount('users', $where);

        // Check for a DB error
        if ($objCount === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        return $objCount;
    }

    /**
     * Get user properties.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['proplabel'] (string) If specified only the value of the specified property (label) is returned.
     *
     * @return array An array of user properties, or false on failure.
     */
    public function optionalItems($args)
    {
        $items = array();

        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return $items;
        }

        if (!ModUtil::available('Profile') || !ModUtil::dbInfoLoad('Profile')) {
            return false;
        }

        $dbtable = DBUtil::getTables();
        $propertycolumn = $dbtable['user_property_column'];

        $extrawhere = '';
        if (isset($args['proplabel']) && !empty($args['proplabel'])) {
            $extrawhere = "AND $propertycolumn[prop_label] = '".DataUtil::formatForStore($args['proplabel'])."'";
        }

        $where = "WHERE  $propertycolumn[prop_weight] != 0
                  AND    $propertycolumn[prop_dtype] != '-1' $extrawhere";

        $orderby = "ORDER BY $propertycolumn[prop_weight]";

        $objArray = DBUtil::selectObjectArray('user_property', $where, $orderby);

        if ($objArray === false) {
            LogUtil::registerError($this->__('Error! Could not load data.'));
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
     * Sends a notification e-mail of a specified type to a user or registrant.
     *
     * @param array $args All parameters passed to this function.
     *                      string  toAddress           The destination e-mail address.
     *                      string  notificationType    The type of notification, converted to the name of a template
     *                                                      in the form users_userapi_{type}mail.tpl and/or .txt
     *                      array   templateArgs        One or more arguments to pass to the renderer for use in the
     *                                                      template.
     *                      string  subject             The e-mail subject, overriding the template's subject.
     *
     * @return <type>
     */
    public function sendNotification($args)
    {
        $toAddress = $args['toAddress'];
        $notificationType = $args['notificationType'];
        $templateArgs = $args['templateArgs'];

        $renderer = Zikula_View::getInstance('Users', false);

        $mailerArgs = array();
        $mailerArgs['toaddress'] = $toAddress;

        $renderer->assign($templateArgs);

        $templateName = "users_email_{$notificationType}_html.tpl";
        if ($renderer->template_exists($templateName)) {
            $mailerArgs['html'] = true;
            $mailerArgs['body'] = $renderer->fetch($templateName);
            $subject = trim($renderer->get_template_vars('subject'));
        }

        $templateName = "users_email_{$notificationType}_txt.tpl";
        if ($renderer->template_exists($templateName)) {
            if (isset($mailerArgs['body'])) {
                $bodyType = 'altbody';
                unset($mailerArgs['html']);
            } else {
                $bodyType = 'body';
                $mailerArgs['html'] = false;
            }
            $mailerArgs[$bodyType] = $renderer->fetch($templateName);
            if (!isset($subject) || empty($subject)) {
                // Favor the subject set in the html template over this one.
                $subject = trim($renderer->get_template_vars('subject'));
            }
        }

        if (isset($subject) && !empty($subject)) {
            $mailerArgs['subject'] = $subject;
        } elseif (isset($args['subject']) && !empty($args['subject'])) {
            $mailerArgs['subject'] = $args['subject'];
        } else {
            switch ($notificationType) {
                case 'activation':
                    $mailerArgs['subject'] = $this->__('Verify your account.');
                    break;
                case 'regadminnotify':
                    $mailerArgs['subject'] = $this->__('New user or registration.');
                    break;
                case 'confirmchemail':
                    $mailerArgs['subject'] = $this->__('Verify your new e-mail address.');
                    break;
                case 'lostpasscode':
                    $mailerArgs['subject'] = $this->__('Recover your password.');
                    break;
                case 'lostuname':
                    $mailerArgs['subject'] = $this->__('Recover your user name.');
                    break;
                case 'welcome':
                    $mailerArgs['subject'] = $this->__('Welcome!');
                    break;
                default:
                    $mailerArgs['subject'] = $this->__f('A message from %s.', array(System::getVar('sitename', System::getBaseUrl())));
            }
        }

        if ($mailerArgs['body']) {
            return ModUtil::apiFunc('Mailer', 'user', 'sendMessage', $mailerArgs);
        }

        return true;
    }

    /**
     * Send the user a lost user name code.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['idfield'] (string) The value 'email'.
     *                    $args['id'] (string) The user's e-mail address.
     *
     * @return bool True if user name sent; otherwise false.
     */
    public function mailUname($args)
    {
        $emailMessageSent = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield']) 
                || (($args['idfield'] != 'email') && ($args['idfield'] != 'uid'))) {
            
            return LogUtil::registerArgsError();
        }

        $adminRequested = (isset($args['adminRequest']) && is_bool($args['adminRequest']) && $args['adminRequest']);

        if ($args['idfield'] == 'email') {
            $ucount = DBUtil::selectObjectCountByID ('users', $args['id'], 'email');

            if ($ucount > 1) {
                return false;
            }
        }

        $user = UserUtil::getVars($args['id'], true, $args['idfield']);

        if ($user) {
            $renderer = Zikula_View::getInstance('Users', false);
            $renderer->assign('uname', $user['uname']);
            $renderer->assign('sitename', System::getVar('sitename'));
            $renderer->assign('hostname', System::serverGetVar('REMOTE_ADDR'));
            $renderer->assign('url',  ModUtil::url('Users', 'user', 'loginScreen', array(), null, null, true));
            $renderer->assign('adminRequested',  $adminRequested);
            $htmlBody = $renderer->fetch('users_email_lostuname_html.tpl');
            $plainTextBody = $renderer->fetch('users_email_lostuname_txt.tpl');

            $subject = $this->__f('User name for %s', $user['uname']);

            $emailMessageSent = ModUtil::apiFunc('Mailer', 'user', 'sendMessage',
                array(
                    'toaddress' => $user['email'],
                    'subject'   => $subject,
                    'body'      => $htmlBody,
                    'altbody'   => $plainTextBody
                ));
            if (!$emailMessageSent) {
                LogUtil::registerError($this->__('Error! Unable to send user name e-mail message.'));
            }
        }

        return $emailMessageSent;
    }

    /**
     * Send the user a lost password confirmation code.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['email'] (string) The user's e-mail address.
     *
     * @return bool True if confirmation code sent; otherwise false.
     */
    public function mailConfirmationCode($args)
    {
        $emailMessageSent = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield']) 
                || (($args['idfield'] != 'uname') && ($args['idfield'] != 'email') && ($args['idfield'] != 'uid'))) {
            
            return LogUtil::registerArgsError();
        }

        if ($args['idfield'] == 'email') {
            $ucount = UserUtil::getEmailUsageCount($args['id']);

            if ($ucount > 1) {
                return false;
            }
        }

        $adminRequested = (isset($args['adminRequest']) && is_bool($args['adminRequest']) && $args['adminRequest']);

        $user = UserUtil::getVars($args['id'], true, $args['idfield']);

        if ($user) {
            $confirmationCode = UserUtil::generatePassword();
            $hashedConfirmationCode = UserUtil::getHashedPassword($confirmationCode);

            if ($confirmationCodeHash !== false) {
                $tables = DBUtil::getTables();
                $verifychgColumn = $tables['users_verifychg_column'];
                DBUtil::deleteWhere('users_verifychg',
                    "({$verifychgColumn['uid']} = {$user['uid']}) AND ({$verifychgColumn['changetype']} = " . UserUtil::VERIFYCHGTYPE_PWD . ")");

                $nowUTC = new DateTime(null, new DateTimeZone('UTC'));

                $verifyChangeObj = array(
                    'changetype'    => UserUtil::VERIFYCHGTYPE_PWD,
                    'uid'           => $user['uid'],
                    'newemail'      => '',
                    'verifycode'    => $hashedConfirmationCode,
                    'created_dt'    => $nowUTC->format(UserUtil::DATETIME_FORMAT),
                );
                $codeSaved = DBUtil::insertObject($verifyChangeObj, 'users_verifychg');

                if ($codeSaved) {
                    $urlArgs = array();
                    $urlArgs['code'] = urlencode($confirmationCode);
                    $urlArgs[$args['idfield']] = urlencode($args['id']);

                    $renderer = Zikula_View::getInstance('Users', false);
                    $renderer->assign('uname', $user['uname']);
                    $renderer->assign('sitename', System::getVar('sitename'));
                    $renderer->assign('hostname', System::serverGetVar('REMOTE_ADDR'));
                    $renderer->assign('code', $confirmationCode);
                    $renderer->assign('url',  ModUtil::url('Users', 'user', 'lostPasswordCode', $urlArgs, null, null, true));
                    $renderer->assign('adminRequested',  $adminRequested);
                    $htmlBody = $renderer->fetch('users_email_lostpassword_html.tpl');
                    $plainTextBody = $renderer->fetch('users_email_lostpassword_txt.tpl');

                    $subject = $this->__f('Confirmation code for %s', $user['uname']);

                    $emailMessageSent = ModUtil::apiFunc('Mailer', 'user', 'sendMessage',
                        array(
                            'toaddress' => $user['email'],
                            'subject'   => $subject,
                            'body'      => $htmlBody,
                            'altbody'   => $plainTextBody
                        ));
                    if (!$emailMessageSent) {
                        LogUtil::registerError($this->__('Error! Unable to send confirmation code e-mail message.'));
                    }
                } else {
                    LogUtil::registerError($this->__('Error! Unable to save confirmation code.'));
                }
            } else {
                LogUtil::registerError($this->__("Error! Unable to create confirmation code."));
            }
        }

        return $emailMessageSent;
    }

    /**
     * Check a lost password confirmation code.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['idfield'] (string) Either 'uname' or 'email'.
     *                    $args['id'] (string) The user's user name or e-mail address, depending on the value of idfield.
     *                    $args['code']  (string) The confirmation code.
     *
     * @return bool True if the new password was sent; otherwise false.
     */
    public function checkConfirmationCode($args)
    {
        $codeIsGood = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield']) || !isset($args['code'])
                || empty($args['code']) || (($args['idfield'] != 'uname') && ($args['idfield'] != 'email'))) {
            
            return LogUtil::registerArgsError();
        }

        $user = UserUtil::getVars($args['id'], true, $args['idfield']);

        if (!$user) {
            LogUtil::registerError('Sorry! Could not find any matching user account.');
        } else {
            // delete all the records for password reset confirmation that have expired
            $chgPassExpireDays = $this->getVar('chgpass_expiredays', 0);
            if ($chgPassExpireDays > 0) {
                $staleRecordUTC = new DateTime(null, new DateTimeZone('UTC'));
                $staleRecordUTC->modify("-{$chgPassExpireDays} days");
                $staleRecordUTCStr = $staleRecordUTC->format(UserUtil::DATETIME_FORMAT);
                $where = "({$verifychgColumn['created_dt']} < '{$staleRecordUTCStr}') AND ({$verifychgColumn['changetype']} = " . UserUtil::VERIFYCHGTYPE_PWD . ")";
                DBUtil::deleteWhere ('users_verifychg', $where);
            }

            $tables = DBUtil::getTables();
            $verifychgColumn = $tables['users_verifychg_column'];
            $verifychgObj = DBUtil::selectObject('users_verifychg',
                "({$verifychgColumn['uid']} = {$user['uid']}) AND ({$verifychgColumn['changetype']} = " . UserUtil::VERIFYCHGTYPE_PWD . ")");

            if ($verifychgObj) {
                $codeIsGood = UserUtil::passwordsMatch($args['code'], $verifychgObj['verifycode']);
            } else {
                LogUtil::registerError('Sorry! Could not retrieve a confirmation code for that account.');
            }
        }

        return $codeIsGood;
    }

    /**
     * Display a message indicating that the user's session has expired.
     *
     * @return string The rendered template.
     */
    public function expiredSession()
    {
        $view = Zikula_View::getInstance('Users', false);
        return $view->fetch('users_userapi_expiredsession.tpl');
    }

    /**
     * Retrieve the account links for each user module.
     *
     * @return array An array of links for the user account page.
     */
    public function accountLinks()
    {
        // Get all user modules
        $mods = ModUtil::getAllMods();

        if ($mods == false) {
            return false;
        }

        $accountlinks = array();

        foreach ($mods as $mod) {
            // saves 17 system checks
            if ($mod['type'] == 3 && !in_array($mod['name'], array('Admin', 'Categories', 'Groups', 'Theme', 'Users'))) {
                continue;
            }

            $modpath = ($mod['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

            $ooAccountApiFile = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/lib/{$mod['directory']}/Api/Account.php");
            $legacyAccountApiFile = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/pnaccountapi.php");
            if (file_exists($ooAccountApiFile) || file_exists($legacyAccountApiFile)) {
                $items = ModUtil::apiFunc($mod['name'], 'account', 'getAll');
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
    public function savePreEmail($args)
    {
        if (!UserUtil::isLoggedIn()) {
            return LogUtil::registerPermissionError();
        }

        $dbinfo = DBUtil::getTables();
        $verifychgColumn = $dbinfo['users_verifychg_column'];

        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));

        $uid = UserUtil::getVar('uid');
        $uname = UserUtil::getVar('uname');

        // generate a randomize value of 7 characters needed to confirm the e-mail change
        $confirmCode = UserUtil::generatePassword();
        $confirmCodeHash = UserUtil::getHashedPassword($confirmCode);

        $obj = array(
            'changetype'    => UserUtil::VERIFYCHGTYPE_EMAIL,
            'uid'           => $uid,
            'newemail'      => DataUtil::formatForStore($args['newemail']),
            'verifycode'    => $confirmCodeHash,
            'created_dt'    => $nowUTC->format(UserUtil::DATETIME_FORMAT),
        );

        DBUtil::deleteWhere('users_verifychg',
            "({$verifychgColumn['uid']} = {$uid}) AND ({$verifychgColumn['changetype']} = " . UserUtil::VERIFYCHGTYPE_EMAIL . ")");
        $obj = DBUtil::insertObject($obj, 'users_verifychg', 'id');

        if (!$obj) {
            return false;
        }

        // send confirmation e-mail to user with the changing code
        $subject = $this->__f('Confirmation change of e-mail for %s', $uname);

        $renderer = Zikula_View::getInstance('Users', false);
        $renderer->assign('uname', $uname);
        $renderer->assign('email', UserUtil::getVar('email'));
        $renderer->assign('newemail', $args['newemail']);
        $renderer->assign('sitename', System::getVar('sitename'));
        $renderer->assign('url',  ModUtil::url('Users', 'user', 'confirmChEmail', array('confirmcode' => $confirmCode), null, null, true));

        $message = $renderer->fetch('users_email_userverifyemail_html.tpl');
        $sent = ModUtil::apiFunc('Mailer', 'user', 'sendMessage', array('toaddress' => $args['newemail'], 'subject' => $subject, 'body' => $message, 'html' => true));

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
    public function getUserPreEmail()
    {
        if (!UserUtil::isLoggedIn()) {
            return LogUtil::registerPermissionError();
        }

        $dbinfo = DBUtil::getTables();
        $verifychgColumn = $dbinfo['users_verifychg_column'];

        // delete all the records from e-mail confirmation that have expired
        $chgEmailExpireDays = $this->getVar('chgemail_expiredays', 0);
        if ($chgEmailExpireDays > 0) {
            $staleRecordUTC = new DateTime(null, new DateTimeZone('UTC'));
            $staleRecordUTC->modify("-{$chgEmailExpireDays} days");
            $staleRecordUTCStr = $staleRecordUTC->format(UserUtil::DATETIME_FORMAT);
            $where = "({$verifychgColumn['created_dt']} < '{$staleRecordUTCStr}') AND ({$verifychgColumn['changetype']} = " . UserUtil::VERIFYCHGTYPE_EMAIL . ")";
            DBUtil::deleteWhere ('users_verifychg', $where);
        }

        $uid = UserUtil::getVar('uid');

        $item = DBUtil::selectObject('users_verifychg',
            "({$verifychgColumn['uid']} = {$uid}) AND ({$verifychgColumn['changetype']} = " . UserUtil::VERIFYCHGTYPE_EMAIL . ")");

        if (!$item) {
            return false;
        }

        return $item;
    }

    /**
     * Removes a record from the users_verifychg table for a specified uid and changetype.
     *
     * @param array $args All parameters passed to this function.
     *                      int       $args['uid']        The uid of the verifychg record to remove. Required.
     *                      int|array $args['changetype'] The changetype(s) of the verifychg record to remove. If more
     *                                                      than one type is to be removed, use an array. Optional. If
     *                                                      not specifed, all verifychg records for the user will be
     *                                                      removed. Note: specifying an empty array will remove none.
     *
     * @return void|bool Null on success, false on error.
     */
    public function resetVerifyChgFor($args)
    {
        if (!isset($args['uid'])) {
            return LogUtil::registerArgsError();
        }
        $uid = $args['uid'];
        if (!is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            return LogUtil::registerArgsError();
        }

        if (!isset($args['changetype'])) {
            $changeType = null;
        } else {
            $changeType = $args['changetype'];
            if (!is_array($changeType)) {
                $changeType = array($changeType);
            } elseif (empty($changeType)) {
                return;
            }
            foreach ($changeType as $theType) {
                if (!is_numeric($theType) || ((int)$theType != $theType) || ($theType < 0)) {
                    return LogUtil::registerArgsError();
                }
            }
        }

        $dbinfo = DBUtil::getTables();
        $verifyChgColumn = $dbinfo['users_verifychg_column'];

        $where = "WHERE ({$verifyChgColumn['uid']} = {$uid})";
        if (isset($changeType)) {
            $where .= " AND ({$verifyChgColumn['changetype']} IN (" . implode(', ', $changeType) . "))";
        }
        DBUtil::deleteWhere('users_verifychg', $where);
    }

    /**
     * Get available user menu links.
     *
     * @return array An array of menu links.
     */
    public function getLinks()
    {

        $allowregistration = $this->getVar('reg_allowreg');

        $links = array();

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('Users', 'user', 'loginScreen'), 'text' => $this->__('Log in'), 'class' => 'z-icon-es-user');
            $links[] = array('url' => ModUtil::url('Users', 'user', 'lostPwdUname'), 'text' => $this->__('Lost user name or password'), 'class' => 'z-icon-es-password');
        }

        if ($allowregistration) {
            $links[] = array('url' => ModUtil::url('Users', 'user', 'register'), 'text' => $this->__('New account'), 'class' => 'z-icon-es-adduser');
        }

        return $links;
    }

    /**
     * Convenience function for several functions; converts registration errors into easily displayable sets of data.
     *
     * @param array $args All parameters passed to the function.
     *                      array   $args['registrationErrors'] The array of registration errors from
     *                                                              getRegistrationErrors or one of its related
     *                                                              functions.
     *
     * @return array Modified error information.
     */
    public function processRegistrationErrorsForDisplay($args)
    {
        $errorFields = array();
        $errorMessages = array();

        if (isset($args['registrationErrors']) && is_array($args['registrationErrors']) && !empty($args['registrationErrors'])) {
            $registrationErrors = $args['registrationErrors'];

            foreach ($registrationErrors as $field => $messageList) {
                $errorFields[$field] = true;
                if ($field == 'reginfo_dynadata') {
                    $errorMessages[] = $messageList['result'] . ' ' . $this->_n(
                        '(Note: This field is not highlighted below, but it must still be corrected.)',
                        '(Note: These fields are not highlighted below, but they must still be corrected.)',
                        count($messageList['fields'])
                        );
                } else {
                    $errorMessages = array_merge($errorMessages, $messageList);
                }
            }
        }

        return array(
            'errorFields' => $errorFields,
            'errorMessages' => $errorMessages,
        );
    }
}
