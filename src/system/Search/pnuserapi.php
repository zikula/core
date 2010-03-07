<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Search
 * @author Jorn Wildt
 * @author Patrick Kellum
 * @author Stefano Garuti (ported to pnAPI)
 */


/**
 * get all previous search queries
 *
 * @param    int     $args['starnum']    (optional) first item to return
 * @param    int     $args['numitems']   (optional) number if items to return
 * @return   array   array of items, or false on failure
 */
function Search_userapi_getall($args)
{
    // Optional arguments.
    if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
        $args['startnum'] = 1;
    }
    if (!isset($args['numitems']) || !is_numeric($args['numitems'])) {
        $args['numitems'] = -1;
    }
    if (!isset($args['sortorder']) || !in_array($args['sortorder'], array('count', 'date'))) {
        $args['sortorder'] = 'count';
    }

    $items = array();

    // Security check
    if (!SecurityUtil::checkPermission('Search::', '::', ACCESS_OVERVIEW)) {
        return $items;
    }

    // Get items
    $sort  = isset($args['sortorder']) ? "ORDER BY {$args['sortorder']} DESC" : '';
    $items = DBUtil::selectObjectArray('search_stat', '', $sort, $args['startnum']-1, $args['numitems']);

    return $items;
}


/**
 * utility function to count the number of previous search queries
 *
 * @return   integer   number of items held by this module
 */
function Search_userapi_countitems()
{
    return DBUtil::selectObjectCount ('search_stat');
}


/**
 * get all search plugins
 *
 * @return   array   array of items, or false on failure
 */
function Search_userapi_getallplugins($args)
{
    // defaults
    if (!isset($args['loadall'])) {
        $args['loadall'] = false;
    }

    // initialize the search plugins array
    $search_modules = array();

    // Attempt to load the search API for each user module
    // The modules should be determined by a select of the modules table or something like that in the future
    $usermods = pnModGetAllMods();
    foreach ($usermods as $usermod) {
        if (pnModAPILoad($usermod['name'], 'search')  &&
             ($args['loadall'] ||
                (!pnModGetVar('Search', "disable_$usermod[name]") &&
                 SecurityUtil::checkPermission('Search::Item', "$usermod[name]::", ACCESS_READ)
                )
             )
           ) {
            $info = pnModAPIFunc($usermod['name'], 'search', 'info');
            $info['name'] = $usermod['name'];
            $search_modules[] = $info;
            $plugins_found = 'yes';
        }
    }

    return $search_modules;
}


/**
 * log search query for search statistics
 */
function search_userapi_log($args)
{
    $searchterms = DataUtil::formatForStore($args['q']);

    $obj = DBUtil::selectObjectByID('search_stat', $searchterms, 'search');

    $newobj['count']  = isset($obj['count']) ? $obj['count'] + 1 : 1;
    $newobj['date']   = date('Y-m-d H:i:s');
    $newobj['search'] = $searchterms;

    if (!isset($obj) || empty($obj)) {
        $res = DBUtil::insertObject ($newobj, 'search_stat');
    } else {
        $res = DBUtil::updateObject ($newobj, 'search_stat', '', 'search');
    }

    if (!$res) {
        return false;
    }

    // Let any hooks know that we have created a new item.
    pnModCallHooks('item', 'create', $args['q'], array('module' => 'Search'));

    return true;
}


/**
 * form custom url string
 *
 * @return string custom url string
 */
function search_userapi_encodeurl($args)
{
    // check we have the required input
    if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
        return LogUtil::registerArgsError();
    }

    if (!isset($args['type'])) {
        $args['type'] = 'user';
    }

    // create an empty string ready for population
    $vars = '';

    // for the display function use either the title (if present) or the page id
    if ($args['func'] == 'search' && isset($args['args']['q'])) {
        $vars = $args['args']['q'];
        if (isset($args['args']['page']) && $args['args']['page'] != 1) {
            $vars .= '/page/'.$args['args']['page'];
        }
    }

    // don't display the function name if either displaying an page or the normal overview
    if ($args['func'] == 'main') {
        $args['func'] = '';
    }

    // construct the custom url part
    if (empty($args['func']) && empty($vars)) {
        return $args['modname'] . '/';
    } elseif (empty($args['func'])) {
        return $args['modname'] . '/' . $vars . '/';
    } elseif (empty($vars)) {
        return $args['modname'] . '/' . $args['func'] . '/'. $args['startnum'];
    } else {
        return $args['modname'] . '/' . $args['func'] . '/' . $vars . '/';
    }
}


/**
 * decode the custom url string
 *
 * @return bool true if successful, false otherwise
 */
function search_userapi_decodeurl($args)
{
    // check we actually have some vars to work with...
    if (!isset($args['vars'])) {
        return LogUtil::registerArgsError();
    }

    // define the available user functions
    $funcs = array('main', 'search', 'recent');
    // set the correct function name based on our input
    if (empty($args['vars'][2])) {
        pnQueryStringSetVar('func', 'main');
    } elseif (!in_array($args['vars'][2], $funcs)) {
        pnQueryStringSetVar('func', 'main');
        $nextvar = 2;
    } else {
        pnQueryStringSetVar('func', $args['vars'][2]);
        $nextvar = 3;
    }

    if (FormUtil::getPassedValue('func') == 'recent'){
       pnQueryStringSetVar('startnum', $args['vars'][$nextvar]);
    }

    // identify the correct parameter to identify the page
    if (FormUtil::getPassedValue('func') == 'search' && isset($args['vars'][$nextvar]) && !empty($args['vars'][$nextvar])) {
        pnQueryStringSetVar('q', $args['vars'][$nextvar]);
        $nextvar++;
        if (isset($args['vars'][$nextvar]) && $args['vars'][$nextvar] == 'page') {
            pnQueryStringSetVar('page', (int)$args['vars'][$nextvar+1]);
        }
    }

    return true;
}
