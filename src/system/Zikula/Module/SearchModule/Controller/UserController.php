<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\SearchModule\Controller;

use ModUtil;
use LogUtil;
use SecurityUtil;
use SessionUtil;
use UserUtil;
use System;
use DataUtil;
use ZLanguage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * User controllers for the search module
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * Main user function
     *
     * @return void
     */
    public function mainAction()
    {
        // Security check will be done in form()
        return $this->redirect(ModUtil::url('ZikulaSearchModule', 'user', 'form'));
    }

    /**
     * Main user function
     *
     * @return void
     */
    public function indexAction()
    {
        // Security check will be done in form()
        return $this->redirect(ModUtil::url('ZikulaSearchModule', 'user', 'form'));
    }

    /**
     * Generate complete search form
     *
     * Generate the whole search form, including the various plugins options.
     * It uses the Search API's getallplugins() function to find plugins.
     *
     * @param mixed[] $vars {
     *      @type string $q           search query
     *      @type string $searchtype  type of search being requested
     *      @type string $searchorder order to sort the results in
     *      @type int    $numlimit    number of search results to return
     *      @type array  $active      array of search plugins to search (if empty all plugins are used)
     *      @type array  $modvar      array with extrainfo for search plugins
     *                      }
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module
     */
    public function formAction($vars = array())
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // get parameter from input
        $vars['q'] = strip_tags($this->request->request->get('q', ''));
        $vars['searchtype'] = $this->request->request->get('searchtype', SessionUtil::getVar('searchtype'));
        $vars['searchorder'] = $this->request->request->get('searchorder', SessionUtil::getVar('searchorder'));
        $vars['numlimit'] = $this->getVar('itemsperpage', 25);
        $vars['active'] = $this->request->request->get('active', SessionUtil::getVar('searchactive'));
        $vars['modvar'] = $this->request->request->get('modvar', SessionUtil::getVar('searchmodvar'));

        // this var allows the headers to not be displayed
        if (!isset($vars['titles']))
            $vars['titles'] = true;

        // set some defaults
        if (!isset($vars['searchtype']) || empty($vars['searchtype'])) {
            $vars['searchtype'] = 'AND';
        }
        if (!isset($vars['searchorder']) || empty($vars['searchorder'])) {
            $vars['searchorder'] = 'newest';
        }
        $setActiveDefaults = false;
        if (!isset($vars['active']) || !is_array($vars['active'])) {
            $setActiveDefaults = true;
            $vars['active'] = array();
        }

        // reset the session vars for a new search
        SessionUtil::delVar('searchtype');
        SessionUtil::delVar('searchorder');
        SessionUtil::delVar('searchactive');
        SessionUtil::delVar('searchmodvar');

        // get all the search plugins
        $search_modules = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins');
        $search_modules = false === $search_modules ? array() : $search_modules;

        if (count($search_modules) > 0) {
            $plugin_options = array();
            foreach ($search_modules as $mods) {
                // if active array is empty, we need to set defaults
                if ($setActiveDefaults) {
                    $vars['active'][$mods['name']] = '1';
                }

                // as every search plugins return a formatted html string
                // we assign it to a generic holder named 'plugin_options'
                // maybe in future this will change
                // we should retrieve from the plugins an array of values
                // and formatting it here according with the module's template
                // we have also to provide some trick to assure the 'backward compatibility'

                if (isset($mods['title'])) {
                    $plugin_options[$mods['title']] = ModUtil::apiFunc($mods['title'], 'search', 'options', $vars);
                }
            }

            // Create output object
            // add content to template
            $this->view->assign($vars)
                       ->assign('plugin_options', $plugin_options);

            // Return the output that has been generated by this function
            return $this->view->fetch('User/form.tpl');
        } else {
            // Create output object
            // Return the output that has been generated by this function
            return $this->view->fetch('User/noplugins.tpl');
        }
    }

    /**
     * Perform the search then show the results
     *
     * This function includes all the search plugins, then call every one passing
     * an array that contains the string to search for, the boolean operators.
     *
     * @return Response symfony response object templated
     *
     * @thrown \InvalidArgumentException Thrown if no search query parameters were provided
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module
     */
    public function searchAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // get parameter from HTTP input
        $vars = array();
        $vars['q'] = strip_tags($this->request->request->get('q', ''));
        $vars['searchtype'] = $this->request->request->get('searchtype', SessionUtil::getVar('searchtype'));
        $vars['searchorder'] = $this->request->request->get('searchorder', SessionUtil::getVar('searchorder'));
        $vars['numlimit'] = $this->getVar('itemsperpage', 25);
        $vars['page'] = (int)$this->request->request->get('page', 1);

        // $firstpage is used to identify the very first result page
        // - and to disable calls to plugins on the following pages
        $vars['firstPage'] = empty($_REQUEST['page']);

        // The modulename exists in this array as key, if the checkbox was filled
        $vars['active'] = $this->request->request->get('active', SessionUtil::getVar('searchactive'));

        // All formular data from the modules search plugins is contained in:
        $vars['modvar'] = $this->request->request->get('modvar', SessionUtil::getVar('searchmodvar'));

        if (empty($vars['q'])) {
            throw new \InvalidArgumentException($this->__('Error! You did not enter any keywords to search for.'));
        }

        // set some defaults
        if (!isset($vars['searchtype']) || empty($vars['searchtype'])) {
            $vars['searchtype'] = 'AND';
        } else {
            SessionUtil::setVar('searchtype', $vars['searchtype']);
        }
        if (!isset($vars['searchorder']) || empty($vars['searchorder'])) {
            $vars['searchorder'] = 'newest';
        } else {
            SessionUtil::setVar('searchorder', $vars['searchorder']);
        }
        if (!isset($vars['active']) || !is_array($vars['active']) || empty($vars['active'])) {
            $vars['active'] = array();
        } else {
            SessionUtil::setVar('searchactive', $vars['active']);
        }
        if (!isset($vars['modvar']) || !is_array($vars['modvar']) || empty($vars['modvar'])) {
            $vars['modvar'] = array();
        } else {
            SessionUtil::setVar('searchmodvar', $vars['modvar']);
        }

        /*
        // FIXME: Cannot cache correctly while do not know
        // the parameters passed to the search plugins, and
        // build a complete cache_id

        // setup an individual cache
        $lifetime = ModUtil::getVar('ZikulaThemeModule', 'render_lifetime');
        $lifetime = $lifetime ? $lifetime : 3600;

        $cacheid = md5($vars['q'].'-'.$vars['searchtype'].'-'.$vars['searchorder']).'/'.UserUtil::getGidCacheString().'/page'.$vars['page'];

        $this->view->setCaching(Zikula_View::CACHE_INDIVIDUAL)
                   ->setCacheLifetime($lifetime)
                   ->setCacheId($cacheid);

        // check if the contents are cached
        if ($this->view->is_cached('User/results.tpl')) {
            return $this->view->fetch('User/results.tpl');
        }
        */
        $this->view->setCaching(false); // not to show equal results for different searches

        $result = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'search', $vars);

        // Get number of chars to display in search summaries
        $limitsummary = $this->getVar('limitsummary');
        if (empty($limitsummary)) {
            $limitsummary = 200;
        }

        $this->view->assign('resultcount', $result['resultCount'])
                   ->assign('results', $result['sqlResult'])
                   ->assign($this->getVars())
                   ->assign($vars)
                   ->assign('limitsummary', $limitsummary);

        // log the search if on first page
        if ($vars['firstPage']) {
            ModUtil::apiFunc('ZikulaSearchModule', 'user', 'log', $vars);
        }

        // Return the output that has been generated by this function
        return $this->view->fetch('User/results.tpl');
    }

    /**
     * display a list of recent searches
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module or no user is logged in
     */
    public function recentAction()
    {
        // security check
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ) || !UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need.
        $startnum = $this->request->query->filter('startnum', 1, false, FILTER_VALIDATE_INT);

        // we need this value multiple times, so we keep it
        $itemsperpage = $this->getVar('itemsperpage');

        // get the
        $items = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getall', array('startnum' => $startnum, 'numitems' => $itemsperpage, 'sortorder' => 'date'));

        // assign the results to the template
        $this->view->assign('recentsearches', $items);

        // assign the values for the smarty plugin to produce a pager in case of there
        // being many items to display.
        $this->view->assign('pager', array('numitems'     => ModUtil::apiFunc('ZikulaSearchModule', 'user', 'countitems'),
                                           'itemsperpage' => $itemsperpage));

        // Return the output that has been generated by this function
        return $this->view->fetch('User/recent.tpl');
    }

    /**
     * Generate xml for opensearch syndication
     *
     * @return void
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module
     */
    public function opensearchAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $sitename = DataUtil::formatForDisplay(System::getVar('sitename'));

        header("Content-Type:text/xml");
        echo
            '<?xml version="1.0" encoding="UTF-8"?>
            <OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
                <ShortName>' . $sitename . '</ShortName>
                <Description>' . DataUtil::formatForDisplay(System::getVar('slogan')) . '</Description>
                <Tags>' . DataUtil::formatForDisplay(System::getVar('metakeywords')) . '</Tags>
                <Contact>' . DataUtil::formatForDisplay(System::getVar('adminmail')) . '</Contact>
                <Url type="text/html" template="' . DataUtil::formatForDisplay(ModUtil::url($this->name, 'user', 'search', array('q' => '{searchTerms}', 'page' => '{startPage?}'), null, null, true)) . '"/>
                <LongName>' . $sitename . ' ' . $this->__('Search') . '</LongName>
                <Attribution>Search data Copyright ' . date('Y') . ', ' . $sitename . DataUtil::formatForDisplay($this->__(', All Rights Reserved')) .'</Attribution>
                <SyndicationRight>open</SyndicationRight>
                <AdultContent>' . (int)$this->getVar('opensearch_adult_content') . '</AdultContent>
                <Language>' . ZLanguage::getLanguageCode() . '</Language>
                <OutputEncoding>UTF-8</OutputEncoding>
                <InputEncoding>UTF-8</InputEncoding>
            </OpenSearchDescription>';
        exit;
    }
}