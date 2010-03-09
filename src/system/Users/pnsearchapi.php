<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @category   Zikula_Core
 * @package    System_Modules
 * @subpackage Users
*/

/**
 * Search plugin info
 **/
function users_searchapi_info()
{
    return array('title' => 'Users',
                 'functions' => array('Users' => 'search'));
}

/**
 * Search form component
 **/
function users_searchapi_options($args)
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

function users_searchapi_search($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        return false;
    }

    if (!isset($args['q']) || empty($args['q'])) {
        return true;
    }

    // decide if we have to load the DUDs from the Profile module
    $profileModule = pnConfigGetVar('profilemodule', '');
    $useProfileMod = (!empty($profileModule) && pnModAvailable($profileModule));

    // get the db and table info
    $pntable = pnDBGetTables();
    $userscolumn = $pntable['users_column'];

    $q = DataUtil::formatForStore($args['q']);
    $q = str_replace('%', '\\%', $q);  // Don't allow user input % as wildcard

    // build the where clause
    $where   = array();
    $where[] = "$userscolumn[activated] = '1'";

    // invoke the current profilemodule search query
    if ($useProfileMod) {
        $uids = pnModAPIFunc($profileModule, 'user', 'searchdynadata',
                             array('dynadata' => array('all' => $q)));

        if (is_array($uids) && !empty($uids)) {
            $tmp = "($userscolumn[uname] LIKE '%$q%' OR $userscolumn[uid] IN (";
            foreach ($uids as $uid) {
                $tmp .= DataUtil::formatForStore($uid) . ',';
            }
            $tmp .= '0))';
            $where[] = $tmp;
        }
    } else {
        $where[] = "$userscolumn[uname] LIKE '%$q%'";
    }

    $where = implode(' AND ', $where);

    $users = DBUtil::selectObjectArray ('users', $where, '', -1, -1, 'uid');

    if (!$users) {
        return LogUtil::registerError(__('Error! Could not load data.'));
    }

    $sessionId = session_id();

    foreach ($users as $user) {
        if ($user['uid'] != 1 && SecurityUtil::checkPermission('Users::', "$user[uname]::$user[uid]", ACCESS_READ)) {
            $qtext = __('Registration date'). ': ' . DateUtil::formatDatetime($user['user_regdate'], 'datebrief') . "\n"
                   . __("Click the user's name to view his/her complete profile.");
            $items = array('title' => __('Registered users') . ': ' . DataUtil::formatForStore($user['uname']),
                           'text' => DataUtil::formatForStore($qtext),
                           'extra' => DataUtil::formatForStore($user['uid']),
                           'module' => 'Users',
                           'created' => DataUtil::formatForStore($user['user_regdate']),
                           'session' => DataUtil::formatForStore($sessionId));
            $insertResult = DBUtil::insertObject($items, 'search_result');
            if (!$insertResult) {
                return LogUtil::registerError(__('Error! Could not load data.'));
            }
        }
    }
    return true;
}

/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found user
 */
function users_searchapi_search_check(&$args)
{
    $profileModule = pnConfigGetVar('profilemodule', '');
    if (!empty($profileModule) && pnModAvailable($profileModule)) {
        $datarow = &$args['datarow'];
        $userId = $datarow['extra'];
        $datarow['url'] = pnModUrl($profileModule, 'user', 'view', array('uid' => $userId));
    }

    return true;
}
