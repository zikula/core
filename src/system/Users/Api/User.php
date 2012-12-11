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
class Users_Api_User extends Zikula_AbstractApi
{
    /**
     * Get all users (for which the current user has permission to read).
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * string  $args['letter']   The first letter of the set of user names to return.
     * integer $args['starnum']  First item to return (optional).
     * integer $args['numitems'] Number if items to return (optional).
     * array   $args['sort']     The field(s) on which to sort the result (optional).
     *
     * @param array $args All parameters passed to this function.
     *
     * @return array An array of users, or false on failure.
     *
     * @throws Zikula_Exception_Fatal Thrown if invalid parameters are received in $args, or if the data cannot be loaded from the database.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have overview access.
     */
    public function getAll($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW)) {
            throw new Zikula_Exception_Forbidden();
        }

        // Check validity of startnum arg, or set default
        if (!isset($args['startnum'])) {
            $limitOffset = -1;
        } else {
            if (is_numeric($args['startnum']) && ((int)$args['startnum'] == $args['startnum'])) {
                $limitOffset = (int)$args['startnum'] - 1;
            } else {
                throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
            }
        }

        // Check validity of numitems arg, or set default
        if (!isset($args['numitems'])) {
            $limitNumRows = -1;
        } else {
            if (is_numeric($args['numitems']) && ((int)$args['numitems'] == $args['numitems']) && ($args['numitems'] >= 1)) {
                $limitNumRows = (int)$args['numitems'];
            } else {
                throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
            }
        }

        // Check validity of letter arg.
        // $args['letter'] is really an SQL LIKE filter
        if (isset($args['letter']) && (empty($args['letter']) || !is_string($args['letter']) || strstr($args['letter'], '%'))) {
            throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
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
                throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
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
                    throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
                } elseif (isset($usersColumn[$fieldName])) {
                    $orderBy[] = $usersColumn[$fieldName] . (!empty($direction) ? ' ' . $direction : '');
                } else {
                    throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
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
            'component_left'    => $this->name,
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
        $where[] = "({$usersColumn['activated']} NOT IN (" . implode(', ', array(Users_Constant::ACTIVATED_PENDING_REG, Users_Constant::ACTIVATED_PENDING_DELETE)) . '))';
        $where = 'WHERE ' . implode(' AND ', $where);

        $objArray = DBUtil::selectObjectArray('users', $where, $orderBy, $limitOffset, $limitNumRows, null, $permFilter);

        // Check for a DB error
        if ($objArray === false) {
            throw new Zikula_Exception_Fatal($this->__('Error! Could not load data.'));
        }

        return $objArray;
    }

    /**
     * Get a specific user record.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * numeric $args['uid']   The id of user to get (required, unless uname specified).
     * string  $args['uname'] The user name of user to get (ignored if uid is specified, otherwise required).
     *
     * @param array $args All parameters passed to this function.
     *
     * @return array The user record as an array, or false on failure.
     */
    public function get($args)
    {
        // Argument check
        if (isset($args['uid'])) {
            if (!is_numeric($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());

                return false;
            } else {
                $key = (int)$args['uid'];
                $field = 'uid';
            }
        } elseif (!isset($args['uname']) || !is_string($args['uname'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
        } else {
            $key = $args['uname'];
            $field = 'uname';
        }

        $obj = UserUtil::getVars($key, false, $field);

        // Check for a DB error
        if ($obj === false) {
            $this->registerError($this->__('Error! Could not load data.'));

            return false;
        }

        // Return the item array
        return $obj;
    }

    /**
     * Count and return the number of users.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * string $args['letter'] If specified, then only those user records whose user name begins with the specified letter are counted.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return int Number of users.
     *
     * @todo Shouldn't there be some sort of limit on the select/loop??
     */
    public function countItems($args)
    {
        // Check validity of letter arg.
        // $args['letter'] is really an SQL LIKE filter
        if (isset($args['letter']) && (empty($args['letter']) || !is_string($args['letter']) || strstr($args['letter'], '%'))) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
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
            $this->registerError($this->__('Error! Could not load data.'));

            return false;
        }

        return $objCount;
    }

    /**
     * Sends a notification e-mail of a specified type to a user or registrant.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * string $args['toAddress']        The destination e-mail address.
     * string $args['notificationType'] The type of notification, converted to the name of a template
     *                                          in the form users_userapi_{type}mail.tpl and/or .txt.
     * array  $args['templateArgs']     One or more arguments to pass to the renderer for use in the template.
     * string $args['subject']          The e-mail subject, overriding the template's subject.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return <type>
     */
    public function sendNotification($args)
    {
        $toAddress = $args['toAddress'];
        $notificationType = $args['notificationType'];
        $templateArgs = $args['templateArgs'];

        $renderer = Zikula_View::getInstance($this->name, false);

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
     * Send the user an account information recovery e-mail.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * string $args['idfield'] The value 'email'.
     * string $args['id']      The user's e-mail address.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True if user name sent; otherwise false.
     */
    public function mailUname($args)
    {
        $emailMessageSent = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield'])
                || (($args['idfield'] != 'email') && ($args['idfield'] != 'uid'))) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
        }

        $adminRequested = (isset($args['adminRequest']) && is_bool($args['adminRequest']) && $args['adminRequest']);

        if ($args['idfield'] == 'email') {
            $ucount = DBUtil::selectObjectCountByID ('users', $args['id'], 'email');

            if ($ucount > 1) {
                return false;
            }
        }

        $userObj = UserUtil::getVars($args['id'], true, $args['idfield']);

        if ($userObj) {
            $authenticationMethods = UserUtil::getUserAccountRecoveryInfo($userObj['uid']);

            $view = Zikula_View::getInstance($this->name, false);
            $viewArgs = array(
                'uname'                 => $userObj['uname'],
                'email'                 => $userObj['email'],
                'has_password'          => !empty($userObj['pass']) && ($userObj['pass'] != Users_Constant::PWD_NO_USERS_AUTHENTICATION),
                'authentication_methods'=> $authenticationMethods,
                'sitename'              => System::getVar('sitename'),
                'hostname'              => System::serverGetVar('REMOTE_ADDR'),
                'url'                   => ModUtil::url($this->name, 'user', 'login', array(), null, null, true),
                'adminRequested'        => $adminRequested,
            );
            $view->assign($viewArgs);
            $htmlBody = $view->fetch('users_email_lostuname_html.tpl');
            $plainTextBody = $view->fetch('users_email_lostuname_txt.tpl');

            $subject = $this->__f('Account information for %s', $userObj['uname']);

            $emailMessageSent = ModUtil::apiFunc('Mailer', 'user', 'sendMessage', array(
                'toaddress' => $userObj['email'],
                'subject'   => $subject,
                'body'      => $htmlBody,
                'altbody'   => $plainTextBody
            ));

            if (!$emailMessageSent) {
                $this->registerError($this->__('Error! Unable to send user name e-mail message.'));
            }
        }

        return $emailMessageSent;
    }

    /**
     * Send the user a lost password confirmation code.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * string $args['email'] The user's e-mail address.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True if confirmation code sent; otherwise false.
     */
    public function mailConfirmationCode($args)
    {
        $emailMessageSent = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield'])
                || (($args['idfield'] != 'uname') && ($args['idfield'] != 'email') && ($args['idfield'] != 'uid'))
                ) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
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
                    "({$verifychgColumn['uid']} = {$user['uid']}) AND ({$verifychgColumn['changetype']} = " . Users_Constant::VERIFYCHGTYPE_PWD . ")");

                $nowUTC = new DateTime(null, new DateTimeZone('UTC'));

                $verifyChangeObj = array(
                    'changetype'    => Users_Constant::VERIFYCHGTYPE_PWD,
                    'uid'           => $user['uid'],
                    'newemail'      => '',
                    'verifycode'    => $hashedConfirmationCode,
                    'created_dt'    => $nowUTC->format(Users_Constant::DATETIME_FORMAT),
                );
                $codeSaved = DBUtil::insertObject($verifyChangeObj, 'users_verifychg');

                if ($codeSaved) {
                    $urlArgs = array();
                    $urlArgs['code'] = urlencode($confirmationCode);
                    $urlArgs[$args['idfield']] = urlencode($args['id']);

                    $view = Zikula_View::getInstance($this->name, false);
                    $viewArgs=array(
                        'uname'         => $user['uname'],
                        'sitename'      => System::getVar('sitename'),
                        'hostname'      => System::serverGetVar('REMOTE_ADDR'),
                        'code'          => $confirmationCode,
                        'url'           => ModUtil::url($this->name, 'user', 'lostPasswordCode', $urlArgs, null, null, true),
                        'adminRequested'=> $adminRequested,
                    );
                    $view->assign($viewArgs);
                    $htmlBody = $view->fetch('users_email_lostpassword_html.tpl');
                    $plainTextBody = $view->fetch('users_email_lostpassword_txt.tpl');

                    $subject = $this->__f('Confirmation code for %s', $user['uname']);

                    $emailMessageSent = ModUtil::apiFunc('Mailer', 'user', 'sendMessage', array(
                        'toaddress' => $user['email'],
                        'subject'   => $subject,
                        'body'      => $htmlBody,
                        'altbody'   => $plainTextBody
                    ));

                    if (!$emailMessageSent) {
                        $this->registerError($this->__('Error! Unable to send confirmation code e-mail message.'));
                    }
                } else {
                    $this->registerError($this->__('Error! Unable to save confirmation code.'));
                }
            } else {
                $this->registerError($this->__("Error! Unable to create confirmation code."));
            }
        }

        return $emailMessageSent;
    }

    /**
     * Check a lost password confirmation code.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * string $args['idfield'] Either 'uname' or 'email'.
     * string $args['id']      The user's user name or e-mail address, depending on the value of idfield.
     * string $args['code']    The confirmation code.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True if the new password was sent; otherwise false.
     */
    public function checkConfirmationCode($args)
    {
        $codeIsGood = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield']) || !isset($args['code'])
                || empty($args['code']) || (($args['idfield'] != 'uname') && ($args['idfield'] != 'email'))) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
        }

        $user = UserUtil::getVars($args['id'], true, $args['idfield']);

        if (!$user) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
        } else {
            // delete all the records for password reset confirmation that have expired
            $tables = DBUtil::getTables();
            $verifychgColumn = $tables['users_verifychg_column'];
            $chgPassExpireDays = $this->getVar(Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, Users_Constant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD);
            if ($chgPassExpireDays > 0) {
                $staleRecordUTC = new DateTime(null, new DateTimeZone('UTC'));
                $staleRecordUTC->modify("-{$chgPassExpireDays} days");
                $staleRecordUTCStr = $staleRecordUTC->format(Users_Constant::DATETIME_FORMAT);
                $where = "({$verifychgColumn['created_dt']} < '{$staleRecordUTCStr}') AND ({$verifychgColumn['changetype']} = " . Users_Constant::VERIFYCHGTYPE_PWD . ")";
                DBUtil::deleteWhere ('users_verifychg', $where);
            }
            $verifychgObj = DBUtil::selectObject('users_verifychg',
                "({$verifychgColumn['uid']} = {$user['uid']}) AND ({$verifychgColumn['changetype']} = " . Users_Constant::VERIFYCHGTYPE_PWD . ")");
            if ($verifychgObj) {
                $codeIsGood = UserUtil::passwordsMatch($args['code'], $verifychgObj['verifycode']);
            } else {
                $this->registerError('Sorry! Could not retrieve a confirmation code for that account.');
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
        $view = Zikula_View::getInstance($this->name, false);
        $view->assign('returnpage', urlencode(System::getCurrentUri()));
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
            if ($mod['type'] == 3 && !in_array($mod['name'], array('Admin', 'Categories', 'Groups', 'Theme', $this->name))) {
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
     * Parameters passed in the $args array:
     * -------------------------------------
     * string $args['newemail'] The new e-mail address to store pending confirmation.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True if success and false otherwise.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user is logged in.
     */
    public function savePreEmail($args)
    {
        if (!UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden();
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
            'changetype'    => Users_Constant::VERIFYCHGTYPE_EMAIL,
            'uid'           => $uid,
            'newemail'      => DataUtil::formatForStore($args['newemail']),
            'verifycode'    => $confirmCodeHash,
            'created_dt'    => $nowUTC->format(Users_Constant::DATETIME_FORMAT),
        );

        DBUtil::deleteWhere('users_verifychg',
            "({$verifychgColumn['uid']} = {$uid}) AND ({$verifychgColumn['changetype']} = " . Users_Constant::VERIFYCHGTYPE_EMAIL . ")");
        $obj = DBUtil::insertObject($obj, 'users_verifychg', 'id');

        if (!$obj) {
            return false;
        }

        // send confirmation e-mail to user with the changing code
        $subject = $this->__f('Confirmation change of e-mail for %s', $uname);

        $view = Zikula_View::getInstance($this->name, false);
        $viewArgs = array(
            'uname'     => $uname,
            'email'     => UserUtil::getVar('email'),
            'newemail'  => $args['newemail'],
            'sitename'  => System::getVar('sitename'),
            'url'       =>  ModUtil::url($this->name, 'user', 'confirmChEmail', array('confirmcode' => $confirmCode), null, null, true),
        );
        $view->assign($viewArgs);

        $message = $view->fetch('users_email_userverifyemail_html.tpl');
        $sent = ModUtil::apiFunc('Mailer', 'user', 'sendMessage', array(
            'toaddress' => $args['newemail'],
            'subject'   => $subject,
            'body'      => $message,
            'html'      => true
        ));

        if (!$sent) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve the user's new e-mail address that is awaiting his confirmation.
     *
     * @return string The e-mail address waiting for confirmation for the current user.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user is logged in.
     */
    public function getUserPreEmail()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden();
        }

        $dbinfo = DBUtil::getTables();
        $verifychgColumn = $dbinfo['users_verifychg_column'];

        // delete all the records from e-mail confirmation that have expired
        $chgEmailExpireDays = $this->getVar(Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL, Users_Constant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL);
        if ($chgEmailExpireDays > 0) {
            $staleRecordUTC = new DateTime(null, new DateTimeZone('UTC'));
            $staleRecordUTC->modify("-{$chgEmailExpireDays} days");
            $staleRecordUTCStr = $staleRecordUTC->format(Users_Constant::DATETIME_FORMAT);
            $where = "({$verifychgColumn['created_dt']} < '{$staleRecordUTCStr}') AND ({$verifychgColumn['changetype']} = " . Users_Constant::VERIFYCHGTYPE_EMAIL . ")";
            DBUtil::deleteWhere ('users_verifychg', $where);
        }

        $uid = UserUtil::getVar('uid');

        $item = DBUtil::selectObject('users_verifychg',
            "({$verifychgColumn['uid']} = {$uid}) AND ({$verifychgColumn['changetype']} = " . Users_Constant::VERIFYCHGTYPE_EMAIL . ")");

        if (!$item) {
            return false;
        }

        return $item;
    }

    /**
     * Removes a record from the users_verifychg table for a specified uid and changetype.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * integer       $args['uid']        The uid of the verifychg record to remove. Required.
     * integer|array $args['changetype'] The changetype(s) of the verifychg record to remove. If more
     *                                          than one type is to be removed, use an array. Optional. If
     *                                          not specifed, all verifychg records for the user will be
     *                                          removed. Note: specifying an empty array will remove none.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return void|bool Null on success, false on error.
     */
    public function resetVerifyChgFor($args)
    {
        if (!isset($args['uid'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
        }
        $uid = $args['uid'];
        if (!is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
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
                    $this->registerError(LogUtil::getErrorMsgArgs());

                    return false;
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
            $links[] = array(
                'url'   => ModUtil::url($this->name, 'user', 'login'),
                'text'  => $this->__('Log in'),
                'class' => 'z-icon-es-user',
            );
            $links[] = array(
                'url'   => ModUtil::url($this->name, 'user', 'lostPwdUname'),
                'text'  => $this->__('Recover account information or password'),
                'class' => 'user-icon-password',
            );
        }

        if ($allowregistration) {
            $links[] = array(
                'url'   => ModUtil::url($this->name, 'user', 'register'),
                'text'  => $this->__('New account'),
                'class' => 'user-icon-adduser',
            );
        }

        return $links;
    }

    /**
     * Convenience function for several functions; converts registration errors into easily displayable sets of data.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array   $args['registrationErrors'] The array of registration errors from getRegistrationErrors or one of its related functions.
     *
     * @param array $args All parameters passed to the function.
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
                $errorMessages = array_merge($errorMessages, is_array($messageList) ? $messageList : array($messageList));
            }
        }

        return array(
            'errorFields' => $errorFields,
            'errorMessages' => $errorMessages,
        );
    }
}
