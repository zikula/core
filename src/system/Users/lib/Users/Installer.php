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
class Users_Installer extends Zikula_AbstractInstaller
{
    /**
     * Convenience access to the modname.
     *
     * @var string
     */
    protected $name;

    /**
     * Initializes the intstaller.
     *
     * @param Zikula_ServiceManager $serviceManager The service manager instance for the current core instance.
     * @param array                 $options        The {@link Zikula_AbstractBase} options; optional; not used.
     */
    public function  __construct(Zikula_ServiceManager $serviceManager, array $options = array()) {
        parent::__construct($serviceManager, $options);

        $this->name = Users_UserInterface::MODNAME;
    }

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

        // Set default values and modvars for module
        $this->defaultdata();
        $this->setVars($this->getDefaultModvars());

        // Register persistent event listeners (handlers)
        EventUtil::registerPersistentModuleHandler($this->name, 'get.pending_content', array('Users_Listener_PendingContent', 'pendingContentListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'user.login.veto', array('Users_Listener_ForcedPasswordChange', 'forcedPasswordChangeListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'user.logout.succeeded', array('Users_Listener_ClearUsersNamespace', 'clearUsersNamespaceListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'frontcontroller.exception', array('Users_Listener_ClearUsersNamespace', 'clearUsersNamespaceListener'));
        
        // Register persistent hook bundles
        HookUtil::registerHookSubscriberBundles($this->version);
        HookUtil::registerHookProviderBundles($this->version);

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
        // Versions 1.14 through 2.1.0 were development versions that were released only to developers, and many changes
        // over the course of those versions regarding database structure were radically modified. Upgrading from any of
        // those versions is not possible.
        if ((version_compare($oldVersion, '1.13') === 1) && (version_compare($oldVersion, '2.2.0') === -1)) {
            return $oldVersion;
        }

        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.11':
                // upgrade 1.11 to 1.12
                $this->upgrade_migrateSerialisedUserTemp();
            case '1.12':
                // upgrade 1.12 to 1.13
                $this->setVar(Users_UserInterface::MODVAR_AVATAR_IMAGE_PATH, Users_UserInterface::MODVAR_AVATAR_IMAGE_PATH);
                // lowercaseuname Removed in 2.0.0
                //$this->setVar('lowercaseuname', 1);
                // **************************************************************
                // 1.12->1.13 is the last known upgrade of Users for Zikula 1.2.x
                // Users module 1.13 is the last known 1.2.x version released.
                // If the 1.2.x branch gets a new version, this must be updated.
                // **************************************************************
            case '1.13':
                // upgrade 1.13 to 2.2.0
                // Check if the hash method is md5. If so, it is not used any more. Change it to the new default.
                if ($this->getVar(Users_UserInterface::MODVAR_HASH_METHOD, false) == 'md5') {
                    $this->setVar(Users_UserInterface::MODVAR_HASH_METHOD, Users_UserInterface::DEFAULT_HASH_METHOD);
                }
                
                // Convert the banned user names to a comma separated list.
                $bannedUnames = $this->getVar(Users_UserInterface::MODVAR_REGISTRATION_ILLEGAL_UNAMES, '');
                $bannedUnames = preg_split('/\s+/', $bannedUnames);
                $bannedUnames = implode(', ', $bannedUnames);
                $this->setVar(Users_UserInterface::MODVAR_REGISTRATION_ILLEGAL_UNAMES, $bannedUnames);
                
                // System-generated passwords are deprecated since 1.3.0. Change it to
                // User-generated passwords.
                $regVerifyEmail = $this->getVar(Users_UserInterface::MODVAR_REGISTRATION_VERIFICATION_MODE, Users_UserInterface::VERIFY_NO);
                if ($regVerifyEmail == Users_UserInterface::VERIFY_SYSTEMPWD) {
                    $this->setVar(Users_UserInterface::MODVAR_REGISTRATION_VERIFICATION_MODE, Users_UserInterface::VERIFY_USERPWD);
                }

                // IDN domains setting moving to system settings.
                System::setVar('idnnames', (bool)$this->getVar('idnnames', true));

                if (!DBUtil::changeTable('users_temp')) {
                    return '1.13';
                }

                if (!$this->upgrade117Xto210($oldVersion)) {
                    return '1.13';
                }

                EventUtil::registerPersistentModuleHandler($this->name, 'get.pending_content', array('Users_Listeners', 'pendingContentListener'));

                // Update users table for data type change of activated field.
                if (!DBUtil::changeTable('users')) {
                    return '1.13';
                }

                EventUtil::registerPersistentModuleHandler($this->name, 'get.pending_content', array('Users_Listener_PendingContent', 'pendingContentListener'));
                EventUtil::registerPersistentModuleHandler($this->name, 'user.login.veto', array('Users_Listener_ForcedPasswordChange', 'forcedPasswordChangeListener'));
                EventUtil::registerPersistentModuleHandler($this->name, 'user.logout.succeeded', array('Users_Listener_ClearUsersNamespace', 'clearUsersNamespaceListener'));
                EventUtil::registerPersistentModuleHandler($this->name, 'frontcontroller.exception', array('Users_Listener_ClearUsersNamespace', 'clearUsersNamespaceListener'));
                HookUtil::upgradeHookSubscriberBundles($this->version);
                HookUtil::upgradeHookProviderBundles($this->version);
            case '2.2.0':
                // This s the current version: add 2.2.0 --> next when appropriate
        }

        $currentModVars = $this->getVars();
        $defaultModVars = $this->getDefaultModvars();

        // Remove modvars that are no longer defined.
        foreach ($currentModVars as $modVar => $currentValue) {
            if (!array_key_exists($modVar, $defaultModVars)) {
                $this->delVar($modVar);
            }
        }

        // Add modvars that are new to the version
        foreach ($defaultModVars as $modVar => $defaultValue) {
            if (!array_key_exists($modVar, $currentModVars)) {
                $this->setVar($modVar, $defaultValue);
            }
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
     * Build and return an array of all current module variables, with their default values.
     *
     * @return array An array of all current module variables, with their default values, suitable for {@link setVars()}.
     */
    private function getDefaultModvars()
    {
        return array(
            Users_UserInterface::MODVAR_ACCOUNT_DISPLAY_GRAPHICS              => Users_UserInterface::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS,
            Users_UserInterface::MODVAR_ACCOUNT_ITEMS_PER_PAGE                => Users_UserInterface::DEFAULT_ACCOUNT_ITEMS_PER_PAGE,
            Users_UserInterface::MODVAR_ACCOUNT_ITEMS_PER_ROW                 => Users_UserInterface::DEFAULT_ACCOUNT_ITEMS_PER_ROW,
            Users_UserInterface::MODVAR_ACCOUNT_PAGE_IMAGE_PATH               => Users_UserInterface::DEFAULT_ACCOUNT_PAGE_IMAGE_PATH,
            Users_UserInterface::MODVAR_ANONYMOUS_DISPLAY_NAME                => $this->__(/* Anonymous (guest) account display name */'Guest'),
            Users_UserInterface::MODVAR_AVATAR_IMAGE_PATH                     => Users_UserInterface::DEFAULT_AVATAR_IMAGE_PATH,
            Users_UserInterface::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL              => Users_UserInterface::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
            Users_UserInterface::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD           => Users_UserInterface::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
            Users_UserInterface::MODVAR_GRAVATARS_ENABLED                     => Users_UserInterface::DEFAULT_GRAVATARS_ENABLED,
            Users_UserInterface::MODVAR_GRAVATAR_IMAGE                        => Users_UserInterface::DEFAULT_GRAVATAR_IMAGE,
            Users_UserInterface::MODVAR_HASH_METHOD                           => Users_UserInterface::DEFAULT_HASH_METHOD,
            Users_UserInterface::MODVAR_ITEMS_PER_PAGE                        => Users_UserInterface::DEFAULT_ITEMS_PER_PAGE,
            Users_UserInterface::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS         => Users_UserInterface::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
            Users_UserInterface::MODVAR_LOGIN_DISPLAY_DELETE_STATUS           => Users_UserInterface::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
            Users_UserInterface::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS         => Users_UserInterface::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
            Users_UserInterface::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS           => Users_UserInterface::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
            Users_UserInterface::MODVAR_LOGIN_METHOD                          => Users_UserInterface::DEFAULT_LOGIN_METHOD,
            Users_UserInterface::MODVAR_LOGIN_WCAG_COMPLIANT                  => Users_UserInterface::DEFAULT_LOGIN_WCAG_COMPLIANT,
            Users_UserInterface::MODVAR_MANAGE_EMAIL_ADDRESS                  => Users_UserInterface::DEFAULT_MANAGE_EMAIL_ADDRESS,
            Users_UserInterface::MODVAR_PASSWORD_MINIMUM_LENGTH               => Users_UserInterface::DEFAULT_PASSWORD_MINIMUM_LENGTH,
            Users_UserInterface::MODVAR_PASSWORD_STRENGTH_METER_ENABLED       => Users_UserInterface::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
            Users_UserInterface::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL => '',
            Users_UserInterface::MODVAR_REGISTRATION_ANTISPAM_QUESTION        => '',
            Users_UserInterface::MODVAR_REGISTRATION_ANTISPAM_ANSWER          => '',
            Users_UserInterface::MODVAR_REGISTRATION_APPROVAL_REQUIRED        => Users_UserInterface::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
            Users_UserInterface::MODVAR_REGISTRATION_APPROVAL_SEQUENCE        => Users_UserInterface::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE,
            Users_UserInterface::MODVAR_REGISTRATION_DISABLED_REASON          => __(/* registration disabled reason (default value, */'Sorry! New user registration is currently disabled.'),
            Users_UserInterface::MODVAR_REGISTRATION_ENABLED                  => Users_UserInterface::DEFAULT_REGISTRATION_ENABLED,
            Users_UserInterface::MODVAR_EXPIRE_DAYS_REGISTRATION              => Users_UserInterface::DEFAULT_EXPIRE_DAYS_REGISTRATION,
            Users_UserInterface::MODVAR_REGISTRATION_ILLEGAL_AGENTS           => '',
            Users_UserInterface::MODVAR_REGISTRATION_ILLEGAL_DOMAINS          => '',
            Users_UserInterface::MODVAR_REGISTRATION_ILLEGAL_UNAMES           => __(/* illegal username list */'root, webmaster, admin, administrator, nobody, anonymous, username'),
            Users_UserInterface::MODVAR_REGISTRATION_VERIFICATION_MODE        => Users_UserInterface::DEFAULT_REGISTRATION_VERIFICATION_MODE,
            Users_UserInterface::MODVAR_REQUIRE_UNIQUE_EMAIL                  => Users_UserInterface::DEFAULT_REQUIRE_UNIQUE_EMAIL,
        );
    }

    /**
     * Create the default data for the users module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     *
     * @return void
     */
    private function defaultdata()
    {
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(Users_UserInterface::DATETIME_FORMAT);

        // Anonymous
        $record = array(
            'uid'           => 1,
            'uname'         => 'guest',
            'email'         => '',
            'pass'          => '',
            'passreminder'  => '',
            'activated'     => Users_UserInterface::ACTIVATED_ACTIVE,
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
            'activated'     => Users_UserInterface::ACTIVATED_ACTIVE,
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
        ModUtil::dbInfoLoad('Users', 'Users');

        $serviceManager = ServiceUtil::getManager();
        $dbinfoSystem = $serviceManager['dbtables'];
        $dbinfo117X = Users_tables('1.17');
        $dbinfo210 = Users_tables('2.1.0');
        $usersOldFields = array(
            'user_theme',
            'user_viewemail',
            'storynum',
            'counter',
            'hash_method',
            'validfrom',
            'validuntil',
        );
        $usersOldFieldsDB = array(
            $dbinfo117X['users_column']['user_theme'],
            $dbinfo117X['users_column']['user_viewemail'],
            $dbinfo117X['users_column']['storynum'],
            $dbinfo117X['users_column']['counter'],
            $dbinfo117X['users_column']['hash_method'],
            $dbinfo117X['users_column']['validfrom'],
            $dbinfo117X['users_column']['validuntil']
        );

        // Upgrade the tables

        // Update the users table with new and altered fields. No fields are removed at this point, and no fields
        // are getting a new data type that is incompatible, so no need to save anything off first.
        // Also, create the users_verifychg tables at this point.
        // Merge the global dbtables with the new field information.
        $tables['users_column'] = $dbinfo210['users_column'];
        $tables['users_column_def'] = $dbinfo210['users_column_def'];
        $tables['users_verifychg'] = $dbinfo210['users_verifychg'];
        $tables['users_verifychg_column'] = $dbinfo210['users_verifychg_column'];
        $tables['users_verifychg_column_def'] = $dbinfo210['users_verifychg_column_def'];
        $serviceManager['dbtables'] = array_merge($dbinfoSystem, $tables);

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
        // Pass through the users_temp table in chunks of 100
        //  * ensure unames and email addresses are lower case,
        //  * convert pending email change request data in preparation for converstion to users_verifychg
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
                            $userArray[$key]['dynamics'] = $theDate->format(Users_UserInterface::DATETIME_FORMAT);
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
                SELECT " . Users_UserInterface::VERIFYCHGTYPE_EMAIL . " AS {$verifyColumn['changetype']},
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
        $tables = array('users_column' => array_merge($dbinfo117X['users_column'], $dbinfo210['users_column']));
        $serviceManager['dbtables'] = array_merge($dbinfoSystem, $tables);
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
                    ".Users_UserInterface::ACTIVATED_PENDING_REG." AS {$usersColumn['activated']},
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
                    AND (users.{$usersColumn['activated']} = ".Users_UserInterface::ACTIVATED_PENDING_REG.")";
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
                    AND (users.{$usersColumn['activated']} = ".Users_UserInterface::ACTIVATED_PENDING_REG.")";
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
                    AND (users.{$usersColumn['activated']} = ".Users_UserInterface::ACTIVATED_PENDING_REG.")";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }

        // Done upgrading. Let's lose some old fields and tables we no longer need.
        DBUtil::dropColumn('users', $usersOldFieldsDB);
        DBUtil::dropTable('users_temp');

        // Reset the system tables to the new table definitons, so the rest of the
        // system upgrade goes smoothly.
        $dbinfoSystem = $serviceManager['dbtables'];
        foreach ($dbinfo117X as $key => $value) {
            unset($dbinfoSystem[$key]);
        }
        foreach ($dbinfo210 as $key => $value) {
            $dbinfoSystem[$key] = $value;
        }
        $serviceManager['dbtables'] = $dbinfoSystem;

        return true;
    }
}
