<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\SearchModule\Api;

use ModUtil;
use UserUtil;
use Doctrine_Manager;
use SessionUtil;
use System;
use FormUtil;
use DataUtil;
use ZLanguage;
use LogUtil;
use DBUtil;
use SecurityUtil;
use Zikula\Module\SearchModule\ResultHelper;
use Zikula\Module\SearchModule\Entity\SearchStatEntity;

/**
 * Search_Api_User class.
 */
class UserApi extends \Zikula_AbstractApi
{

    /**
     * Perform the search.
     *
     * @param string $args['g']           query string to search
     * @param bool   $args['firstPage']   is this first search attempt? is so - basic search is performed
     * @param string $args['searchtype']  (optional) search type (default='AND')
     * @param string $args['searchorder'] (optional) search order (default='newest')
     * @param int    $args['numlimit']    (optional) number of items to return (default value based on Search settings, -1 for no limit)
     * @param int    $args['page']        (optional) page number (default=1)
     * @param array  $args['active']      (optional) array of search plugins to search (if empty all plugins are used)
     * @param array  $args['modvar']      (optional) array with extrainfo for search plugins
     *
     * @return array array of items array and result count, or false on failure
     */
    public function search($args)
    {
        // query string and firstPage params are required
        if (!isset($args['q']) || empty($args['q']) || !isset($args['firstPage'])) {
            return LogUtil::registerArgsError();
        }
        $vars = array();
        $vars['q'] = $args['q'];
        $vars['searchtype'] = isset($args['searchtype']) && !empty($args['searchtype']) ? $args['searchtype'] : 'AND';
        $vars['searchorder'] = isset($args['searchorder']) && !empty($args['searchorder']) ? $args['searchorder'] : 'newest';
        $vars['numlimit'] = isset($args['numlimit']) && !empty($args['numlimit']) ? $args['numlimit'] : $this->getVar('itemsperpage', 25);
        $vars['page'] = isset($args['page']) && !empty($args['page']) ? (int)$args['page'] : 1;

        $firstPage = isset($args['firstPage']) ? $args['firstPage'] : false;

        $active = isset($args['active']) && is_array($args['active']) && !empty($args['active']) ? $args['active'] : array();
        $modvar = isset($args['modvar']) && is_array($args['modvar']) && !empty($args['modvar']) ? $args['modvar'] : array();

        // work out row index from page number
        $vars['startnum'] = $vars['numlimit'] > 0 ? (($vars['page'] - 1) * $vars['numlimit']) + 1 : 1;

        $userId = (int)UserUtil::getVar('uid');
        $sessionId = session_id();

        // Do all the heavy database stuff on the first page only
        if ($firstPage) {
            // Clear current search result for current user - before showing the first page
            // Clear also older searches from other users.
            $query = $this->entityManager->createQuery("DELETE Zikula\Module\SearchModule\Entity\SearchResultEntity s WHERE s.sesid = :sid OR DATE_ADD(s.found, 1, 'DAY') < CURRENT_TIMESTAMP()");
            $query->setParameter('sid', $sessionId);
            $query->execute();

            // get all the search plugins
            $search_modules = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins');

            // Ask active modules to find their items and put them into $searchTable for the current user
            // At the same time convert modules list from numeric index to modname index

            $searchModulesByName = array();
            foreach ($search_modules as $mod) {
                // check we've a valid search plugin
                if (isset($mod['functions']) && (empty($active) || isset($active[$mod['title']]))) {
                    foreach ($mod['functions'] as $contenttype => $function) {
                        if (isset($modvar[$mod['title']])) {
                            $param = array_merge($vars, $modvar[$mod['title']]);
                        } else {
                            $param = $vars;
                        }
                        $searchModulesByName[$mod['name']] = $mod;
                        $ok = ModUtil::apiFunc($mod['title'], 'search', $function, $param);
                        if (!$ok) {
                            LogUtil::registerError($this->__f('Error! \'%1$s\' module returned false in search function \'%2$s\'.', array($mod['title'], $function)));

                            return System::redirect(ModUtil::url('ZikulaSearchModule', 'user', 'index'));
                        }
                    }
                }
            }

            // Count number of found results (pointless, this will alays be 0 as we just deleted these! - drak)
            $query = $this->entityManager->createQuery("SELECT COUNT(s.sesid) FROM Zikula\Module\SearchModule\Entity\SearchResultEntity s WHERE s.sesid = :sid");
            $query->setParameter('sid', $sessionId);
            $resultCount = $query->getSingleScalarResult();
            SessionUtil::setVar('searchResultCount', $resultCount);
            SessionUtil::setVar('searchModulesByName', $searchModulesByName);
        } else {
            $resultCount = SessionUtil::getVar('searchResultCount');
            $searchModulesByName = SessionUtil::getVar('searchModulesByName');
        }

        // Fetch search result - do sorting and paging in database
        // Figure out what to sort by
        switch ($args['searchorder']) {
            case 'alphabetical':
                $sort = 'title';
                break;
            case 'oldest':
                $sort = 'created';
                break;
            case 'newest':
                $sort = 'created DESC';
                break;
            default:
                $sort = 'title';
                break;
        }

        // Get next N results from the current user's result set
        // The "checker" object is used to:
        // 1) do secondary access control (deprecated more or less)
        // 2) let the modules add "url" to the found (and viewed) items
        $checker = new ResultHelper($searchModulesByName);

        $dql = "SELECT s FROM Zikula\Module\SearchModule\Entity\SearchResultEntity s WHERE s.sesid = :sid ORDER BY s.created ASC";
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('sid', $sessionId);
        $query->setMaxResults($vars['numlimit']);

        $query->setFirstResult($vars['startnum'] - 1);

        $results = $query->getArrayResult();

        // add displayname of modules found
        $sqlResult = array();
        foreach ($results as $result) {
            if ($checker->checkResult($result)) {
                $sqlResult[] = $result;
            }
        }

        $cnt = count($sqlResult);
        for ($i = 0; $i < $cnt; $i++) {
            $modinfo = ModUtil::getInfoFromName($sqlResult[$i]['module']);
            $sqlResult[$i]['displayname'] = $modinfo['displayname'];
        }

        $result = array(
                'resultCount' => $resultCount,
                'sqlResult' => $sqlResult
        );

        return $result;
    }

    /**
     * Get all previous search queries.
     *
     * @param int $args['starnum']  (optional) first item to return.
     * @param int $args['numitems'] (optional) number if items to return.
     *
     * @return array array of items, or false on failure.
     */
    public function getall($args)
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
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_OVERVIEW)) {
            return $items;
        }

        // Get items
        $sort = isset($args['sortorder']) ? "ORDER BY s.{$args['sortorder']} DESC" : '';
        $dql = "SELECT s FROM Zikula\\Module\\SearchModule\\Entity\\SearchStatEntity s $sort";
        $query = $this->entityManager->createQuery($dql);
        $query->setMaxResults($args['numitems']);
        $query->setFirstResult($args['startnum'] - 1);
        $items = $query->execute();

        return $items;
    }

    /**
     * Utility function to count the number of previous search queries.
     *
     * @return integer number of items held by this module.
     */
    public function countitems()
    {
        return DBUtil::selectObjectCount('search_stat');
    }

    /**
     * Get all search plugins.
     *
     * @return array array of items, or false on failure.
     */
    public function getallplugins($args)
    {
        // defaults
        if (!isset($args['loadall'])) {
            $args['loadall'] = false;
        }

        // initialize the search plugins array
        $search_modules = array();

        // Attempt to load the search API for each user module
        // The modules should be determined by a select of the modules table or something like that in the future
        $usermods = ModUtil::getAllMods();
        foreach ($usermods as $usermod) {
            if ($args['loadall'] || (!$this->getVar("disable_$usermod[name]") && SecurityUtil::checkPermission('ZikulaSearchModule::Item', "$usermod[name]::", ACCESS_READ))) {
                $info = ModUtil::apiFunc($usermod['name'], 'search', 'info');
                if ($info) {
                    $info['name'] = $usermod['name'];
                    $search_modules[] = $info;
                    $plugins_found = 'yes';
                }
            }
        }

        return $search_modules;
    }

    /**
     * Log search query for search statistics.
     */
    public function log($args)
    {
        $searchterms = DataUtil::formatForStore($args['q']);

        $obj = $this->entityManager->getRepository('Zikula\Module\SearchModule\Entity\SearchStatEntity')->findOneBy(array('search' => $searchterms));

        if (!$obj) {
            $obj = new SearchStatEntity();
            $this->entityManager->persist($obj);
        }

        $obj['count'] = isset($obj['count']) ? $obj['count'] + 1 : 1;
        $obj['date'] = new \DateTime('now', new \DateTimeZone('UTC'));
        $obj['search'] = $searchterms;

        $this->entityManager->flush();

        return true;
    }

    /**
     * Form custom url string.
     *
     * @return string custom url string.
     */
    public function encodeurl($args)
    {
        // check we have the required input
        if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
            return LogUtil::registerArgsError();
        }

        if (!isset($args['type']) || empty($args['type'])) {
            $args['type'] = 'user';
        } elseif (!is_string($args['type']) || ($args['type'] != 'user')) {
            return LogUtil::registerArgsError();
        }

        if (empty($args['func'])) {
            $args['func'] = 'index';
        }

        // rename the search function to avoid conflicts
        // with the module name and default shortURL module
        if ($args['func'] == 'search') {
            $args['func'] = 'process';
        }

        // create an empty string ready for population
        $vars = '';

        // for the display function use either the title (if present) or the page id
        if ($args['func'] == 'process' && isset($args['args']['q']) && !empty($args['args']['q'])) {
            $vars = '/' . $args['args']['q'];
            if (isset($args['args']['page']) && $args['args']['page'] != 1) {
                $vars .= '/page/' . $args['args']['page'];
            }
        }

        // construct the custom url part
        if (empty($vars) && isset($args['args']['startnum']) && !empty($args['args']['startnum'])) {
            return $args['modname'] . '/' . $args['func'] . '/' . $args['args']['startnum'];
        } else {
            return $args['modname'] . (!empty($vars) || $args['func'] != 'index' ? '/' . $args['func'] . $vars : '');
        }
    }

    /**
     * Decode the custom url string.
     *
     * @return bool true if successful, false otherwise.
     */
    public function decodeurl($args)
    {
        // check we actually have some vars to work with...
        if (!isset($args['vars'])) {
            return LogUtil::registerArgsError();
        }

        System::queryStringSetVar('type', 'user');

        // define the available user functions
        $funcs = array('index', 'form', 'search', 'process', 'recent');
        // set the correct function name based on our input
        if (empty($args['vars'][2])) {
            // Retain this for BC for older URLs that might be stored
            System::queryStringSetVar('func', 'index');
        } elseif (!in_array($args['vars'][2], $funcs)) {
            System::queryStringSetVar('func', 'index');
            $nextvar = 2;
        } else {
            if ($args['vars'][2] == 'process') {
                $args['vars'][2] = 'search';
            }
            System::queryStringSetVar('func', $args['vars'][2]);
            $nextvar = 3;
        }

        if (FormUtil::getPassedValue('func') == 'recent' && isset($args['vars'][$nextvar])) {
            System::queryStringSetVar('startnum', $args['vars'][$nextvar]);
        }

        // identify the correct parameter to identify the page
        if (FormUtil::getPassedValue('func') == 'search' && isset($args['vars'][$nextvar]) && !empty($args['vars'][$nextvar])) {
            System::queryStringSetVar('q', $args['vars'][$nextvar]);
            $nextvar++;
            if (isset($args['vars'][$nextvar]) && $args['vars'][$nextvar] == 'page') {
                System::queryStringSetVar('page', (int)$args['vars'][$nextvar + 1]);
            }
        }

        return true;
    }

    /**
     * Splits the query string into words suitable for a SQL query.
     *
     * This function is ported 'as is' from the old, nonAPI, module
     * it is called from each plugin so we can't delete it or change it's name
     *
     * @param string $q          the string to parse and split.
     * @param string $dbwildcard wrap each word in a DB wildcard character (%).
     *
     * @return array an array of words optionally surrounded by '%'
     */
    public static function split_query($q, $dbwildcard = true)
    {
        if (!isset($q)) {
            return;
        }

        $w = array();
        $stripped = DataUtil::formatForStore($q);
        $qwords = preg_split('/ /', $stripped, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($qwords as $word) {
            if ($dbwildcard) {
                $w[] = '%' . $word . '%';
            } else {
                $w[] = $word;
            }
        }

        return $w;
    }

    /**
     * Construct part of a where clause out of the supplied search parameters.
     */
    public static function construct_where($args, $fields, $mlfield = null)
    {
        $where = '';

        if (!isset($args) || empty($args) || !isset($fields) || empty($fields)) {
            return $where;
        }

        if (!empty($args['q'])) {
            $q = DataUtil::formatForStore($args['q']);
            $q = str_replace('%', '\\%', $q);  // Don't allow user input % as wildcard
            $where .= ' (';
            if ($args['searchtype'] !== 'EXACT') {
                $searchwords = self::split_query($q);
                $connector = $args['searchtype'] == 'AND' ? ' AND ' : ' OR ';
            } else {
                $searchwords = array("%{$q}%");
            }
            $start = true;
            foreach ($searchwords as $word) {
                $where .= ( !$start ? $connector : '') . ' (';
                // I'm not sure if "LIKE" is the best solution in terms of DB portability (PC)
                foreach ($fields as $field) {
                    $where .= "{$field} LIKE '$word' OR ";
                }
                $where = substr($where, 0, -4);
                $where .= ')';
                $start = false;
            }
            $where .= ') ';
        }

        // Check if we're in a multilingual setup
        if (isset($mlfield) && System::getVar('multilingual') == 1) {
            $currentlang = ZLanguage::getLanguageCode();
            $where .= "AND ({$mlfield} = '$currentlang' OR {$mlfield} = '')";
        }

        return $where;
    }

    /**
     * Get available menu links.
     *
     * @return array array of menu links.
     */
    public function getlinks($args)
    {
        $links = array();
        $search_modules = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins');

        if (SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaSearchModule', 'admin', 'index'), 'text' => $this->__('Backend'), 'class' => 'smallicon smallicon-config');
        }

        if (SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('ZikulaSearchModule', 'user', 'index', array()), 'text' => $this->__('New search'), 'class' => 'smallicon smallicon-search');
            if ((count($search_modules) > 0) && UserUtil::isLoggedIn()) {
                $links[] = array('url' => ModUtil::url('ZikulaSearchModule', 'user', 'recent', array()), 'text' => $this->__('Recent searches list'), 'class' => 'smallicon smallicon-view');
            }
        }

        return $links;
    }

}
