<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Controller;

use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\SearchModule\AbstractSearchable;

/**
 * User controllers for the search module
 */
class UserController extends AbstractController
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
        return new RedirectResponse($this->get('router')->generate('zikulasearchmodule_user_form', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("")
     * @Method("GET")
     * @Template
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
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $getData = $request->query;

        $vars = [
            'q' => $getData->get('q', ''),
            'searchtype' => $getData->getAlpha('searchtype', 'AND'),
            'searchorder' => $getData->getAlpha('searchorder', 'newest'),
            'numlimit' => $this->getVar('itemsperpage', 25),
            'active' => $getData->get('active'),
            'modvar' => $getData->get('modvar', [])
        ];

        // set some defaults
        $setActiveDefaults = false;
        if (!is_array($vars['active'])) {
            $setActiveDefaults = true;
            $vars['active'] = [];
        }

        if (!empty($vars['q']) && !$request->request->get('no-result', false)) {
            return $this->forwardRequest($request, 'search', [], $vars);
        }

        $searchableModules = $this->get('zikula_search_module.internal.searchable_module_collector')->getAll();
        if (count($searchableModules) == 0) {
            return $this->render('@ZikulaSearchModule/User/noplugins.html.twig');
        }

        $pluginOptions = [];
        foreach ($searchableModules as $moduleName => $searchableInstance) {
            if ($setActiveDefaults) {
                $vars['active'][$moduleName] = 1;
            }
            if ($this->getVar('disable_' . $moduleName)) {
                continue;
            }
            if (!$this->hasPermission('ZikulaSearchModule::Item', $moduleName . '::', ACCESS_READ)) {
                continue;
            }
            $active = !isset($vars['active']) || (isset($vars['active'][$moduleName]) && ($vars['active'][$moduleName] == 1));
            $pluginOptions[$moduleName] = $searchableInstance->getOptions($active, $vars['modvar']);
        }

        $templateParameters = array_merge($vars, [
            'pluginOptions' => $pluginOptions,
            'q' => $vars['q'],
            'searchType' => $vars['searchtype'],
            'searchOrder' => $vars['searchorder']
        ]);

        return $templateParameters;
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
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // get parameter from HTTP input
        $vars = [
            'q' => $request->request->get('q'),
            'searchtype' => $request->request->get('searchtype', 'AND'),
            'searchorder' => $request->request->get('searchorder', 'newest'),
            'numlimit' => $this->getVar('itemsperpage', 25),

            // firstPage is used to identify the very first result page
            // - and to disable calls to plugins on the following pages
            'firstPage' => $page < 1,
            'page' => $page < 1 ? 1 : $page,

            'active' => $request->request->get('active'),
            // contains all form data from the modules search plugins
            'modvar' => $request->request->get('modvar')
        ];

        // The modulename exists in this array as key, if the checkbox was filled
        if (!isset($vars['active']) || !is_array($vars['active']) || empty($vars['active'])) {
            $vars['active'] = [];
        }

        if (!isset($vars['modvar']) || !is_array($vars['modvar']) || empty($vars['modvar'])) {
            $vars['modvar'] = [];
        }

        if (empty($vars['q']) && $vars['firstPage']) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! You did not enter any keywords to search for.'));

            return new RedirectResponse($this->get('router')->generate('zikulasearchmodule_user_form'));
        }

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

            return $this->forwardRequest($request, 'form', $vars, ['no-result' => true]);
        }

        // Get number of chars to display in search summaries
        $limitSummary = $this->getVar('limitsummary', 200);

        $templateParameters = array_merge($vars, [
            'resultCount' => $result['resultCount'],
            'results' => $result['sqlResult'],
            'limitSummary' => $limitSummary,
            'errors' => isset($result['errors']) ? $result['errors'] : []
        ]);

        // log the search if on first page
        if ($vars['firstPage']) {
            ModUtil::apiFunc('ZikulaSearchModule', 'user', 'log', $vars);
        }

        return $this->render('@ZikulaSearchModule/User/results.html.twig', $templateParameters);
    }

    /**
     * @Route("/recent-searches")
     * @Template
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
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)
            || !$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need.
        $startnum = $request->query->filter('startnum', 1, false, FILTER_VALIDATE_INT);
        $itemsPerPage = $this->getVar('itemsperpage');

        $items = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getall', [
            'startnum' => $startnum,
            'numitems' => $itemsPerPage,
            'sortorder' => 'date'
        ]);

        $templateParameters = [
            'recentSearches' => $items,
            'pager' => [
                'amountOfItems' => ModUtil::apiFunc('ZikulaSearchModule', 'user', 'countitems'),
                'itemsPerPage'  => $itemsPerPage
            ]
        ];

        return $templateParameters;
    }

    /**
     * @Route("/opensearch", options={"i18n"=false})
     *
     * Generate xml for opensearch syndication
     *
     * @return PlainResponse Thrown if the user doesn't have read access to the module
     */
    public function opensearchAction()
    {
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $templateParameters = [
            'siteName' => $variableApi->getSystemVar('sitename', $variableApi->getSystemVar('sitename_en')),
            'slogan' => $variableApi->getSystemVar('slogan', $variableApi->getSystemVar('slogan_en')),
            'metaKeywords' => $variableApi->getSystemVar('metakeywords', $variableApi->getSystemVar('metakeywords_en')),
            'adminMail' => $variableApi->getSystemVar('adminmail'),
            'hasAdultContent' => $variableApi->get('ZikulaSearchModule', 'opensearch_adult_content', false)
        ];

        return new PlainResponse($this->renderView('@ZikulaSearchModule/User/opensearch.xml.twig', $templateParameters), Response::HTTP_OK, ['Content-Type' => 'text/xml']);
    }

    /**
     * Forwards the request to another action of this controller.
     *
     * @param Request $request
     * @param         $action  The action to forwards to
     * @param array   $get     Array of GET parameters
     * @param array   $post    Array of POST parameters
     *
     * @return mixed
     */
    private function forwardRequest(Request $request, $action, $get = [], $post = [])
    {
        $path = ['_controller' => 'ZikulaSearchModule:User:' . $action];
        $subRequest = $request->duplicate($get, $post, $path);

        return $this->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
