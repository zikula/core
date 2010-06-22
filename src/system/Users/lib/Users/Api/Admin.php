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
 * The Admin API provides administrative system-level and database-level functions for modules;
 * this class provides those functions for the Users module.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Api_Admin extends Zikula_Api
{
    /**
     * Find users.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['uname']         (string) A fragment of a user name on which to search using an SQL LIKE clause. The user name will be surrounded by wildcards.
     *                    $args['ugroup']        (int)    A group id in which to search (only users who are members of the specified group are returned).
     *                    $args['email']         (string) A fragment of an e-mail address on which to search using an SQL LIKE clause. The e-mail address will be surrounded by
     *                                                      wildcards.
     *                    $args['regdateafter']  (string) An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date after the date specified
     *                                                      will be returned.
     *                    $args['regdatebefore'] (string) An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date before the date specified
     *                                                      will be returned.
     *                    $args['dynadata']      (array)  An array of search values to be passed to the designated profile module. Only those user records also satisfying the profile
     *                                                      module's search of its data are returned.
     *                    $args['condition']     (string) An SQL condition for finding users; overrides all other parameters.
     *
     * @return mixed array of items if succcessful, false otherwise
     */
    public function findUsers($args)
    {
        // Need read access to call this function
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return false;
        }

        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));

        $pntable     = System::dbGetTables();
        $userstable  = $pntable['users'];
        $userscolumn = $pntable['users_column'];

        // Set query conditions (unless some one else sends a hardcoded one)
        if (!isset($args['condition']) || !$args['condition']) {
            // process all of these in one loop
            $args['condition'] = $userscolumn['uname'] . " != 'Anonymous'";
            $vars = array('uname', 'email');
            foreach ($vars as $var) {
                if (isset($args[$var]) && !empty($args[$var])) {
                    $args['condition'] .= ' AND '.$userscolumn[$var].' LIKE \'%'.DataUtil::formatForStore($args[$var]).'%\'';
                }
            }

            // do the rest manually
            if (isset($args['ugroup']) && $args['ugroup']) {
                $guids = UserUtil::getUsersForGroup($args['ugroup']);
                if (!empty($guids)) {
                    $args['condition'] .= " AND $userscolumn[uid] IN (";
                    foreach ($guids as $uid) {
                        $args['condition'] .= DataUtil::formatForStore($uid) . ',';
                    }
                    $args['condition'] .= '0)';
                }
            }
            if (isset($args['regdateafter']) && $args['regdateafter']) {
                $args['condition'] .= " AND $userscolumn[user_regdate] > '".DataUtil::formatForStore($args['regdateafter'])."'";
            }
            if (isset($args['regdatebefore']) && $args['regdatebefore']) {
                $args['condition'] .= " AND $userscolumn[user_regdate] < '".DataUtil::formatForStore($args['regdatebefore'])."'";
            }

            if ($useProfileMod) {
                // Check for attributes
                if (isset($args['dynadata']) && is_array($args['dynadata'])) {
                    $uids = ModUtil::apiFunc($profileModule, 'user', 'searchDynadata', array('dynadata' => $args['dynadata']));
                    if (is_array($uids) && !empty($uids)) {
                        $args['condition'] .= " AND $userscolumn[uid] IN (";
                        foreach ($uids as $uid) {
                            $args['condition'] .= DataUtil::formatForStore($uid) . ',';
                        }
                        $args['condition'] .= '0)';
                    }
                }
            }
        }

        $where = 'WHERE ' . $args['condition'];

        $permFilter = array();
        $permFilter[] = array('realm' => 0,
                          'component_left'   => 'Users',
                          'component_middle' => '',
                          'component_right'  => '',
                          'instance_left'    => 'uname',
                          'instance_middle'  => '',
                          'instance_right'   => 'uid',
                          'level'            => ACCESS_READ);
        $objArray = DBUtil::selectObjectArray('users', $where, 'uname', null, null, null, $permFilter);

        return $objArray;
    }

    /**
     * Save a new user record.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['uname']              (string) The user name to store on the new user record.
     *                    $args['email']              (string) The e-mail address to store on the new user record.
     *                    $args['pass']               (string) The new password to store on the new user record.
     *                    $args['vpass']              (string) A verification of the new password to store on the new user record.
     *                    $args['dynadata']           (array)  An array of additional information to be stored by the designated profile module, and linked to the newly created
     *                                                           user account.
     *                    $args['access_permissions'] (array)  Used only for 'edit' operations; an array of group ids to which the user should belong.
     *
     * @return bool true if successful, false otherwise.
     */
    public function saveUser($args)
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
            return false;
        }

        // Checking for necessary basics
        if (!isset($args['uid']) || empty($args['uid']) || !isset($args['uname']) || empty($args['uname']) ||
            !isset($args['email'])  || empty($args['email'])) {
            return LogUtil::registerError($this->__('Error! One or more required fields were left blank or incomplete.'));
        }

        $checkpass = false;
        if (isset($args['pass']) && !empty($args['pass'])) {
            $checkpass = true;
        }

        if ($checkpass) {
            if (isset($args['pass']) && isset($args['vpass']) && $args['pass'] !== $args['vpass']) {
                return LogUtil::registerError($this->__('Error! You did not enter the same password in each password field. '
                    . 'Please enter the same password once in each password field (this is required for verification).'));
            }

            $pass  = $args['pass'];
            $vpass = $args['vpass'];

            $minpass = ModUtil::getVar('Users', 'minpass');
            if (empty($pass) || strlen($pass) < $minpass) {
                return LogUtil::registerError($this->_fn('Your password must be at least %s character long', 'Your password must be at least %s characters long', $minpass, $minpass));
            }
            if (!empty($pass) && $pass) {
                $args['pass'] = UserUtil::getHashedPassword($pass);
            }
        } else {
            unset($args['pass']);
        }

        // process the dynamic data
        $dynadata = isset($args['dynadata']) ? $args['dynadata'] : array();

        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));
        if ($useProfileMod && $dynadata) {

            $checkrequired = ModUtil::apiFunc($profileModule, 'user', 'checkRequired',
                                          array('dynadata' => $dynadata));

            if ($checkrequired['result'] == true) {
                return LogUtil::registerError($this->__f('Error! A required item is missing from your profile information (%s).', $checkrequired['translatedFieldsStr']));
            }
        }

        if (isset($dynadata['publicemail']) && !empty($dynadata['publicemail'])) {
            $dynadata['publicemail'] = preg_replace('/[^a-zA-Z0-9_@.-]/', '', $dynadata['publicemail']);
        }

        if (isset($dynadata['url']) && !empty($dynadata['url'])) {
            $dynadata['url'] = preg_replace('/[^a-zA-Z0-9_@.&#?;:\/-]/', '', $dynadata['url']);
            if (!preg_match('/^http:\/\/[0-9a-z]+/i', $dynadata['url'])) {
                $dynadata['url'] = "http://" . $dynadata['url'];
            }
        }

        $args['dynadata'] = $dynadata;

        // call the profile manager to handle dynadata if needed
        if ($useProfileMod) {
            $adddata = ModUtil::apiFunc($profileModule, 'user', 'insertDyndata', $args);
            if (is_array($adddata)) {
                $args = array_merge($adddata, $args);
            }
        }

        DBUtil::updateObject($args, 'users', '', 'uid');

        // Fixing a high numitems to be sure to get all groups
        $groups = ModUtil::apiFunc('Groups', 'user', 'getAll', array('numitems' => 1000));

        foreach ($groups as $group) {
            if (in_array($group['gid'], $args['access_permissions'])) {
                // Check if the user is already in the group
                $useringroup = false;
                $usergroups  = ModUtil::apiFunc('Groups', 'user', 'getUserGroups', array('uid' => $args['uid']));
                if ($usergroups) {
                    foreach ($usergroups as $usergroup) {
                        if ($group['gid'] == $usergroup['gid']) {
                            $useringroup = true;
                            break;
                        }
                    }
                }
                // User is not in this group
                if ($useringroup == false) {
                    ModUtil::apiFunc('Groups', 'admin', 'addUser', array('gid' => $group['gid'], 'uid' => $args['uid']));
                }
            } else {
                // We don't need to do a complex check, if the user is not in the group, the SQL will not return
                // an error anyway.
                if (SecurityUtil::checkPermission('Groups::', "$group[gid]::", ACCESS_EDIT)) {
                    ModUtil::apiFunc('Groups', 'admin', 'removeUser', array('gid' => $group['gid'], 'uid' => $args['uid']));
                }
            }
        }

        // Let other modules know we have updated an item
        $this->callHooks('item', 'update', $args['uid'], array('module' => 'Users'));

        return true;
    }

    /**
     * Delete one or more user account records.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['uid'] (numeric|array) A single (int) user id, or an array of user ids to delete.
     *
     * @return bool True if successful, false otherwise.
     */
    public function deleteUser($args)
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
            return false;
        }

        if (!isset($args['uid']) || !(is_numeric($args['uid']) || is_array($args['uid']))) {
            return LogUtil::registerError("Error! Illegal argument were passed to 'deleteuser'");
        }

        // ensure we always have an array
        if (!is_array($args['uid'])) {
            $args['uid'] = array(0 => $args['uid']);
        }

        foreach ($args['uid'] as $id) {
            if (!DBUtil::deleteObjectByID('group_membership', $id, 'uid')) {
                return false;
            }

            if (!DBUtil::deleteObjectByID('users', $id, 'uid')) {
                return false;
            }

            // Let other modules know we have deleted an item
            $this->callHooks('item', 'delete', $id, array('module' => 'Users'));
        }

        return $args['uid'];
    }

    /**
     * Get available admin panel links.
     *
     * @return array Array of admin links.
     */
    public function getLinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'view'), 'text' => $this->__('Users list'), 'class' => 'z-icon-es-list');
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $pending = ModUtil::apiFunc('Users', 'registration', 'countAll');
            if ($pending) {
                $links[] = array('url' => ModUtil::url('Users', 'admin', 'viewRegistrations'), 'text' => $this->__('Pending registrations') . ' ( '.DataUtil::formatForDisplay($pending).' )');
            }
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'newUser'), 'text' => $this->__('Create new user'), 'class' => 'z-icon-es-new');
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'import'), 'text' => $this->__('Import users'), 'class' => 'z-icon-es-import');
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'export'), 'text' => $this->__('Export users'), 'class' => 'z-icon-es-export');
        }
        if (SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'search'), 'text' => $this->__('Find and e-mail users'), 'class' => 'z-icon-es-mail');
        } else if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'search'), 'text' => $this->__('Find users'), 'class' => 'z-icon-es-search');
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'modifyConfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));
        if ($useProfileMod) {
            // Make sure there are links for the user to see in the submenu. Don't try
            // to guess at what permission level the profule module might have for its
            // links in its getlinks function. Just try to get the links and see if
            // it is not empty. If it is not empty, then the user has permissions for
            // at least one function in there (maybe more).
            $profileAdminLinks = ModUtil::apiFunc($profileModule, 'admin', 'getLinks');
            if (!empty($profileAdminLinks)) {
                if (ModUtil::getName() == 'Users') {
                    $links[] = array('url' => 'javascript:showdynamicsmenu()', 'text' => $this->__('Account panel manager'), 'class' => 'z-icon-es-profile');
                } else {
                    $links[] = array('url' => ModUtil::url($profileModule, 'admin', 'main'), 'text' => $this->__('Account panel manager'), 'class' => 'z-icon-es-profile');
                }
            }
        }

        return $links;
    }

    /**
     * Retrieve an array of user records whose field specified by the key parameter match one of the values specified in the valuesArray parameter.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['key']      (string) The field to be searched, typically 'uname' or 'email'.
     *                    $args['keyValue'] (array)  An array containing the values to be matched.
     *
     * @return array|bool An array of user records indexed by user name, each whose key field matches one value in the valueArray; false on error.
     */
    public function checkMultipleExistence($args)
    {
        // Need read access to call this function
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return false;
        }

        $pntable = System::dbGetTables();
        $userscolumn = $pntable['users_column'];

        $valuesArray = $args['valuesArray'];
        $key = $args['key'];

        $where = '';
        foreach ($valuesArray as $value) {
            $where .=  $userscolumn[$key] . "='" . $value . "' OR ";
        }

        $where = substr($where, 0, -3);

        $items = DBUtil::selectObjectArray ('users', $where, '', '-1', '-1', 'uname');

        if ($items === false) {
            return false;
        }

        return $items;
    }

    /**
     * Add new user accounts from the import process.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['importValues'] (array) An array of information used to create new user records.
     *
     * @return bool True on success; false otherwise.
     */
    public function createImport($args)
    {
        // Need add access to call this function
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return false;
        }

        $importValues = $args['importValues'];

        if (empty($importValues)) {
            return false;
        }

        $pntable = System::dbGetTables();
        $userstable = $pntable['users'];
        $userscolumn = $pntable['users_column'];

        $createUsersSQL = "INSERT INTO " . $userstable . "($userscolumn[uname],$userscolumn[email],$userscolumn[activated],$userscolumn[pass],$userscolumn[hash_method]) VALUES ";

        // construct a sql statement with all the inserts to avoid to much database connections
        foreach ($importValues as $value) {
            $value = DataUtil::formatForStore($value);
            $passwordHashinfo = UserUtil::getHashedPassword($value['pass']);
            $createUsersSQL .= "('" . trim(mb_strtolower($value['uname'])) . "','" . trim($value['email']) . "', {$value['activated']}, '{$passwordHashinfo['hash']}', {$passwordHashinfo['hashMethodCode']}),";
            $usersArray[] = $value['uname'];
        }

        $createUsersSQL = substr($createUsersSQL, 0 , -1) . ';';

        // execute sql to create users
        $result = DBUtil::executeSQL($createUsersSQL);
        if (!$result) {
            return false;
        }

        // get users. We need the users identities set them into their groups
        $usersInDB = ModUtil::apiFunc('Users', 'admin', 'checkMultipleExistence',
                                      array('valuesArray' => $usersArray,
                                            'key' => 'uname'));
        if (!$usersInDB) {
            return LogUtil::registerError($this->__('Error! The users have been created but something has failed trying to get them from the database. '
                . 'Now all these users do not have group.'));
        }

        // get available groups
        $allGroups = ModUtil::apiFunc('Groups','user','getAll');

        // create an array with the groups identities where the user can add other users
        $allGroupsArray = array();
        foreach ($allGroups as $group) {
            if (SecurityUtil::checkPermission('Groups::', $group['name'] . '::' . $group['gid'], ACCESS_EDIT)) {
                $allGroupsArray[] = $group['gid'];
            }
        }

        $groupstable = $pntable['group_membership'];
        $groupscolumn = $pntable['group_membership_column'];

        $addUsersToGroupsSQL = "INSERT INTO " . $groupstable . "({$groupscolumn['uid']},{$groupscolumn['gid']}) VALUES ";

        // construct a sql statement with all the inserts to avoid to much database connections
        foreach ($importValues as $value) {
            $groupsArray = explode('|', $value['groups']);
            foreach ($groupsArray as $group) {
                if (in_array(trim($group), $allGroupsArray)) {
                    $addUsersToGroupsSQL .= "(" . $usersInDB[$value['uname']]['uid'] . "," . $group . "),";
                }
            }
        }

        $addUsersToGroupsSQL = substr($addUsersToGroupsSQL, 0 , -1) . ';';

        // execute sql to create users
        $result = DBUtil::executeSQL($addUsersToGroupsSQL);
        if (!$result) {
            return LogUtil::registerError($this->__('Error! The users have been created but something has failed while trying to add the users to their groups. '
                . 'Now all these users do not have group.'));
        }

        // check if module Mailer is active
        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName('Mailer'));
        if ($modinfo['state'] == 3) {
            $sitename  = System::getVar('sitename');
            $siteurl   = System::getBaseUrl();
            $pnRender = Renderer::getInstance('Users', false);
            $pnRender->assign('sitename', $sitename);
            $pnRender->assign('siteurl', $siteurl);
            foreach ($importValues as $value) {
                if ($value['activated'] == UserUtil::ACTIVATED_ACTIVE && $value['sendMail'] == 1) {
                    $pnRender->assign('email', $value['email']);
                    $pnRender->assign('uname', $value['uname']);
                    $pnRender->assign('pass', $value['pass']);
                    $message = $pnRender->fetch('users_adminapi_notifyemail.htm');
                    $subject = $this->__f('Password for %1$s from %2$s', array($value['uname'], $sitename));
                    if (!ModUtil::apiFunc('Mailer', 'user', 'sendMessage',
                                        array('toaddress' => $value['email'],
                                              'subject' => $subject,
                                              'body' => $message,
                                              'html' => true)))
                    {
                        LogUtil::registerError($this->__f('Error! A problem has occurred while sending e-mail messages. The error happened trying to send a message to the user %s. '
                            . 'After this error, no more messages were sent.', $value['uname']));
                        break;
                    }
                }
            }
        }

        return true;
    }
}
