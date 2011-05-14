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
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        HookUtil::registerProviderBundles($this->version->getHookProviderBundles());

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

                // Do modvar renames and moves here, but new modvars and modvar removals are done below for all versions
                $this->setVar(Users_Constant::MODVAR_AVATAR_IMAGE_PATH, Users_Constant::MODVAR_AVATAR_IMAGE_PATH);
                // lowercaseuname Removed in 2.0.0
                //$this->setVar('lowercaseuname', 1);

                // **************************************************************
                // 1.12->1.13 is the last known upgrade of Users for Zikula 1.2.x
                // Users module 1.13 is the last known 1.2.x version released.
                // If the 1.2.x branch gets a new version, this must be updated.
                // **************************************************************
            case '1.13':
                // upgrade 1.13 to 2.2.0

                // Do modvar renames and moves here, but new modvars and modvar removals are done below for all versions

                // Check if the hash method is md5. If so, it is not used any more. Change it to the new default.
                if ($this->getVar(Users_Constant::MODVAR_HASH_METHOD, false) == 'md5') {
                    $this->setVar(Users_Constant::MODVAR_HASH_METHOD, Users_Constant::DEFAULT_HASH_METHOD);
                }

                // Convert the banned user names to a comma separated list.
                $bannedUnames = $this->getVar(Users_Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES, '');
                $bannedUnames = preg_split('/\s+/', $bannedUnames);
                $bannedUnames = implode(', ', $bannedUnames);
                $this->setVar(Users_Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES, $bannedUnames);

                // System-generated passwords are deprecated since 1.3.0. Change it to
                // User-generated passwords.
                $regVerifyEmail = $this->getVar(Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE, Users_Constant::VERIFY_NO);
                if ($regVerifyEmail == Users_Constant::VERIFY_SYSTEMPWD) {
                    $this->setVar(Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE, Users_Constant::VERIFY_USERPWD);
                }

                // IDN domains setting moving to system settings.
                System::setVar('idnnames', (bool)$this->getVar('idnnames', true));

                // Minimum age is moving to Legal
                ModUtil::setVar('Legal', 'minimumAge', $this->getVar('minage', 0));

                if (!$this->upgrade113XTablesTo220Tables($oldVersion)) {
                    return '1.13';
                }

                EventUtil::registerPersistentModuleHandler($this->name, 'get.pending_content', array('Users_Listener_PendingContent', 'pendingContentListener'));
                EventUtil::registerPersistentModuleHandler($this->name, 'user.login.veto', array('Users_Listener_ForcedPasswordChange', 'forcedPasswordChangeListener'));
                EventUtil::registerPersistentModuleHandler($this->name, 'user.logout.succeeded', array('Users_Listener_ClearUsersNamespace', 'clearUsersNamespaceListener'));
                EventUtil::registerPersistentModuleHandler($this->name, 'frontcontroller.exception', array('Users_Listener_ClearUsersNamespace', 'clearUsersNamespaceListener'));
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
                HookUtil::registerProviderBundles($this->version->getHookProviderBundles());
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
            Users_Constant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS              => Users_Constant::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS,
            Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_PAGE                => Users_Constant::DEFAULT_ACCOUNT_ITEMS_PER_PAGE,
            Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_ROW                 => Users_Constant::DEFAULT_ACCOUNT_ITEMS_PER_ROW,
            Users_Constant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH               => Users_Constant::DEFAULT_ACCOUNT_PAGE_IMAGE_PATH,
            Users_Constant::MODVAR_ANONYMOUS_DISPLAY_NAME                => $this->__(/* Anonymous (guest) account display name */'Guest'),
            Users_Constant::MODVAR_AVATAR_IMAGE_PATH                     => Users_Constant::DEFAULT_AVATAR_IMAGE_PATH,
            Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL              => Users_Constant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
            Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD           => Users_Constant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
            Users_Constant::MODVAR_EXPIRE_DAYS_REGISTRATION              => Users_Constant::DEFAULT_EXPIRE_DAYS_REGISTRATION,
            Users_Constant::MODVAR_GRAVATARS_ENABLED                     => Users_Constant::DEFAULT_GRAVATARS_ENABLED,
            Users_Constant::MODVAR_GRAVATAR_IMAGE                        => Users_Constant::DEFAULT_GRAVATAR_IMAGE,
            Users_Constant::MODVAR_HASH_METHOD                           => Users_Constant::DEFAULT_HASH_METHOD,
            Users_Constant::MODVAR_ITEMS_PER_PAGE                        => Users_Constant::DEFAULT_ITEMS_PER_PAGE,
            Users_Constant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS         => Users_Constant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
            Users_Constant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS           => Users_Constant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
            Users_Constant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS         => Users_Constant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
            Users_Constant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS           => Users_Constant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
            Users_Constant::MODVAR_LOGIN_METHOD                          => Users_Constant::DEFAULT_LOGIN_METHOD,
            Users_Constant::MODVAR_LOGIN_WCAG_COMPLIANT                  => Users_Constant::DEFAULT_LOGIN_WCAG_COMPLIANT,
            Users_Constant::MODVAR_MANAGE_EMAIL_ADDRESS                  => Users_Constant::DEFAULT_MANAGE_EMAIL_ADDRESS,
            Users_Constant::MODVAR_PASSWORD_MINIMUM_LENGTH               => Users_Constant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
            Users_Constant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED       => Users_Constant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
            Users_Constant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL => '',
            Users_Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION        => '',
            Users_Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER          => '',
            Users_Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED        => Users_Constant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
            Users_Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE        => Users_Constant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE,
            Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN               => Users_Constant::DEFAULT_REGISTRATION_AUTO_LOGIN,
            Users_Constant::MODVAR_REGISTRATION_DISABLED_REASON          => $this->__(/* registration disabled reason (default value, */'Sorry! New user registration is currently disabled.'),
            Users_Constant::MODVAR_REGISTRATION_ENABLED                  => Users_Constant::DEFAULT_REGISTRATION_ENABLED,
            Users_Constant::MODVAR_REGISTRATION_ILLEGAL_AGENTS           => '',
            Users_Constant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS          => '',
            Users_Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES           => $this->__(/* illegal username list */'root, webmaster, admin, administrator, nobody, anonymous, username'),
            Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE        => Users_Constant::DEFAULT_REGISTRATION_VERIFICATION_MODE,
            Users_Constant::MODVAR_REQUIRE_UNIQUE_EMAIL                  => Users_Constant::DEFAULT_REQUIRE_UNIQUE_EMAIL,
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
        $nowUTCStr = $nowUTC->format(Users_Constant::DATETIME_FORMAT);

        // Anonymous
        $record = array(
            'uid'           => 1,
            'uname'         => 'guest',
            'email'         => '',
            'pass'          => '',
            'passreminder'  => '',
            'activated'     => Users_Constant::ACTIVATED_ACTIVE,
            'approved_date' => '1970-01-01 00:00:00',
            'approved_by'   => 0,
            'user_regdate'  => '1970-01-01 00:00:00',
            'lastlogin'     => '1970-01-01 00:00:00',
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
            'activated'     => Users_Constant::ACTIVATED_ACTIVE,
            'approved_date' => $nowUTCStr,
            'approved_by'   => 2,
            'user_regdate'  => $nowUTCStr,
            'lastlogin'     => '1970-01-01 00:00:00',
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
     * Migrate from version 1.13 to 2.2.0
     *
     * @param string $oldversion The old version from which this upgrade is being processed.
     *
     * @return bool True on success; otherwise false.
     */
    public function upgrade113XTablesTo220Tables($oldversion)
    {
        if (!DBUtil::changeTable('users_temp')) {
            return false;
        }

        // Get the dbinfo for the new version
        ModUtil::dbInfoLoad('Users', 'Users');

        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(Users_Constant::DATETIME_FORMAT);

        $serviceManager = ServiceUtil::getManager();
        $dbinfoSystem = $serviceManager['dbtables'];
        $dbinfo113X = Users_tables('1.13');
        $dbinfo220 = Users_tables('2.2.0');
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
            $dbinfo113X['users_column']['user_theme'],
            $dbinfo113X['users_column']['user_viewemail'],
            $dbinfo113X['users_column']['storynum'],
            $dbinfo113X['users_column']['counter'],
            $dbinfo113X['users_column']['hash_method'],
            $dbinfo113X['users_column']['validfrom'],
            $dbinfo113X['users_column']['validuntil']
        );

        // Upgrade the tables

        // Update the users table with new and altered fields. No fields are removed at this point, and no fields
        // are getting a new data type that is incompatible, so no need to save anything off first.
        // Also, create the users_verifychg tables at this point.
        // Merge the global dbtables with the new field information.
        $tables['users_column'] = $dbinfo220['users_column'];
        $tables['users_column_def'] = $dbinfo220['users_column_def'];
        $tables['users_verifychg'] = $dbinfo220['users_verifychg'];
        $tables['users_verifychg_column'] = $dbinfo220['users_verifychg_column'];
        $tables['users_verifychg_column_def'] = $dbinfo220['users_verifychg_column_def'];
        $serviceManager['dbtables'] = array_merge($dbinfoSystem, $tables);

        // Now change the tables
        if (!DBUtil::changeTable('users')) {
            return false;
        }
        if (!DBUtil::createTable('users_verifychg')) {
            return false;
        }

        // First users_temp pending email verification records to users_verifychg.
        $tempColumn = $dbinfo113X['users_temp_column'];
        $verifyColumn = $dbinfo220['users_verifychg_column'];
        $usersColumn = $dbinfo220['users_column'];

        $legalModInfo = ModUtil::getInfoFromName('Legal');
        if (($legalModInfo['state'] == ModUtil::STATE_ACTIVE) || ($legalModInfo['state'] == ModUtil::STATE_UPGRADED)) {
            $legalModuleActive = true;
            $termsOfUseActive = ModUtil::getVar('Legal', 'termsofuse', false);
            $privacyPolicyActive = ModUtil::getVar('Legal', 'privacypolicy', false);
            $agePolicyActive = ($this->getVar('minage', 0) > 0);
        } else {
            $legalModuleActive = false;
        }

        // Next, users table conversion
        // We need to convert some information over from the old users table fields, so merge the old field list into
        // the new one. The order of array_merge parameters is important here!
        $tables = array('users_column' => array_merge($dbinfo113X['users_column'], $dbinfo220['users_column']));
        $serviceManager['dbtables'] = array_merge($dbinfoSystem, $tables);
        // Do the conversion in PHP we use mb_strtolower, and even if MySQL had an equivalent, there is
        // no guarantee that another supported DB platform would.
        $limitNumRows = 100;
        $limitOffset = 0;
        $updated = true;
        $userCount = DBUtil::selectObjectCount('users');
        while ($limitOffset < $userCount) {
            $userArray = DBUtil::selectObjectArray('users', "{$usersColumn['uid']} != 1", '', $limitOffset, $limitNumRows,
                '', null, null, array('uid', 'uname', 'email', 'pass', 'hash_method', 'user_regdate', 'lastlogin', 'approved_by', 'approved_date'));
            if (!empty($userArray) && is_array($userArray)) {
                foreach ($userArray as $key => $userObj) {
                    // force user names and emails to lower case
                    $userArray[$key]['uname'] = mb_strtolower($userArray[$key]['uname']);
                    $userArray[$key]['email'] = mb_strtolower($userArray[$key]['email']);

                    if ($userArray[$key]['user_regdate'] == '1970-01-01 00:00:00') {
                        $userArray[$key]['user_regdate'] = $nowUTCStr;
                        $userArray[$key]['approved_date'] = $nowUTCStr;
                    } else {
                        $userArray[$key]['approved_date'] = $userArray[$key]['user_regdate'];
                    }
                    $userArray[$key]['approved_by'] = 2;

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

                    if ($legalModuleActive && ($userArray[$key]['uid'] > 2)) {
                        $userRegDateTime = new DateTime($userArray[$key]['user_regdate'], new DateTimeZone('UTC'));
                        $policyDateTimeStr = $userRegDateTime->format(DATE_ISO8601);

                        if ($termsOfUseActive) {
                            $userArray[$key]['__ATTRIBUTES__']['_Legal_termsOfUseAccepted'] = $policyDateTimeStr;
                        }
                        if ($privacyPolicyActive) {
                            $userArray[$key]['__ATTRIBUTES__']['_Legal_privacyPolicyAccepted'] = $policyDateTimeStr;
                        }
                        if ($agePolicyActive) {
                            $userArray[$key]['__ATTRIBUTES__']['_Legal_agePolicyConfirmed'] = $policyDateTimeStr;
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

        $limitNumRows = 100;
        $limitOffset = 0;
        $updated = true;
        $userCount = DBUtil::selectObjectCount('users_temp');
        // Pass through the users_temp table in chunks of 100
        //  * ensure unames and email addresses are lower case,
        while ($limitOffset < $userCount) {
            $userTempArray = DBUtil::selectObjectArray('users_temp', '', '', $limitOffset, $limitNumRows, '', null, null,
                array('tid', 'type', 'uname', 'email', 'pass', 'hash_method', 'dynamics', 'comment'));
            $userArray = array();
            if (!empty($userTempArray) && is_array($userTempArray)) {
                foreach ($userTempArray as $key => $userTempOpj) {
                    // type == 1: User registration pending approval (moderation)
                    if ($userTempArray[$key]['type'] == 1) {
                        $userObj = array();

                        // Ensure uname and email are lower case
                        $userObj['uname'] = mb_strtolower($userTempArray[$key]['uname']);
                        $userObj['email'] = mb_strtolower($userTempArray[$key]['email']);

                        // Convert pass to salted pass with embedded hash method, leave salt blank
                        $userObj['pass'] = $userTempArray[$key]['hash_method'] . '$$' . $userTempArray[$key]['pass'];

                        $userObj['approved_by'] = 0;
                        $userObj['activated'] = Users_Constant::ACTIVATED_PENDING_REG;

                        if (!empty($userTempArray[$key]['dynamics'])) {
                            $userObj['__ATTRIBUTES__'] = unserialize($userTempArray[$key]['dynamics']);
                        } else {
                            $userObj['__ATTRIBUTES__'] = array();
                        }

                        if (isset($userObj['dynamics']) && !empty($userObj['dynamics'])) {
                            if (DataUtil::is_serialized($userObj['dynamics'])) {
                                $dynamics = @unserialize($userObj['dynamics']);
                                if (!empty($dynamics) && is_array($dynamics)) {
                                    foreach ($dynamics as $key => $value) {
                                        $userObj['__ATTRIBUTES__'][$key] = $value;
                                    }
                                }
                            }
                        }

                        $userObj['__ATTRIBUTES__']['_Users_isVerified'] = 0;

                        if ($legalModuleActive) {
                            $userRegDateTime = new DateTime($userArray[$key]['user_regdate'], new DateTimeZone('UTC'));
                            $policyDateTimeStr = $userRegDateTime->format(DATE_ISO8601);

                            if ($termsOfUseActive) {
                                $userObj['__ATTRIBUTES__']['_Legal_termsOfUseAccepted'] = $policyDateTimeStr;
                            }
                            if ($privacyPolicyActive) {
                                $userObj['__ATTRIBUTES__']['_Legal_privacyPolicyAccepted'] = $policyDateTimeStr;
                            }
                            if ($agePolicyActive) {
                                $userObj['__ATTRIBUTES__']['_Legal_agePolicyConfirmed'] = $policyDateTimeStr;
                            }
                        }

                        $userArray[] = $userObj;
                    } else {
                        throw new Zikula_Exception_Fatal($this->__f('Unknown users_temp record type: %1$s', array($userTempArray[$key]['type'])));
                    }
                }
            }
            if (!DBUtil::insertObjectArray($userArray, 'users', 'uid', false)) {
                $updated = false;
                break;
            }
            $limitOffset += $limitNumRows;
        }
        if (!$updated) {
            return false;
        }

        // Done upgrading. Let's lose some old fields and tables we no longer need.
        DBUtil::dropColumn('users', $usersOldFieldsDB);
        DBUtil::dropTable('users_temp');

        // Reset the system tables to the new table definitons, so the rest of the
        // system upgrade goes smoothly.
        $dbinfoSystem = $serviceManager['dbtables'];
        foreach ($dbinfo113X as $key => $value) {
            unset($dbinfoSystem[$key]);
        }
        foreach ($dbinfo220 as $key => $value) {
            $dbinfoSystem[$key] = $value;
        }
        $serviceManager['dbtables'] = $dbinfoSystem;

        // Update users table for data type change of activated field.
        if (!DBUtil::changeTable('users')) {
            return false;
        }

        return true;
    }
}
