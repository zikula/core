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
 * Provides module installation and upgrade services for the Users module.
 */
class Users_Installer extends Zikula_Installer
{
    /**
     * Initialise the users module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance. This function MUST exist in the pninit file for a module.
     *
     * @return bool True on success, false otherwise.
     */
    public function install()
    {
        if (!DBUtil::createTable('session_info')) {
            return false;
        }

        if (!DBUtil::createTable('users')) {
            return false;
        }

        if (!DBUtil::createTable('users_verifychg')) {
            return false;
        }

        // Set default values for module
        $this->defaultdata();

        $this->setVar('itemsperpage', 25)
             ->setVar('accountdisplaygraphics', 1)
             ->setVar('accountitemsperpage', 25)
             ->setVar('accountitemsperrow', 5)
             ->setVar('changepassword', 1)
             ->setVar('changeemail', 1)
             ->setVar('reg_allowreg', 1)
             ->setVar('reg_verifyemail', UserUtil::VERIFY_USERPWD)
             ->setVar('reg_Illegalusername', 'root adm linux webmaster admin god administrator administrador nobody anonymous anonimo')
             ->setVar('reg_Illegaldomains', '')
             ->setVar('reg_Illegaluseragents', '')
             ->setVar('reg_noregreasons', __('Sorry! New user registration is currently disabled.'))
             ->setVar('reg_uniemail', 1)
             ->setVar('reg_notifyemail', '')
             ->setVar('reg_optitems', 0)
             ->setVar('userimg', 'images/menu')
             ->setVar('avatarpath', 'images/avatar')
             ->setVar('allowgravatars', 1)
             ->setVar('gravatarimage', 'gravatar.gif')
             ->setVar('minage', 13)
             ->setVar('minpass', 5)
             ->setVar('anonymous', 'Guest')
             ->setVar('loginviaoption', 0)
             ->setVar('moderation', 0)
             ->setVar('hash_method', 'sha256')
             ->setVar('login_redirect', 1)
             ->setVar('reg_question', '')
             ->setVar('reg_answer', '')
             ->setVar('use_password_strength_meter', 0)
             ->setVar('default_authmodule', 'Users')
             ->setVar('moderation_order', UserUtil::APPROVAL_BEFORE)
             ->setVar('login_displaymarkeddel', false)
             ->setVar('login_displayinactive', false)
             ->setVar('login_displayverify', false)
             ->setVar('login_displayapproval', false)
             ->setVar('chgemail_expiredays', 0)
             ->setVar('chgpass_expiredays', 0)
             ->setVar('reg_expiredays', 0)
                ;

        // Register persistent event listeners (handlers)
        EventUtil::registerPersistentModuleHandler('Users', 'get.pending_content', array('Users_Listeners', 'pendingContentListener'));
        HookUtil::registerHookSubscriberBundles($this->version);

        // Initialisation successful
        return true;
    }

    /**
     * Upgrade the users module from an older version.
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param string $oldVersion Version number string to upgrade from.
     *
     * @return mixed True on success, last valid version string or false if fails.
     */
    public function upgrade($oldVersion)
    {
        // $oldversion 1.9 and 1.10 handled by Zikula 1.2.
        if (version_compare($oldVersion, '1.11') === -1) {
            return $oldVersion;
        }
        // Versions 1.18 and 2.0.0 were development versions that were released only to developers, and many changes
        // in those two versions regarding database structure were radically modified. Upgrading from those versions
        // is not possible.
        if ((version_compare($oldVersion, '1.17') === 1) && (version_compare($oldVersion, '2.1.0') === -1)) {
            return $oldVersion;
        }

        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.11':
                // upgrade 1.11 to 1.12
                $this->upgrade_migrateSerialisedUserTemp();
            case '1.12':
                // upgrade 1.12 to 1.13
                $this->setVar('avatarpath', 'images/avatar');
                // lowercaseuname Removed in 2.0.0
                //$this->setVar('lowercaseuname', 1);
            case '1.13':
                // upgrade 1.13 to 1.14
                $this->setVar('use_password_strength_meter', 0);
            case '1.14':
                // upgrade 1.14 to 1.15
                if ($this->getVar('hash_method') == 'md5') {
                    $this->setVar('hash_method', 'sha256');
                }
            case '1.15':
                // upgrade 1.15 to 1.16
                $this->delVar('savelastlogindate');
                $this->setVar('allowgravatars', 1);
                $this->setVar('gravatarimage', 'gravatar.gif');
                if (!DBUtil::changeTable('users_temp')) {
                    return '1.15';
                }
            case '1.16':
                // upgrade 1.16 to 1.17
                // authmodules removed in 2.0.0
                //$this->setVar('authmodules', 'Users');
            case '1.17':
                // upgrade 1.17 to 2.1.0
                if (!$this->upgrade117Xto210($oldVersion)) {
                    return '1.17';
                }
            case '2.1.0':
                // Register persistent event listeners (handlers)
                EventUtil::registerPersistentModuleHandler('Users', 'get.pending_content', array('Users_Listeners', 'pendingContentListener'));
            case '2.1.1':
                // Update users table for data type change of activated field.
                if (!DBUtil::changeTable('users')) {
                    return '2.1.1';
                }
            case '2.1.2':
                HookUtil::registerHookSubscriberBundles($this->version);
            case '2.1.3':
                // Current version: add 2.1.3 --> next when appropriate
        }

        // Update successful
        return true;
    }

    /**
     * Delete the users module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance. This function MUST exist in the pninit file for a module.
     *
     * Since the users module should never be deleted we'all always return false here.
     *
     * @return bool false
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Create the default data for the users module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     *
     * @return void
     */
    public function defaultdata()
    {
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(UserUtil::DATETIME_FORMAT);

        // Anonymous
        $record = array(
            'uid'           => 1,
            'uname'         => 'guest',
            'email'         => '',
            'pass'          => '',
            'passreminder'  => '',
            'activated'     => UserUtil::ACTIVATED_ACTIVE,
            'approved_date' => $nowUTCStr,
            'approved_by'   => 2,
            'user_regdate'  => $nowUTCStr,
            'theme'         => '',
            'ublockon'      => 0,
            'ublock'        => '',
        );
        DBUtil::insertObject($record, 'users', 'uid', true);

        // Admin
        $record = array(
            'uid'           => 2,
            'uname'         => 'admin',
            'email'         => '',
            'pass'          => '1$$dc647eb65e6711e155375218212b3964',
            'passreminder'  => '',
            'activated'     => UserUtil::ACTIVATED_ACTIVE,
            'approved_date' => $nowUTCStr,
            'approved_by'   => 2,
            'user_regdate'  => $nowUTCStr,
            'theme'         => '',
            'ublockon'      => 0,
            'ublock'        => '',
        );
        DBUtil::insertObject($record, 'users', 'uid', true);
    }

    /**
     * Migrate serialized data in users_temp.
     *
     * @return void
     */
    public function upgrade_migrateSerialisedUserTemp()
    {
        $array = DBUtil::selectObjectArray('users_temp');
        foreach ($array as $obj) {
            if (DataUtil::is_serialized($obj['dynamics'])) {
                $obj['dynamics'] = serialize(DataUtil::mb_unserialize($obj['dynamics']));
            }
            DBUtil::updateObject($obj, 'users_temp', '', 'tid');
        }
    }

    /**
     * Migrate from version 1.17 to 2.1.0
     *
     * @param string $oldversion The old version from which this upgrade is being processed.
     *
     * @return bool True on success; otherwise false.
     */
    public function upgrade117Xto210($oldversion)
    {
        // Get the dbinfo for the new version
        $funcExists = function_exists('Users_tables');
        if (!$funcExists) {
            require_once 'system/Users/tables.php';
        }

        $dbinfoSystem = DBUtil::getTables();
        $dbinfo117X = Users_tables('1.17');
        $dbinfo210 = Users_tables('2.1.0');
        $usersOldFields = array('user_theme', 'user_viewemail', 'storynum', 'counter', 'hash_method', 'validfrom', 'validuntil');
        $usersOldFieldsDB = array($dbinfo117X['users_column']['user_theme'], $dbinfo117X['users_column']['user_viewemail'],
            $dbinfo117X['users_column']['storynum'], $dbinfo117X['users_column']['counter'], $dbinfo117X['users_column']['hash_method'],
            $dbinfo117X['users_column']['validfrom'], $dbinfo117X['users_column']['validuntil']);

        // Upgrade the tables

        // Update the users table with new and altered fields. No fields are removed at this point, and no fields
        // are getting a new data type that is incompatible, so no need to save anything off first.
        // Also, create the users_verifychg tables at this point.
        // Hack the global dbtables with the new field information.
        $GLOBALS['dbtables']['users_column'] = $dbinfo210['users_column'];
        $GLOBALS['dbtables']['users_column_def'] = $dbinfo210['users_column_def'];
        $GLOBALS['dbtables']['users_verifychg'] = $dbinfo210['users_verifychg'];
        $GLOBALS['dbtables']['users_verifychg_column'] = $dbinfo210['users_verifychg_column'];
        $GLOBALS['dbtables']['users_verifychg_column_def'] = $dbinfo210['users_verifychg_column_def'];

        // Now change the tables
        if (!DBUtil::changeTable('users')) {
            return false;
        }
        if (!DBUtil::createTable('users_verifychg')) {
            return false;
        }

        // First users_temp pending email verification records to users_verifychg.
        $tempColumn = $dbinfo117X['users_temp_column'];
        $verifyColumn = $dbinfo210['users_verifychg_column'];
        $usersColumn = $dbinfo210['users_column'];

        $limitNumRows = 100;
        $limitOffset = 0;
        $updated = true;
        $userCount = DBUtil::selectObjectCount('users_temp');
        // Pass through the users_temp table in chunks of 100, ensuring unames and email addresses are lower case,
        // and converting pending email change request data in preparation for converstion to users_verifychg
        while ($limitOffset < $userCount) {
            $userArray = DBUtil::selectObjectArray('users_temp', '', '', $limitOffset, $limitNumRows, '', null, null,
                array('tid', 'type', 'uname', 'email', 'pass', 'hash_method', 'dynamics', 'comment'));
            if (!empty($userArray) && is_array($userArray)) {
                foreach ($userArray as $key => $userObj) {
                    // Ensure uname and email are lower case
                    $userArray[$key]['uname'] = mb_strtolower($userArray[$key]['uname']);
                    $userArray[$key]['email'] = mb_strtolower($userArray[$key]['email']);

                    if ($userArray[$key]['type'] == 1) {
                        // type == 1: User registration pending approval (moderation)
                        // Convert pass to salted pass with embedded hash method, leave salt blank
                        $userArray[$key]['pass'] = $userArray[$key]['hash_method'] . '$$' . $userArray[$key]['pass'];
                    } elseif ($userArray[$key]['type'] == 2) {
                        // type == 2: E-mail change request pending verification
                        if (isset($userArray[$key]['dynamics']) && !empty($userArray[$key]['dynamics']) 
                                && is_numeric($userArray[$key]['dynamics'])) {
                            // Convert the date to a date/time format instead of a UNIX timestamp
                            $theDate = new DateTime("@{$userArray[$key]['dynamics']}", $tzUTC);
                            $userArray[$key]['dynamics'] = $theDate->format(UserUtil::DATETIME_FORMAT);
                        }
                        if (isset($userArray[$key]['comment']) && !empty($userArray[$key]['comment']) 
                                && is_string($userArray[$key]['comment'])) {
                            // Convert the verification code into a salted hash with blank salt, and specify the
                            // hash method used in 1.2
                            $userArray[$key]['comment'] = '1$$' . $userArray[$key]['comment'];
                        }
                    }
                }
            }
            if (!DBUtil::updateObjectArray($userArray, 'users_temp', 'tid', false)) {
                $updated = false;
                break;
            }
            $limitOffset += $limitNumRows;
        }
        if (!$updated) {
            return false;
        }
        // After converting, now use SQL to transfer the data for pending e-mail change requests into the
        // users_verifychg table
        $sql = "INSERT INTO {$dbinfo210['users_verifychg']}
                    ({$verifyColumn['changetype']}, {$verifyColumn['uid']}, {$verifyColumn['newemail']},
                     {$verifyColumn['verifycode']}, {$verifyColumn['created_dt']})
                SELECT " . UserUtil::VERIFYCHGTYPE_EMAIL . " AS {$verifyColumn['changetype']},
                    users.{$usersColumn['uid']} AS {$verifyColumn['uid']},
                    ut.{$tempColumn['email']} AS {$verifyColumn['newemail']},
                    ut.{$tempColumn['comment']} AS {$verifyColumn['verifycode']},
                    ut.{$tempColumn['dynamics']} AS {$verifyColumn['created_dt']}
                FROM {$dbinfo117X['users_temp']} AS ut
                    INNER JOIN {$dbinfo210['users']} AS users ON ut.{$tempColumn['uname']} = users.{$usersColumn['uname']}
                WHERE ut.{$tempColumn['type']} = 2";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }

        // Next, users table conversion
        // We need to convert some information over from the old users table fields, so merge the old field list into
        // the new one. The order of array_merge parameters is important here!
        $GLOBALS['dbtables']['users_column'] = array_merge($dbinfo117X['users_column'], $dbinfo210['users_column']);
        // Do the conversion in PHP we use mb_strtolower, and even if MySQL had an equivalent, there is
        // no guarantee that another supported DB platform would.
        $limitNumRows = 100;
        $limitOffset = 0;
        $updated = true;
        $userCount = DBUtil::selectObjectCount('users');
        while ($limitOffset < $userCount) {
            $userArray = DBUtil::selectObjectArray('users', "{$usersColumn['uid']} != 1", '', $limitOffset, $limitNumRows,
                '', null, null, array('uid', 'uname', 'email', 'pass', 'hash_method', 'user_regdate', 'lastlogin'));
            if (!empty($userArray) && is_array($userArray)) {
                foreach ($userArray as $key => $userObj) {
                    // force user names and emails to lower case
                    $userArray[$key]['uname'] = mb_strtolower($userArray[$key]['uname']);
                    $userArray[$key]['email'] = mb_strtolower($userArray[$key]['email']);

                    // merge hash method for salted passwords, leave salt blank
                    if (!empty($userArray[$key]['pass']) && (strpos($userArray[$key]['pass'], '$$') === false)) {
                        $userArray[$key]['pass'] =
                            (isset($userArray[$key]['hash_method'])
                                ? $userArray[$key]['hash_method']
                                : '1')
                            . '$$' . $userArray[$key]['pass'];
                    }

                    // Save some disappearing fields as attributes, just in case someone actually used them for
                    // something. But don't overwrite if there already
                    if (!isset($userArray[$key]['__ATTRIBUTES__']) || !is_array($userArray[$key]['__ATTRIBUTES__'])) {
                        $userArray[$key]['__ATTRIBUTES__'] = array();
                    }
                    foreach ($usersOldFields as $fieldName) {
                        if (($fieldName != 'hash_method') && isset($userArray[$key][$fieldName])
                                && !empty($userArray[$key][$fieldName]) && !isset($userArray[$key]['__ATTRIBUTES__'][$fieldName])) {
                            $userArray[$key]['__ATTRIBUTES__'][$fieldName] = $userArray[$key][$fieldName];
                        }
                    }
                }
            }
            if (!DBUtil::updateObjectArray($userArray, 'users', 'uid', false)) {
                $updated = false;
                break;
            }
            $limitOffset += $limitNumRows;
        }
        if (!$updated) {
            return false;
        }

        $obaColumn = $dbinfoSystem['objectdata_attributes_column'];

        // Next, users_temp conversion to users. This needs to be done in a few steps, since we have to set some object
        // attributes too. Step 1, from the users_temp table to the main user table, pending registrations that are
        // awaiting approval.
        $sql = "INSERT INTO {$dbinfoSystem['users']}
                    ({$usersColumn['uname']}, {$usersColumn['email']}, {$usersColumn['pass']}, {$usersColumn['activated']},
                     {$usersColumn['approved_by']})
                SELECT {$tempColumn['uname']} AS {$usersColumn['uname']},
                    {$tempColumn['email']} AS {$usersColumn['email']},
                    {$tempColumn['pass']} AS {$usersColumn['pass']},
                    ".UserUtil::ACTIVATED_PENDING_REG." AS {$usersColumn['activated']},
                    0 AS {$usersColumn['approved_by']}
                FROM {$dbinfo117X['users_temp']}
                WHERE {$dbinfo117X['users_temp']}.{$tempColumn['type']} = 1";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }

        // Next we need to get the dynadata into the objectdata_attributes table
        $sql = "INSERT INTO {$dbinfoSystem['objectdata_attributes']}
                    ({$obaColumn['attribute_name']}, {$obaColumn['object_id']}, {$obaColumn['object_type']},
                     {$obaColumn['value']})
                SELECT 'dynadata' AS {$obaColumn['attribute_name']},
                    users.{$usersColumn['uid']} AS {$obaColumn['object_id']},
                    'users' AS {$obaColumn['object_type']},
                    ut.{$tempColumn['dynamics']} AS {$obaColumn['value']}
                FROM {$dbinfo117X['users_temp']} AS ut
                LEFT JOIN {$dbinfo210['users']} AS users
                    ON ut.{$tempColumn['uname']} = users.{$usersColumn['uname']}
                WHERE (ut.{$tempColumn['type']} = 1)
                    AND (users.{$usersColumn['activated']} = ".UserUtil::ACTIVATED_PENDING_REG.")";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }

        // Next we need to get the isverified field into the objectdata_attributes table
        $sql = "INSERT INTO {$dbinfoSystem['objectdata_attributes']}
                    ({$obaColumn['attribute_name']}, {$obaColumn['object_id']}, {$obaColumn['object_type']},
                     {$obaColumn['value']})
                SELECT 'isverified' AS {$obaColumn['attribute_name']},
                    users.{$usersColumn['uid']} AS {$obaColumn['object_id']},
                    'users' AS {$obaColumn['object_type']},
                    0 AS {$obaColumn['value']}
                FROM {$dbinfo117X['users_temp']} AS ut
                LEFT JOIN {$dbinfo210['users']} AS users
                    ON ut.{$tempColumn['uname']} = users.{$usersColumn['uname']}
                WHERE (ut.{$tempColumn['type']} = 1)
                    AND (users.{$usersColumn['activated']} = ".UserUtil::ACTIVATED_PENDING_REG.")";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }

        // Finally, we need to get the agreetoterms field into the objectdata_attributes table
        $sql = "INSERT INTO {$dbinfoSystem['objectdata_attributes']}
                    ({$obaColumn['attribute_name']}, {$obaColumn['object_id']}, {$obaColumn['object_type']},
                     {$obaColumn['value']})
                SELECT 'agreetoterms' AS {$obaColumn['attribute_name']},
                    users.{$usersColumn['uid']} AS {$obaColumn['object_id']},
                    'users' AS {$obaColumn['object_type']},
                    1 AS {$obaColumn['value']}
                FROM {$dbinfo117X['users_temp']} AS ut
                LEFT JOIN {$dbinfo210['users']} AS users
                    ON ut.{$tempColumn['uname']} = users.{$usersColumn['uname']}
                WHERE (ut.{$tempColumn['type']} = 1)
                    AND (users.{$usersColumn['activated']} = ".UserUtil::ACTIVATED_PENDING_REG.")";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }

        // Done upgrading. Let's lose some old fields and tables we no longer need.
        DBUtil::dropColumn('users', $usersOldFieldsDB);
        DBUtil::dropTable('users_temp');

        // Reset $GLOBALS['dbtables'] to the new table definitons, so the rest of the
        // system upgrade goes smoothly.
        foreach ($dbinfo117X as $key => $value) {
            unset($GLOBALS['dbtables'][$key]);
        }
        foreach ($dbinfo210 as $key => $value) {
            $GLOBALS['dbtables'][$key] = $value;
        }

        // done with db changes. Now handle some final stuff.
        $this->delVar('authmodules');
        $this->setVar('default_authmodule', 'Users');

        $regVerifyEmail = $this->getVar('reg_verifyemail', UserUtil::VERIFY_NO);
        if ($regVerifyEmail == UserUtil::VERIFY_SYSTEMPWD) {
            $this->setVar('reg_verifyemail', UserUtil::VERIFY_USERPWD);
        }

        $this->setVar('moderation_order', UserUtil::APPROVAL_BEFORE)
             ->setVar('login_displaymarkeddel', false)
             ->setVar('login_displayinactive', false)
             ->setVar('login_displayverify', false)
             ->setVar('login_displayapproval', false)
             ->setVar('chgemail_expiredays', 0)
             ->setVar('chgpass_expiredays', 0)
             ->setVar('reg_expiredays', 0)
                ;

        $this->delVar('reg_forcepwdchg');
        $this->delVar('lowercaseuname');
        $this->delVar('recovery_forcepwdchg');

        // IDN domains setting moving to system settings.
        System::setVar('idnnames', (bool)$this->getVar('idnnames', true));
        $this->delVar('idnnames');

        return true;
    }
}
