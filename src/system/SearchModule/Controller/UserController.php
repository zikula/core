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

namespace Zikula\SearchModule\Controller;

use ModUtil;
use SecurityUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use UserUtil;
use Zikula\Core\Response\PlainResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\SearchModule\AbstractSearchable;

/**
 * User controllers for the search module
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * Main user function
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        return $this->indexAction();
    }

    /**
     * Main user function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in form()
        return new RedirectResponse($this->get('router')->generate('zikulasearchmodule_user_form', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("")
     * @Method("GET")
     *
     * Generate complete search form
     *
     * Generate the whole search form, including the various plugins options.
     * It uses the Search API's getallplugins() function to find plugins.
     *
     * @param mixed[] $vars {
     *      @type string $q           search query
     *      @type string $searchtype  type of search being requested
     *      @type string $searchorder order to sort the results in
     *      @type array  $active      array of search plugins to search (if empty all plugins are used)
     *      @type array  $modvar      array with extrainfo for search plugins
     *                      }
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module
     */
    public function formAction(Request $request)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $vars['q'] = $request->query->get('q', '');
        $vars['searchtype'] = $request->query->get('searchtype', 'AND');
        $vars['searchorder'] = $request->query->get('searchorder', 'newest');
        $vars['numlimit'] = $this->getVar('itemsperpage', 25);
        $vars['active'] = $request->query->get('active');
        $vars['modvar'] = $request->query->get('modvar', array());

        // set some defaults
        $setActiveDefaults = false;
        if (!isset($vars['active']) || !is_array($vars['active'])) {
            $setActiveDefaults = true;
            $vars['active'] = array();
        }

        if (!empty($vars['q']) && !$request->request->get('no-result', false)) {
            return $this->forward($request, 'search', array(), $vars);
        }

        // get all the LEGACY (<1.4.0) search plugins
        $search_modules = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins');
        $search_modules = false === $search_modules ? array() : $search_modules;

        // get 1.4.0+ type searchable modules
        $searchableModules = ModUtil::getModulesCapableOf(AbstractSearchable::SEARCHABLE);

        if (count($search_modules) == 0 && count($searchableModules) == 0) {
            return $this->response($this->view->fetch('User/noplugins.tpl'));
        }

        $plugin_options = array();
        // LEGACY handling (<1.4.0)
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
        // 1.4.0+ type handling
        foreach ($searchableModules as $searchableModule) {
            if ($setActiveDefaults) {
                $vars['active'][$searchableModule['name']] = '1';
            }
            $moduleBundle = ModUtil::getModule($searchableModule['name']);
            /** @var $searchableInstance AbstractSearchable */
            $searchableInstance = new $searchableModule['capabilities']['searchable']['class']($this->getContainer(), $moduleBundle);

            if ($searchableInstance instanceof AbstractSearchable) {
                if ((!$this->getVar("disable_{$searchableModule['name']}") && SecurityUtil::checkPermission('ZikulaSearchModule::Item', "{$searchableModule['name']}::", ACCESS_READ))) {
                    $active = !isset($vars['active']) || (isset($vars['active'][$searchableModule['name']]) && ($vars['active'][$searchableModule['name']] == '1'));
                    $plugin_options[$searchableModule['name']] = $searchableInstance->getOptions($active, $vars['modvar']);
                }
            }
        }

        // Create output object
        // add content to template
        $this->view->assign($vars)
                   ->assign('plugin_options', $plugin_options);

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('User/form.tpl'));
    }

    /**
     * @Route("/results/{page}", requirements={"page"="\d+"})
     *
     * Perform the search then show the results
     *
     * This function includes all the search plugins, then call every one passing
     * an array that contains the string to search for, the boolean operators.
     *
     * @return Response symfony response object templated
     *
     * @throws \InvalidArgumentException Thrown if no search query parameters were provided
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module
     */
    public function searchAction(Request $request, $page = -1)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // get parameter from HTTP input
        $vars = array();
        $vars['q'] = $request->request->get('q');
        $vars['searchtype'] = $request->request->get('searchtype', 'AND');
        $vars['searchorder'] = $request->request->get('searchorder', 'newest');
        $vars['numlimit'] = $this->getVar('itemsperpage', 25);

        // $firstpage is used to identify the very first result page
        // - and to disable calls to plugins on the following pages
        $vars['firstPage'] = $page < 1;
        $vars['page'] = $page < 1 ? 1 : $page;

        // The modulename exists in this array as key, if the checkbox was filled
        $vars['active'] = $request->request->get('active');
        if (!isset($vars['active']) || !is_array($vars['active']) || empty($vars['active'])) {
            $vars['active'] = array();
        }

        // All formular data from the modules search plugins is contained in:
        $vars['modvar'] = $request->request->get('modvar');
        if (!isset($vars['modvar']) || !is_array($vars['modvar']) || empty($vars['modvar'])) {
            $vars['modvar'] = array();
        }

        if (empty($vars['q']) && $vars['firstPage']) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! You did not enter any keywords to search for.'));

            return new RedirectResponse($this->get('router')->generate('zikulasearchmodule_user_form'));
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

        if ($result['resultCount'] == 0) {
            $request->getSession()->getFlashBag()->add('error', "
{$this->__('No search results found. You can try the following:')}
<ul>
    <li>{$this->__('Check that you spelled all words correctly.')}</li>
    <li>{$this->__('Use different keywords.')}</li>
    <li>{$this->__('Use keywords that are more general.')}</li>
    <li>{$this->__('Use fewer words.')}</li>
</ul>"
            );

            return $this->forward($request, 'form', $vars, array('no-result' => true));
        }

        // Get number of chars to display in search summaries
        $limitsummary = $this->getVar('limitsummary');
        if (empty($limitsummary)) {
            $limitsummary = 200;
        }

        $this->view->assign('resultcount', $result['resultCount'])
                   ->assign('results', $result['sqlResult'])
                   ->assign($vars)
                   ->assign('limitsummary', $limitsummary);
        if (isset($result['errors'])) {
            $this->view->assign('errors', $result['errors']);
        }

        // log the search if on first page
        if ($vars['firstPage']) {
            ModUtil::apiFunc('ZikulaSearchModule', 'user', 'log', $vars);
        }

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('User/results.tpl'));
    }

    /**
     * @Route("/recent-searches")
     *
     * Display a list of recent searches
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module or no user is logged in
     */
    public function recentAction(Request $request)
    {
        // security check
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ) || !UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need.
        $startnum = $request->query->filter('startnum', 1, false, FILTER_VALIDATE_INT);

        // we need this value multiple times, so we keep it
        // Fix it to 20 as long as there isn't a pager built in.
        $itemsperpage = 20; //$this->getVar('itemsperpage');

        $items = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getall', array('startnum' => $startnum, 'numitems' => $itemsperpage, 'sortorder' => 'date'));

        // assign the results to the template
        $this->view->assign('recentsearches', $items);

        // assign the values for the smarty plugin to produce a pager in case of there
        // being many items to display.
        $this->view->assign('pager', array('numitems'     => ModUtil::apiFunc('ZikulaSearchModule', 'user', 'countitems'),
                                           'itemsperpage' => $itemsperpage));

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('User/recent.tpl'));
    }

    /**
     * @Route("/opensearch", options={"i18n"=false})
     *
     * Generate xml for opensearch syndication
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access to the module
     */
    public function opensearchAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        return new PlainResponse($this->view->fetch('User/opensearch.xml'), Response::HTTP_OK, array("Content-Type" => "text/xml"));
    }

    /**
     * Forwards the request to another action of this controller.
     *
     * @param Request $request
     * @param         $action  The action to forwards to.
     * @param array   $get     Array of GET parameters.
     * @param array   $post    Array of POST parameters.
     *
     * @return mixed
     */
    private function forward(Request $request, $action, $get = array(), $post = array())
    {
        $path = array('_controller' => 'ZikulaSearchModule:User:' . $action);
        $subRequest = $request->duplicate($get, $post, $path);

        return $this->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
