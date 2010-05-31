<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @category Zikula_Core
 * @package System_Modules
 * @subpackage Users
 */

/**
 * The Search API allows a user to search for items in a module; this class provides
 * serach services for the Users module.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Api_Search extends AbstractApi
{
    /**
     * Return search plugin info.
     *
     * @return array An array containing information for the searc API.
     */
    public function info()
    {
        return array('title' => 'Users',
                     'functions' => array('Users' => 'search'));
    }

    /**
     * Render the search form component for Users.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['active'] (?) ?.
     *
     * @return string The rendered template for the Users search component.
     */
    public function options($args)
    {
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            // Create output object - this object will store all of our output so that
            // we can return it easily when required
            $pnRender = Renderer::getInstance('Users');
            $pnRender->assign('active', !isset($args['active']) || isset($args['active']['Users']));
            return $pnRender->fetch('users_search_options.htm');
        }

        return '';
    }

    /**
     * Perform a search.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['q'] (?) ?.
     *                    $args[?] (?) ?.
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
        $pntable = System::dbGetTables();
        $userscolumn = $pntable['users_column'];

        $q = DataUtil::formatForStore($args['q']);
        $q = str_replace('%', '\\%', $q);  // Don't allow user input % as wildcard

        // build the where clause
        $where   = array();
        $where[] = "{$userscolumn['activated']} = " . UserUtil::ACTIVATED_ACTIVE;

        $unameClause = search_construct_where($args,array($userscolumn['uname']));
        // invoke the current profilemodule search query
        if ($useProfileMod) {
            $uids = ModUtil::apiFunc($profileModule, 'user', 'searchDynadata',
                                 array('dynadata' => array('all' => $q)));

            if (is_array($uids) && !empty($uids)) {
                $tmp = $unameClause . " OR $userscolumn[uid] IN (";
                foreach ($uids as $uid) {
                    $tmp .= DataUtil::formatForStore($uid) . ',';
                }
                $tmp .= '0))';
                $where[] = $tmp;
            }
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
                    return LogUtil::registerError($this->__("Error! Could not load the results of the user's search."));
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
     * @param array &$args The search results.
     *
     * @return bool True.
     */
    public function search_check(&$args)
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
