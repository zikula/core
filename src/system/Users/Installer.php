<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
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

        if (!DBUtil::createTable('users_registration')) {
            return false;
        }

        if (!DBUtil::createTable('users_verifychg')) {
            return false;
        }

        // Set default values for module
        $this->defaultdata();

        ModUtil::setVar('Users', 'itemsperpage', 25);
        ModUtil::setVar('Users', 'accountdisplaygraphics', 1);
        ModUtil::setVar('Users', 'accountitemsperpage', 25);
        ModUtil::setVar('Users', 'accountitemsperrow', 5);
        ModUtil::setVar('Users', 'changepassword', 1);
        ModUtil::setVar('Users', 'changeemail', 1);
        ModUtil::setVar('Users', 'reg_allowreg', 1);
        ModUtil::setVar('Users', 'reg_verifyemail', 1);
        ModUtil::setVar('Users', 'reg_Illegalusername', 'root adm linux webmaster admin god administrator administrador nobody anonymous anonimo');
        ModUtil::setVar('Users', 'reg_Illegaldomains', '');
        ModUtil::setVar('Users', 'reg_Illegaluseragents', '');
        ModUtil::setVar('Users', 'reg_noregreasons', __('Sorry! New user registration is currently disabled.'));
        ModUtil::setVar('Users', 'reg_uniemail', 1);
        ModUtil::setVar('Users', 'reg_notifyemail', '');
        ModUtil::setVar('Users', 'reg_optitems', 0);
        ModUtil::setVar('Users', 'userimg', 'images/menu');
        ModUtil::setVar('Users', 'avatarpath', 'images/avatar');
        ModUtil::setVar('Users', 'allowgravatars', 1);
        ModUtil::setVar('Users', 'gravatarimage', 'gravatar.gif');
        ModUtil::setVar('Users', 'minage', 13);
        ModUtil::setVar('Users', 'minpass', 5);
        ModUtil::setVar('Users', 'anonymous', 'Guest');
        ModUtil::setVar('Users', 'loginviaoption', 0);
        ModUtil::setVar('Users', 'moderation', 0);
        ModUtil::setVar('Users', 'hash_method', 'sha256');
        ModUtil::setVar('Users', 'login_redirect', 1);
        ModUtil::setVar('Users', 'reg_question', '');
        ModUtil::setVar('Users', 'reg_answer', '');
        ModUtil::setVar('Users', 'use_password_strength_meter', 0);
        ModUtil::setVar('Users', 'default_authmodule', 'Users');
        ModUtil::setVar('Users', 'moderation_order', UserUtil::APPROVAL_BEFORE);

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
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            // $oldversion 1.9 and 1.10 handled by Zikula 1.2.
            case '1.11':
                // upgrade 1.11 to 1.12
                $this->upgrade_migrateSerialisedUserTemp();
            case '1.12':
                // upgrade 1.12 to 1.13
                ModUtil::setVar('Users', 'avatarpath', 'images/avatar');
                // lowercaseuname Removed in 2.0.0
                //ModUtil::setVar('Users', 'lowercaseuname', 1);
            case '1.13':
                // upgrade 1.13 to 1.14
                ModUtil::setVar('Users', 'use_password_strength_meter', 0);
            case '1.14':
                // upgrade 1.14 to 1.15
                if (ModUtil::getVar('Users', 'hash_method') == 'md5') {
                    ModUtil::setVar('Users', 'hash_method', 'sha256');
                }
            case '1.15':
                // upgrade 1.15 to 1.16
                ModUtil::delVar('Users', 'savelastlogindate');
                ModUtil::setVar('Users', 'allowgravatars', 1);
                ModUtil::setVar('Users', 'gravatarimage', 'gravatar.gif');
                if (!DBUtil::changeTable('users_temp')) {
                    return '1.15';
                }
            case '1.16':
                // upgrade 1.16 to 1.17
                // authmodules removed in 2.0.0
                //ModUtil::setVar('Users', 'authmodules', 'Users');
            case '1.17':
                // upgrade 1.17 to 1.18
                if (!DBUtil::changeTable('users')
                    || !DBUtil::changeTable('users_temp')
                    || !DBUtil::createTable('users_shadow'))
                {
                    return '1.17';
                }
            case '1.18':
                // upgrade 1.18 to 2.0.0
                if (!$this->upgrade118Xto200($oldversion)) {
                    return '1.18';
                }
            case '2.0.0':
                // Current version: add 2.0.0 --> next when appropriate
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
     */
    public function defaultdata()
    {
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(UserUtil::DATETIME_FORMAT);

        // Anonymous
        $record = array();
        $record['uid']              = '1';
        $record['uname']            = 'guest';
        $record['email']            = '';
        $record['user_regdate']     = $nowUTCStr;
        $record['pass']             = '';
        $record['ublockon']         = '0';
        $record['ublock']           = '';
        $record['theme']            = '';
        $record['activated']        = UserUtil::ACTIVATED_ACTIVE;
        DBUtil::insertObject($record, 'users', 'uid', true);

        // Admin
        $record = array();
        $record['uid']             = '2';
        $record['uname']           = 'admin';
        $record['email']           = '';
        $record['pass']            = '1$$dc647eb65e6711e155375218212b3964';
        $record['ublockon']        = '0';
        $record['ublock']          = '';
        $record['theme']           = '';
        $record['activated']       = UserUtil::ACTIVATED_ACTIVE;
        DBUtil::insertObject($record, 'users', 'uid', true);
    }

    /**
     * Migrate serialized data in users_temp.
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
     * Migrate from version 1.18 to 2.0.0
     *
     * @param string $oldversion The old version from which this upgrade is being processed.
     *
     * @return bool True on success; otherwise false.
     */
    public function upgrade118Xto200($oldversion)
    {
        LogUtil::log('UPG118-200: Beginning 1.18 to 2.0.0 upgrade.', 'DEBUG');

        // Get the dbinfo for the new version
        $funcExists = function_exists('Users_tables');
        LogUtil::log("UPG118-200: Users_tables is defined: " . ($funcExists ? 'yes' : 'no'), 'DEBUG');
        if (!$funcExists) {
            require_once 'system/Users/tables.php';
            LogUtil::log("UPG118-200: require_once: 'system/Users/tables.php'", 'DEBUG');
        }

        $dbinfoSystem = System::dbGetTables();
        $dbinfo118X = Users_tables('1.18');
        $dbinfo200 = Users_tables('2.0.0');
        $usersOldFields = array('user_theme', 'user_viewemail', 'storynum', 'counter', 'hash_method', 'validfrom', 'validuntil');
        $usersOldFieldsDB = array($dbinfo118X['users_column']['user_theme'], $dbinfo118X['users_column']['user_viewemail'],
            $dbinfo118X['users_column']['storynum'], $dbinfo118X['users_column']['counter'], $dbinfo118X['users_column']['hash_method'],
            $dbinfo118X['users_column']['validfrom'], $dbinfo118X['users_column']['validuntil']);

        $tzUTC = new DateTimeZone('UTC');
        $convertTZ = ($tzUTC->getOffset(new DateTime()) != 0);
        LogUtil::log('UPG118-200: convertTZ = ' . ($convertTZ ? 'yes' : 'no'), 'DEBUG');

        // Upgrade the tables
        // First, users table conversion.

        // Update the users table with new and altered fields. No fields are removed at this point, and no fields
        // are getting a new data type that is incompatible, so no need to save anything off first.
        // Also, create the users_registration and users_verifychg tables at this point.
        // Hack the global dbtables with the new field information.
        $GLOBALS['dbtables']['users_column'] = $dbinfo200['users_column'];
        $GLOBALS['dbtables']['users_column_def'] = $dbinfo200['users_column_def'];
        $GLOBALS['dbtables']['users_registration'] = $dbinfo200['users_registration'];
        $GLOBALS['dbtables']['users_registration_column'] = $dbinfo200['users_registration_column'];
        $GLOBALS['dbtables']['users_registration_column_def'] = $dbinfo200['users_registration_column_def'];
        $GLOBALS['dbtables']['users_verifychg'] = $dbinfo200['users_verifychg'];
        $GLOBALS['dbtables']['users_verifychg_column'] = $dbinfo200['users_verifychg_column'];
        $GLOBALS['dbtables']['users_verifychg_column_def'] = $dbinfo200['users_verifychg_column_def'];

        // Now change the tables
        //LogUtil::log('UPG118-200: changeTable(users) ' . var_export($GLOBALS['dbtables'], true), 'DEBUG');
        if (!DBUtil::changeTable('users')) {
            LogUtil::log('UPG118-200: changeTable(users) failed.', 'DEBUG');
            return false;
        }
        if (!DBUtil::createTable('users_registration')) {
            LogUtil::log('UPG118-200: createTable(users_registration) failed.', 'DEBUG');
            return false;
        }
        if (!DBUtil::createTable('users_verifychg')) {
            LogUtil::log('UPG118-200: createTable(users_verifychg) failed.', 'DEBUG');
            return false;
        }

        // First users_temp pending email verification records to users_verifychg, because the uname is changing in users.
        $utColumn = $GLOBALS['dbtables']['users_temp_column'];
        $ucColumn = $GLOBALS['dbtables']['users_verifychg_column'];
        $uColumn = $GLOBALS['dbtables']['users_column'];
        $limitNumRows = 100;
        $limitOffset = 0;
        $updated = true;
        $userCount = DBUtil::selectObjectCount('users_temp');
        while ($limitOffset < $userCount) {
            $userArray = DBUtil::selectObjectArray('users_temp', "{$utColumn['type']} = 2", '', $limitOffset, $limitNumRows, '', null, null, array('tid', 'dynamics', 'comment', 'email'));
            if (!empty($userArray) && is_array($userArray)) {
                foreach ($userArray as $key => $userObj) {
                    if (isset($userArray[$key]['dynamics']) && !empty($userArray[$key]['dynamics']) && is_numeric($userArray[$key]['dynamics'])) {
                        LogUtil::log("UPG118-200: theDate ts = @{$userArray[$key]['dynamics']}.", 'DEBUG');
                        $theDate = new DateTime("@{$userArray[$key]['dynamics']}", $tzUTC);
                        $theDate->modify('+5 days');
                        $userArray[$key]['dynamics'] = $theDate->format(UserUtil::DATETIME_FORMAT);
                    }
                    if (isset($userArray[$key]['comment']) && !empty($userArray[$key]['comment']) && is_string($userArray[$key]['comment'])) {
                        $userArray[$key]['comment'] = '1$$' . $userArray[$key]['comment'];
                    }
                    if (isset($userArray[$key]['email']) && !empty($userArray[$key]['email']) && is_string($userArray[$key]['email'])) {
                        $userArray[$key]['email'] = mb_strtolower($userArray[$key]['email']);
                    }
                }
            }
            $theCount = count($userArray);
            if (DBUtil::updateObjectArray($userArray, 'users_temp', 'tid', false)) {
                LogUtil::log("UPG118-200: Updated {$theCount} users_temp records.", 'DEBUG');
            } else {
                $updated = false;
                LogUtil::log("UPG118-200: users_temp updateObjectArray returned false - {$theCount} users in the array beginning at offset {$limitOffset}.", 'DEBUG');
                break;
            }
            $limitOffset += $limitNumRows;
        }
        if (!$updated) {
            return false;
        }
        $sql = "INSERT INTO {$dbinfo200['users_verifychg']}
                    ({$ucColumn['changetype']}, {$ucColumn['uid']}, {$ucColumn['newemail']}, {$ucColumn['verifycode']}, {$ucColumn['validuntil']})
                SELECT 2 AS {$ucColumn['changetype']},
                    users.{$uColumn['uid']} AS {$ucColumn['uid']},
                    ut.{$utColumn['email']} AS {$ucColumn['newemail']},
                    ut.{$utColumn['comment']} AS {$ucColumn['verifycode']},
                    ut.{$utColumn['dynamics']} AS {$ucColumn['validuntil']}
                FROM {$dbinfo118X['users_temp']} AS ut
                    INNER JOIN {$dbinfo200['users']} AS users ON ut.{$utColumn['uname']} = users.{$uColumn['uname']}
                WHERE ut.{$utColumn['type']} = 2";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }

        // Next, users table conversion
        // We need to convert some information over from the old users table fields, so merge the old field list into the new one.
        // The order of array_merge parameters is important here!
        $GLOBALS['dbtables']['users_column'] = array_merge($dbinfo118X['users_column'], $dbinfo200['users_column']);
        // Do the conversion in PHP we use mb_strtolower, and even if MySQL had an equivalent, there is
        // no guarantee that another supported DB platform would.
        $limitNumRows = 100;
        $limitOffset = 0;
        $updated = true;
        $userCount = DBUtil::selectObjectCount('users');
        while ($limitOffset < $userCount) {
            $userArray = DBUtil::selectObjectArray('users', 'pn_uid != 1', '', $limitOffset, $limitNumRows, '', null, null, array('uid', 'uname', 'email', 'pass', 'hash_method', 'user_regdate', 'lastlogin'));
            if (!empty($userArray) && is_array($userArray)) {
                foreach ($userArray as $key => $userObj) {
                    // force user names to lower case
                    $userArray[$key]['uname'] = mb_strtolower($userArray[$key]['uname']);
                    LogUtil::log("UPG118-200: uid: {$userObj['uid']}; uname: '{$userArray[$key]['uname']}' was '{$userObj['uname']}'", 'DEBUG');

                    // force email addresses to lower case
                    $userArray[$key]['email'] = mb_strtolower($userArray[$key]['email']);
                    LogUtil::log("UPG118-200: uid: {$userObj['uid']}; email: '{$userArray[$key]['email']}' was '{$userObj['email']}'", 'DEBUG');

                    // merge hash method for salted passwords
                    if (!empty($userArray[$key]['pass']) && (strpos($userArray[$key]['pass'], '$$') === false)) {
                        $userArray[$key]['pass'] = (isset($userArray[$key]['hash_method']) ? $userArray[$key]['hash_method'] : '') . '$$' . $userArray[$key]['pass'];
                    }
                    LogUtil::log("UPG118-200: uid: {$userObj['uid']}; hash_method: '{$userObj['hash_method']}'; pass: '{$userArray[$key]['pass']}' was '{$userObj['pass']}'", 'DEBUG');

                    // TODO - We probably cannot convert dates like this, especially for users with status inactive who are probably
                    // waiting on activation.
                    //if ($convertTZ) {
                    //    // reset regdate to UTC
                    //    $theDate = new DateTime($userArray[$key]['user_regdate']);
                    //    $theDate->setTimezone($tzUTC);
                    //    $userArray[$key]['user_regdate'] = $theDate->format('Y-m-d H:i:s');
                    //    LogUtil::log("UPG118-200: uid: {$userObj['uid']}; user_regdate: '{$userArray[$key]['user_regdate']}' was '{$userObj['user_regdate']}'", 'DEBUG');
                    //
                    //    // reset last login to UTC
                    //    $theDate = new DateTime($userArray[$key]['lastlogin']);
                    //    $theDate->setTimezone($tzUTC);
                    //    $userArray[$key]['lastlogin'] = $theDate->format('Y-m-d H:i:s');
                    //    LogUtil::log("UPG118-200: uid: {$userObj['uid']}; lastlogin: '{$userArray[$key]['lastlogin']}' was '{$userObj['lastlogin']}'", 'DEBUG');
                    //}

                    // Save some disappearing fields as attributes, just in case someone actually used them for something. But don't overwrite if there already
                    if (!isset($userArray[$key]['__ATTRIBUTES__']) || !is_array($userArray[$key]['__ATTRIBUTES__'])) {
                        $userArray[$key]['__ATTRIBUTES__'] = array();
                    }
                    foreach ($usersOldFields as $fieldName) {
                        if (($fieldName != 'hash_method') && isset($userArray[$key][$fieldName]) && !empty($userArray[$key][$fieldName]) && !isset($userArray[$key]['__ATTRIBUTES__'][$fieldName])) {
                            $userArray[$key]['__ATTRIBUTES__'][$fieldName] = $userArray[$key][$fieldName];
                        }
                    }
                }
            }
            $theCount = count($userArray);
            if (DBUtil::updateObjectArray($userArray, 'users', 'uid', false)) {
                LogUtil::log("UPG118-200: Updated {$theCount} users.", 'DEBUG');
            } else {
                $updated = false;
                LogUtil::log("UPG118-200: users updateObjectArray returned false - {$theCount} users in the array beginning at offset {$limitOffset}.", 'DEBUG');
                break;
            }
            $limitOffset += $limitNumRows;
        }
        if (!$updated) {
            return false;
        }

        if ($oldversion == '1.18') {
            // Gather any password_reminder fields set as attributes in 1.18
            $obaColumn = $dbinfoSystem['objectdata_attributes_column'];
            $urColumn = $dbinfo200['users_column'];
            $sql = "UPDATE {$dbinfo200['users']} AS u
                    INNER JOIN {$dbinfoSystem['objectdata_attributes']} AS oba
                        ON u.{$urColumn['uid']} = oba.{$obaColumn['object_id']}
                    SET u.{$urColumn['passreminder']} = oba.{$obaColumn['value']}
                    WHERE oba.{$obaColumn['object_type']} = 'users'
                        AND oba.{$obaColumn['attribute_name']} = 'password_reminder'";
            $updated = DBUtil::executeSQL($sql);
            if (!$updated) {
                return false;
            }
            $sql = "DELETE FROM {$dbinfoSystem['objectdata_attributes']}
                    WHERE {$obaColumn['object_type']} = 'users'
                        AND {$obaColumn['attribute_name']} = 'password_reminder'";
            $updated = DBUtil::executeSQL($sql);
            if (!$updated) {
                return false;
            }
        }

        // Next, users_temp conversion to users_registration
        $utColumn = $GLOBALS['dbtables']['users_temp_column'];
        $urColumn = $GLOBALS['dbtables']['users_registration_column'];
        $sql = "INSERT INTO {$dbinfo200['users_registration']}
                    ({$urColumn['uname']}, {$urColumn['email']}, {$urColumn['pass']}, {$urColumn['agreetoterms']}, {$urColumn['dynadata']})
                SELECT {$utColumn['uname']} AS {$urColumn['uname']},
                    {$utColumn['email']} AS {$urColumn['email']},
                    CONCAT({$utColumn['hash_method']}, '\$\$', {$utColumn['pass']}) AS {$urColumn['pass']},
                    1 AS {$urColumn['agreetoterms']},
                    {$utColumn['dynamics']} AS {$urColumn['dynadata']}
                FROM {$dbinfo118X['users_temp']}
                WHERE {$dbinfo118X['users_temp']}.{$utColumn['type']} = 1";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }
        // Gather any new database fields set as attributes in 1.18
        $sql = "UPDATE {$dbinfo200['users_registration']} AS ur
                INNER JOIN {$dbinfo118X['users_temp']} AS ut
                    ON ur.{$urColumn['uname']} = ut.{$utColumn['uname']}
                INNER JOIN {$dbinfoSystem['objectdata_attributes']} AS oba
                    ON ut.{$utColumn['tid']} = oba.{$obaColumn['object_id']}
                SET ur.{$urColumn['passreminder']} = oba.{$obaColumn['value']}
                WHERE ut.{$utColumn['type']} = 1
                    AND oba.{$obaColumn['object_type']} = 'users_temp'
                    AND oba.{$obaColumn['attribute_name']} = 'password_reminder'";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }
        $sql = "UPDATE {$dbinfo200['users_registration']} AS ur
                INNER JOIN {$dbinfo118X['users_temp']} AS ut
                    ON ur.{$urColumn['uname']} = ut.{$utColumn['uname']}
                INNER JOIN {$dbinfoSystem['objectdata_attributes']} AS oba
                    ON ut.{$utColumn['tid']} = oba.{$obaColumn['object_id']}
                SET ur.{$urColumn['isapproved']} = 1
                WHERE ut.{$utColumn['type']} = 1
                    AND oba.{$obaColumn['object_type']} = 'users_temp'
                    AND oba.{$obaColumn['attribute_name']} = 'pendingApproval'
                    AND oba.{$obaColumn['value']} IN ('0', 'false')";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }
        $sql = "UPDATE {$dbinfo200['users_registration']} AS ur
                INNER JOIN {$dbinfo118X['users_temp']} AS ut
                    ON ur.{$urColumn['uname']} = ut.{$utColumn['uname']}
                INNER JOIN {$dbinfoSystem['objectdata_attributes']} AS oba
                    ON ut.{$utColumn['tid']} = oba.{$obaColumn['object_id']}
                SET ur.{$urColumn['isverified']} = 1
                WHERE ut.{$utColumn['type']} = 1
                    AND oba.{$obaColumn['object_type']} = 'users_temp'
                    AND oba.{$obaColumn['attribute_name']} = 'pendingVerification'
                    AND oba.{$obaColumn['value']} IN ('0', 'false')";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }
        $sql = "DELETE FROM {$dbinfoSystem['objectdata_attributes']}
                WHERE {$obaColumn['object_type']} = 'users_temp'
                    AND {$obaColumn['attribute_name']} IN ('password_reminder', 'pendingApproval', 'pendingVerification')";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }
        $limitNumRows = 100;
        $limitOffset = 0;
        $updated = true;
        $userCount = DBUtil::selectObjectCount('users_registration');
        while ($limitOffset < $userCount) {
            $userArray = DBUtil::selectObjectArray('users_registration', '', '', $limitOffset, $limitNumRows, '', null, null, array('id', 'uname', 'email'));
            if (!empty($userArray) && is_array($userArray)) {
                foreach ($userArray as $key => $userObj) {
                    if (isset($userArray[$key]['uname']) && !empty($userArray[$key]['uname']) && is_string($userArray[$key]['uname'])) {
                        $userArray[$key]['uname'] = mb_strtolower($userArray[$key]['uname']);
                    }
                    if (isset($userArray[$key]['email']) && !empty($userArray[$key]['email']) && is_string($userArray[$key]['email'])) {
                        $userArray[$key]['email'] = mb_strtolower($userArray[$key]['email']);
                    }
                }
            }
            $theCount = count($userArray);
            if (DBUtil::updateObjectArray($userArray, 'users_registration', 'id', false)) {
                LogUtil::log("UPG118-200: Updated {$theCount} user_registration records.", 'DEBUG');
            } else {
                $updated = false;
                LogUtil::log("UPG118-200: users_registration updateObjectArray returned false - {$theCount} users in the array beginning at offset {$limitOffset}.", 'DEBUG');
                break;
            }
            $limitOffset += $limitNumRows;
        }
        if (!$updated) {
            return false;
        }

        // Next and last, convert the pending password change codes in users_shadow table over to users_verifychg, just like the pending emails
        $usColumn = $GLOBALS['dbtables']['users_shadow_column'];
        $ucColumn = $GLOBALS['dbtables']['users_verifychg_column'];
        $sql = "INSERT INTO {$dbinfo200['users_verifychg']}
                    ({$ucColumn['changetype']}, {$ucColumn['uid']}, {$ucColumn['verifycode']})
                SELECT 1 AS {$ucColumn['changetype']},
                    {$usColumn['uid']} AS {$ucColumn['uid']},
                    CONCAT({$usColumn['code_hash_method']}, '$$', {$usColumn['code']}) AS {$ucColumn['verifycode']}
                FROM {$dbinfo118X['users_shadow']}";
        $updated = DBUtil::executeSQL($sql);
        if (!$updated) {
            return false;
        }


        // Done upgrading. Let's lose some old fields and tables we no longer need.
        DBUtil::dropColumn('users', $usersOldFieldsDB);
        DBUtil::dropTable('users_temp');
        DBUtil::dropTable('users_shadow');

        // Reset $GLOBALS['dbtables'] to the new table definitons, so the rest of the
        // system upgrade goes smoothly.
        foreach ($dbinfo118X as $key => $value) {
            unset($GLOBALS['dbtables'][$key]);
        }
        foreach ($dbinfo200 as $key => $value) {
            $GLOBALS['dbtables'][$key] = $value;
        }

        // done with db changes. Now handle some final stuff.
        ModUtil::delVar('Users', 'authmodules');
        ModUtil::setVar('Users', 'default_authmodule', 'Users');

        $regVerifyEmail = ModUtil::getVar('Users', 'reg_verifyemail', UserUtil::VERIFY_NO);
        if ($regVerifyEmail == UserUtil::VERIFY_SYSTEMPWD) {
            ModUtil::setVar('Users', 'reg_verifyemail', UserUtil::VERIFY_USERPWD);
        }

        ModUtil::setVar('Users', 'moderation_order', UserUtil::APPROVAL_BEFORE);

        ModUtil::delVar('Users', 'reg_forcepwdchg');
        ModUtil::delVar('Users', 'lowercaseuname');
        ModUtil::delVar('Users', 'idnnames');
        ModUtil::delVar('Users', 'recovery_forcepwdchg');

        return true;
    }
}
