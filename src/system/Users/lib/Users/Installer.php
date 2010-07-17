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
             ->setVar('login_displayinactive', false)
             ->setVar('login_displayverify', false)
             ->setVar('login_displayapprove', false);

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
                // upgrade 1.17 to 1.18
                if (!DBUtil::changeTable('users')
                    || !DBUtil::changeTable('users_temp'))
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
        // Get the dbinfo for the new version
        $funcExists = function_exists('Users_tables');
        if (!$funcExists) {
            require_once 'system/Users/tables.php';
        }

        $dbinfoSystem = DBUtil::getTables();
        $dbinfo118X = Users_tables('1.18');
        $dbinfo200 = Users_tables('2.0.0');
        $usersOldFields = array('user_theme', 'user_viewemail', 'storynum', 'counter', 'hash_method', 'validfrom', 'validuntil');
        $usersOldFieldsDB = array($dbinfo118X['users_column']['user_theme'], $dbinfo118X['users_column']['user_viewemail'],
            $dbinfo118X['users_column']['storynum'], $dbinfo118X['users_column']['counter'], $dbinfo118X['users_column']['hash_method'],
            $dbinfo118X['users_column']['validfrom'], $dbinfo118X['users_column']['validuntil']);

        $tzUTC = new DateTimeZone('UTC');
        $convertTZ = ($tzUTC->getOffset(new DateTime()) != 0);

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
        if (!DBUtil::changeTable('users')) {
            return false;
        }
        if (!DBUtil::createTable('users_registration')) {
            return false;
        }
        if (!DBUtil::createTable('users_verifychg')) {
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
            if (!DBUtil::updateObjectArray($userArray, 'users_temp', 'tid', false)) {
                $updated = false;
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

                    // force email addresses to lower case
                    $userArray[$key]['email'] = mb_strtolower($userArray[$key]['email']);

                    // merge hash method for salted passwords
                    if (!empty($userArray[$key]['pass']) && (strpos($userArray[$key]['pass'], '$$') === false)) {
                        $userArray[$key]['pass'] = (isset($userArray[$key]['hash_method']) ? $userArray[$key]['hash_method'] : '') . '$$' . $userArray[$key]['pass'];
                    }

                    // TODO - We probably cannot convert dates like this, especially for users with status inactive who are probably
                    // waiting on activation.
                    //if ($convertTZ) {
                    //    // reset regdate to UTC
                    //    $theDate = new DateTime($userArray[$key]['user_regdate']);
                    //    $theDate->setTimezone($tzUTC);
                    //    $userArray[$key]['user_regdate'] = $theDate->format('Y-m-d H:i:s');
                    //
                    //    // reset last login to UTC
                    //    $theDate = new DateTime($userArray[$key]['lastlogin']);
                    //    $theDate->setTimezone($tzUTC);
                    //    $userArray[$key]['lastlogin'] = $theDate->format('Y-m-d H:i:s');
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

        if ($oldversion == '1.18') {
            // Gather any password_reminder fields set as attributes in 1.18
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
            if (!DBUtil::updateObjectArray($userArray, 'users_registration', 'id', false)) {
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

        // Reset $GLOBALS['dbtables'] to the new table definitons, so the rest of the
        // system upgrade goes smoothly.
        foreach ($dbinfo118X as $key => $value) {
            unset($GLOBALS['dbtables'][$key]);
        }
        foreach ($dbinfo200 as $key => $value) {
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
             ->setVar('login_displayinactive', false)
             ->setVar('login_displayverify', false)
             ->setVar('login_displayapprove', false);

        $this->delVar('reg_forcepwdchg');
        $this->delVar('lowercaseuname');
        $this->delVar('idnnames');
        $this->delVar('recovery_forcepwdchg');

        return true;
    }
}
