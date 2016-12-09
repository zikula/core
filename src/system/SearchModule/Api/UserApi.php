<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Api;

use DataUtil;
use ModUtil;
use ServiceUtil;
use SessionUtil;
use System;
use Zikula\Core\ModUrl;
use Zikula\SearchModule\AbstractSearchable;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\Entity\SearchStatEntity;
use Zikula\SearchModule\ResultHelper;
use ZLanguage;

/**
 * API's used by user controllers
 *
 * @deprecated
 * @todo Needs a service replacement for removal in Core-2.0
 */
class UserApi
{
    /**
     * Perform the search.
     *
     * @param mixed[] $args {
     *         @type string $q           query string to search
     *         @type bool   $firstPage   is this first search attempt? is so - basic search is performed
     *         @type string $searchtype  (optional) search type (default='AND')
     *         @type string $searchorder (optional) search order (default='newest')
     *         @type int    $numlimit    (optional) number of items to return (default value based on Search settings, -1 for no limit)
     *         @type int    $page        (optional) page number (default=1)
     *         @type array  $active      (optional) array of search plugins to search (if empty all plugins are used)
     *         @type array  $modvar      (optional) array with extrainfo for search plugins
     *                      }
     *
     * @return array array of items array and result count
     *
     * @throws \InvalidArgumentException Thrown if either q or firstpage isn't provided or q is empty
     * @throws \RuntimeException Thrown if a search plugin returns false
     */
    public function search($args)
    {
        // query string and firstPage params are required
        if (!isset($args['firstPage']) || ($args['firstPage'] && (!isset($args['q']) || empty($args['q'])))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }
        $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');
        $vars = [
            'q' => str_replace('%', '', $args['q']), // Don't allow user input % as wildcard
            'searchtype' => isset($args['searchtype']) && !empty($args['searchtype']) ? $args['searchtype'] : 'AND',
            'searchorder' => isset($args['searchorder']) && !empty($args['searchorder']) ? $args['searchorder'] : 'newest',
            'numlimit' => isset($args['numlimit']) && !empty($args['numlimit']) ? $args['numlimit'] : $variableApi->get('ZikulaSearchModule', 'itemsperpage', 25),
            'page' => isset($args['page']) && !empty($args['page']) ? (int)$args['page'] : 1
        ];

        $firstPage = isset($args['firstPage']) ? $args['firstPage'] : false;

        $active = isset($args['active']) && is_array($args['active']) && !empty($args['active']) ? $args['active'] : [];
        $modvar = isset($args['modvar']) && is_array($args['modvar']) && !empty($args['modvar']) ? $args['modvar'] : [];

        // work out row index from page number
        $vars['startnum'] = $vars['numlimit'] > 0 ? (($vars['page'] - 1) * $vars['numlimit']) + 1 : 1;

        $sessionId = session_id();

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $searchResultRepository = $entityManager->getRepository('ZikulaSearchModule:SearchResultEntity');

        // Do all the heavy database stuff on the first page only
        if ($firstPage) {
            // Clear current search result for current user - before showing the first page
            // Clear also older searches from other users.
            $searchResultRepository->clearOldResults($sessionId);

            // get all the search plugins
            $search_modules = $this->getallplugins();

            // Ask active modules to find their items and put them into $searchTable for the current user
            // At the same time convert modules list from numeric index to modname index

            $searchModulesByName = [];
            foreach ($search_modules as $mod) {
                // check if we've a valid search plugin
                if (!isset($mod['functions'])) {
                    continue;
                }
                if (!empty($active) && !isset($active[$mod['title']])) {
                    continue;
                }

                foreach ($mod['functions'] as $contenttype => $function) {
                    if (isset($modvar[$mod['title']])) {
                        $param = array_merge($vars, $modvar[$mod['title']]);
                    } else {
                        $param = $vars;
                    }
                    $searchModulesByName[$mod['name']] = $mod;
                    $ok = ModUtil::apiFunc($mod['title'], 'search', $function, $param);
                    if (!$ok) {
                        $translator = ServiceUtil::get('translator.default');
                        throw new \RuntimeException($translator->__f('Error! \'%1$s\' module returned false in search function \'%2$s\'.', [
                            '%1$s' => $mod['title'],
                            '%2$s' => $function
                        ]));
                    }
                }
            }

            // Ask 1.4.0+ type modules for search results and persist them
            $searchableModules = ModUtil::getModulesCapableOf(AbstractSearchable::SEARCHABLE);
            foreach ($searchableModules as $searchableModule) {
                if (!empty($active) && !isset($active[$searchableModule['name']])) {
                    continue;
                }

                // send an *array* of queried words to 1.4.0+ type modules
                if ($vars['searchtype'] == 'EXACT') {
                    $words = [trim($vars['q'])];
                } else {
                    $words = preg_split('/ /', $vars['q'], -1, PREG_SPLIT_NO_EMPTY);
                }
                $moduleBundle = ModUtil::getModule($searchableModule['name']);
                /** @var $searchableInstance AbstractSearchable */
                $searchableInstance = new $searchableModule['capabilities']['searchable']['class'](ServiceUtil::get('service_container'), $moduleBundle);
                if (!($searchableInstance instanceof AbstractSearchable)) {
                    continue;
                }
                $modvar[$searchableModule['name']] = isset($modvar[$searchableModule['name']]) ? $modvar[$searchableModule['name']] : null;
                $results = $searchableInstance->getResults($words, $vars['searchtype'], $modvar[$searchableModule['name']]);
                foreach ($results as $result) {
                    $searchResult = new SearchResultEntity();
                    $searchResult->merge($result);
                    $entityManager->persist($searchResult);
                }
                $entityManager->flush();
            }

            // Count number of found results
            $resultCount = $searchResultRepository->countResults($sessionId);
            SessionUtil::setVar('searchResultCount', $resultCount);
            SessionUtil::setVar('searchModulesByName', $searchModulesByName);
        } else {
            $resultCount = SessionUtil::getVar('searchResultCount');
            $searchModulesByName = SessionUtil::getVar('searchModulesByName');
        }

        // Fetch search result - do sorting and paging in database
        // Figure out what to sort by
        $sort = 'title';
        $dir = 'ASC';
        switch ($args['searchorder']) {
            case 'alphabetical':
            default:
                break;
            case 'oldest':
                $sort = 'created';
                break;
            case 'newest':
                $sort = 'created';
                $dir = 'DESC';
                break;
        }

        // Get next N results from the current user's result set
        // The "checker" object is used to:
        // 1) do secondary access control (deprecated more or less)
        // 2) let the modules add "url" to the found (and viewed) items
        $checker = new ResultHelper($searchModulesByName);

        $results = $searchResultRepository->getResults(['sesid' => $sessionId], [$sort => $dir], $vars['numlimit'], $vars['startnum'] - 1);

        // add displayname of modules found
        $sqlResult = [];
        foreach ($results as $result) {
            // reformat url for 1.4.0+ type searches @todo - refactor to do this in the template
            $result['url'] = (isset($result['url']) && ($result['url'] instanceof ModUrl)) ? $result['url']->getUrl() : null;
            // process result for LEGACY (<1.4.0) searches
            if ($checker->checkResult($result)) {
                $sqlResult[] = $result;
            }
        }

        $amountOfResults = count($sqlResult);
        for ($i = 0; $i < $amountOfResults; $i++) {
            $modinfo = ModUtil::getInfoFromName($sqlResult[$i]['module']);
            $sqlResult[$i]['displayname'] = $modinfo['displayname'];
        }

        $result = [
            'resultCount' => $resultCount,
            'sqlResult' => $sqlResult,
        ];
        if (isset($searchableInstance)) {
            $result['errors'] = $searchableInstance->getErrors();
        }

        return $result;
    }

    /**
     * Get all previous search queries.
     *
     * @param int[] $args {
     *         @type int    $starnum   (optional) first item to return.
     *         @type int    $numitems  (optional) number if items to return.
     *         @type string $sortorder (optional} sort order either 'count' or 'date'
     *                    }
     *
     * @return array array of items
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
        if (!isset($args['sortorder']) || !in_array($args['sortorder'], ['count', 'date'])) {
            $args['sortorder'] = 'count';
        }

        $items = [];

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaSearchModule::', '::', ACCESS_OVERVIEW)) {
            return $items;
        }

        $sorting = [];
        if (isset($args['sortorder'])) {
            $sorting[$args['sortorder']] = 'DESC';
        }

        // Get items
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $items = $entityManager->getRepository('ZikulaSearchModule:SearchStatEntity')->getStats([], $sorting, $args['numitems'], $args['startnum'] - 1);

        return $items;
    }

    /**
     * Utility function to count the number of previous search queries.
     *
     * @return integer number of items held by this module
     */
    public function countitems()
    {
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $amount = $entityManager->getRepository('ZikulaSearchModule:SearchStatEntity')->countStats();

        return $amount;
    }

    /**
     * Get all search plugins.
     *
     * @param bool[] $args {
     *      @type bool $loadall load all plugins (default: false_
     *                     }
     * @return array array of items
     */
    public function getallplugins($args)
    {
        // defaults
        $loadAll = isset($args['loadall']) ? (bool) $args['loadall'] : false;

        // initialize the search plugins array
        $search_modules = [];

        $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');
        // Attempt to load the search API for each user module
        // The modules should be determined by a select of the modules table or something like that in the future
        $usermods = ModUtil::getAllMods();
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        foreach ($usermods as $usermod) {
            if ($loadAll || (!$variableApi->get('ZikulaSearchModule', 'disable_' . $usermod['name']) && $permissionApi->Permission('ZikulaSearchModule::Item', "$usermod[name]::", ACCESS_READ))) {
                $info = ModUtil::apiFunc($usermod['name'], 'search', 'info');
                if ($info) {
                    $info['name'] = $usermod['name'];
                    $search_modules[] = $info;
                }
            }
        }

        return $search_modules;
    }

    /**
     * Log search query for search statistics.
     *
     * @param mixed[] $args {
     *                      }
     *
     * @return bool true
     */
    public function log($args)
    {
        if (!isset($args['q'])) {
            return true;
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $obj = $entityManager->getRepository('ZikulaSearchModule:SearchStatEntity')->findOneBy(['search' => $args['q']]);

        if (!$obj) {
            $obj = new SearchStatEntity();
            $entityManager->persist($obj);
        }

        $obj['count'] = isset($obj['count']) ? $obj['count'] + 1 : 1;
        $obj['date'] = new \DateTime('now', new \DateTimeZone('UTC'));
        $obj['search'] = $args['q'];

        $entityManager->flush();

        return true;
    }

    /**
     * Splits the query string into words suitable for a SQL query.
     *
     * This function is ported 'as is' from the old, nonAPI, module
     * it is called from each plugin so we can't delete it or change it's name
     *
     * @param string $q          the string to parse and split
     * @param bool   $dbWildcard whether to wrap each word in a DB wildcard character (%)
     *
     * @return array an array of words optionally surrounded by '%'
     */
    public static function split_query($q, $dbWildcard = true)
    {
        if (!isset($q)) {
            return;
        }

        $w = [];
        $stripped = DataUtil::formatForStore($q);
        $qwords = preg_split('/ /', $stripped, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($qwords as $word) {
            $searchWord = $dbWildcard ? '%' . $word . '%' : $word;
            $w[] = $searchWord;
        }

        return $w;
    }

    /**
     * Construct part of a where clause out of the supplied search parameters.
     *
     * @param mixed[] $args {
     *          @type string $q          the search query string
     *          @type string $searchtype type of search ('AND'/'OR')
     *                      }
     * @param mixed[] $fields
     * @param string $mlfield
     *
     * @return string sql where clause
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
                $searchwords = ['%' . $q . '%'];
                $connector = ' OR ';
            }
            $start = true;
            foreach ($searchwords as $word) {
                $where .= (!$start ? $connector : '') . ' (';
                foreach ($fields as $field) {
                    $where .= "{$field} LIKE '" . str_replace('\'', '', $word) . "' OR ";
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
}
