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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\SearchModule\AbstractSearchable;

class SearchController extends AbstractController
{
    /**
     * @Route("/{page}", requirements={"page"="\d+"})
     * @Template("ZikulaSearchModule:Search:execute.html.twig")
     *
     * @param Request $request
     * @param int $page
     * @return array|Response
     */
    public function executeAction(Request $request, $page = -1)
    {
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        $setActiveDefaults = false;
        if (!$request->query->has('active')) {
            $setActiveDefaults = true;
            $activeModules = [];
        } else {
            $activeModules = $request->query->get('active');
            if (!is_array($activeModules)) {
                $activeModules = [$activeModules];
            }
        }

        // get all the LEGACY (<1.4.0) search plugins @deprecated - remove at Core-2.0
        $legacySearchModules = \ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins');
        $legacySearchModules = false === $legacySearchModules ? [] : $legacySearchModules;
        // get 1.4.0+ type searchable modules @deprecated - remove at Core-2.0
        $core14searchableModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf(AbstractSearchable::SEARCHABLE);
        // get Core-2.0 searchable modules
        $searchableModules = $this->get('zikula_search_module.internal.searchable_module_collector')->getAll();

        if (0 == count($legacySearchModules) && 0 == count($core14searchableModules) && 0 == count($searchableModules)) {
            return $this->render('@ZikulaSearchModule/Search/unsearchable.html.twig');
        }

        $pluginOptions = [];
        // LEGACY handling (<1.4.0) @deprecated - remove at Core-2.0
        foreach ($legacySearchModules as $module) {
            // if active array is empty, we need to set defaults
            if ($setActiveDefaults) {
                $activeModules[$module['name']] = 1;
            }
            if (isset($module['title'])) {
                $pluginOptions[$module['title']] = \ModUtil::apiFunc($module['title'], 'search', 'options', [
                    'q' => $request->query->get('q', ''),
                ]);
            }
        }
        // 1.4.x type handling @deprecated - remove at Core-2.0
        foreach ($core14searchableModules as $searchableModule) {
            if ($this->getVar('disable_' . $searchableModule['name'], false)) {
                continue;
            }
            if (!$this->hasPermission('ZikulaSearchModule::Item', $searchableModule['name'] . '::', ACCESS_READ)) {
                continue;
            }
            if ($setActiveDefaults) {
                $activeModules[$searchableModule['name']] = 1;
            }
            $moduleBundle = \ModUtil::getModule($searchableModule['name']);
            /** @var $searchableInstance AbstractSearchable */
            $searchableInstance = new $searchableModule['capabilities']['searchable']['class']($this->get('service_container'), $moduleBundle);
            if (!($searchableInstance instanceof AbstractSearchable)) {
                continue;
            }
            $active = !$setActiveDefaults || (isset($activeModules[$searchableModule['name']]) && (1 == $activeModules[$searchableModule['name']]));
            $pluginOptions[$searchableModule['name']] = $searchableInstance->getOptions($active, $request->query->get('modvar', []));
        }

        // Core 2.0 handling
        $moduleFormBuilder = $this->get('form.factory')
            ->createNamedBuilder('modules', 'Symfony\Component\Form\Extension\Core\Type\FormType', [], [
                'auto_initialize' => false,
                'required' => false
            ]);
        foreach ($searchableModules as $moduleName => $searchableInstance) {
            if ($setActiveDefaults) {
                $activeModules[$moduleName] = 1;
            }
            if ($this->getVar('disable_' . $moduleName, false)) {
                continue;
            }
            if (!$this->hasPermission('ZikulaSearchModule::Item', $moduleName . '::', ACCESS_READ)) {
                continue;
            }
            $moduleFormBuilder->add($moduleName, 'Zikula\SearchModule\Form\Type\AmendableModuleSearchType', [
                'label' => $this->get('kernel')->getModule($moduleName)->getMetaData()->getDisplayName(),
                'translator' => $this->getTranslator(),
                'active' => !$setActiveDefaults || (isset($activeModules[$moduleName]) && (1 == $activeModules[$moduleName])),
                'permissionApi' => $this->get('zikula_permissions_module.api.permission')
            ]);
            $searchableInstance->amendForm($moduleFormBuilder->get($moduleName));
        }
        $form = $this->createForm('Zikula\SearchModule\Form\Type\SearchType', [], [
            'translator' => $this->get('translator.default'),
        ]);
        $form->add($moduleFormBuilder->getForm());

        $form->handleRequest($request);
        $noResultsFound = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $searchApi = $this->get('zikula_search_module.api.search_api');
            $formData = $form->getData();
            $formData['numlimit'] = $this->getVar('itemsperpage', 25);
            $formData['firstPage'] = $page < 1;
            $formData['page'] = $page < 1 ? 1 : $page;
            // $searchApi only persists and return Core-2.0 search results
            $result = $searchApi->search($formData['q'], $page < 1, $formData['searchType'], $formData['searchOrder'], $this->getVar('itemsperpage', 25), $page, $formData['modules']);
            $searchApiErrors = $result['errors'];
            // The $result variable is intentionally overwritten to use the legacy result-fetching functionality.
            // in Core-2.0 simply removing the line below will automatically utilize the Core-2.0 functionality above.
            $result = \ModUtil::apiFunc('ZikulaSearchModule', 'user', 'search', $this->mapFormDataToLegacy($formData, $request->request->all())); // @deprecated remove at Core-2.0
            if ($result['resultCount'] > 0) {
                $templateParameters = array_merge($formData, [
                    'resultCount' => $result['resultCount'],
                    'results' => $result['sqlResult'],
                    'router' => $this->get('router'),
                    'limitSummary' => $this->getVar('limitsummary', 200),
                    'errors' => isset($searchApiErrors) ? $searchApiErrors : []
                ]);
                // log the search if on first page
                if ($formData['firstPage']) {
                    $searchApi->log($formData['q']);
                }

                return $this->render('@ZikulaSearchModule/Search/results.html.twig', $templateParameters);
            } else {
                $noResultsFound = true;
            }
        }

        return [
            'noResultsFound' => $noResultsFound,
            'form' => $form->createView(),
            'pluginOptions' => $pluginOptions, // @deprecated remove at Core-2.0
        ];
    }

    /**
     * @Route("/recent")
     * @Template("ZikulaSearchModule:Search:recent.html.twig")
     *
     * Display a list of recent searches
     *
     * @return array
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access
     */
    public function recentAction(Request $request)
    {
        // security check
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $startnum = $request->query->getInt('startnum', 0);
        $itemsPerPage = $this->getVar('itemsperpage', 25);
        $statRepo = $this->get('zikula_search_module.search_stat_repository');
        $items = $statRepo->getStats([], ['date' => 'DESC'], $itemsPerPage, $startnum);

        $templateParameters = [
            'recentSearches' => $items,
            'pager' => [
                'amountOfItems' => $statRepo->countStats(),
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

        return new PlainResponse($this->renderView('@ZikulaSearchModule/Search/opensearch.xml.twig', $templateParameters), Response::HTTP_OK, ['Content-Type' => 'text/xml']);
    }

    /**
     * @param array $data
     * @param array $requestData
     * @return array
     * @deprecated remove at Core-2.0
     */
    private function mapFormDataToLegacy(array $data, array $requestData)
    {
        $data = array_change_key_case($data, CASE_LOWER);
        $data['firstPage'] = $data['firstpage'];
        $data['active'] = [];
        $data['modvar'] = [];
        foreach ($data as $key => $value) {
            if ('modules' == $key) {
                foreach ($value as $module => $optionData) {
                    if (isset($optionData['active']) && $optionData['active']) {
                        $data['active'][$module] = "1";
                    }
                    unset($optionData['active']);
                    $data['modvar'][$module] = $optionData;
                }
            }
        }
        $data['active'] = isset($requestData['active']) ? $data['active'] + $requestData['active'] : $data['active'];
        $data['modvar'] = isset($requestData['modvar']) ? $data['modvar'] + $requestData['modvar'] : $data['modvar'];
        $data['clear'] = false;

        return $data;
    }
}
