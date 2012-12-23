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
 * The search for items in the Users module.
 */
class Users_Api_Search extends Zikula_AbstractApi
{
    /**
     * Return search plugin info.
     *
     * @return array An array containing information for the searc API.
     */
    public function info()
    {
        return array(
            'title'     => 'Users',
            'functions' => array(
                'Users'     => 'search'
            )
        );
    }

    /**
     * Render the search form component for Users.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * boolean 'active' Indicates that the Users module is an active part of the search(?).
     *
     * @param array $args All parameters passed to this function.
     *
     * @return string The rendered template for the Users search component.
     */
    public function options($args)
    {
        $options = '';

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            // Create output object - this object will store all of our output so that
            // we can return it easily when required
            $renderer = Zikula_View::getInstance($this->name);
            $options = $renderer->assign('active', !isset($args['active']) || isset($args['active']['Users']))
                    ->fetch('users_search_options.tpl');
        }

        return $options;
    }

    /**
     * Perform a search.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * ? $args['q'] ?.
     * ? $args[?]   ?.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success or null result, false on error.
     */
    public function search($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return false;
        }

        if (!isset($args['q']) || empty($args['q'])) {
            return true;
        }

        // decide if we have to load the DUDs from the Profile module
        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));

        // get the db and table info
        $dbtable = DBUtil::getTables();
        $userscolumn = $dbtable['users_column'];

        $q = DataUtil::formatForStore($args['q']);
        $q = str_replace('%', '\\%', $q);  // Don't allow user input % as wildcard

        // build the where clause
        $where   = array();
        $where[] = "({$userscolumn['activated']} != " . Users_Constant::ACTIVATED_PENDING_REG . ')';

        $unameClause = Search_Api_User::construct_where($args, array($userscolumn['uname']));

        // invoke the current profilemodule search query
        if ($useProfileMod) {
            $uids = ModUtil::apiFunc($profileModule, 'user', 'searchDynadata',
                                 array('dynadata' => array('all' => $q)));

            $tmp = $unameClause;
            if (is_array($uids) && !empty($uids)) {
                $tmp .= " OR {$userscolumn['uid']} IN (" . implode(', ', $uids) . ')';
            }
            $where[] = "({$tmp}) ";
        } else {
            $where[] = $unameClause;
        }

        $where = implode(' AND ', $where);

        $users = DBUtil::selectObjectArray ('users', $where, '', -1, -1, 'uid');

        if (!$users) {
            return true;
        }

        $sessionId = session_id();

        foreach ($users as $user) {
            if ($user['uid'] != 1 && SecurityUtil::checkPermission('Users::', "$user[uname]::$user[uid]", ACCESS_READ)) {
                if ($useProfileMod) {
                     $qtext = $this->__("Click the user's name to view his/her complete profile.");
                } else {
                    $qtext = '';
                }
                $items = array('title' => $user['uname'],
                               'text' => $qtext,
                               'extra' => $user['uid'],
                               'module' => 'Users',
                               'created' => null,
                               'session' => $sessionId);
                $insertResult = DBUtil::insertObject($items, 'search_result');
                if (!$insertResult) {
                    $this->registerError($this->__("Error! Could not load the results of the user's search."));

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Do last minute access checking and assign URL to items.
     *
     * Access checking is ignored since access check has
     * already been done. But we do add a URL to the found user.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array $args['datarow'] ?.
     *
     * @param array $args The search results.
     *
     * @return bool True.
     */
    public function search_check($args)
    {
        $profileModule = System::getVar('profilemodule', '');
        if (!empty($profileModule) && ModUtil::available($profileModule)) {
            $datarow = &$args['datarow'];
            $userId = $datarow['extra'];
            $datarow['url'] = ModUtil::url($profileModule, 'user', 'view', array('uid' => $userId));
        }

        return true;
    }
}
