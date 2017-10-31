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
        $searchableModules = $this->get('zikula_search_module.internal.searchable_module_collector')->getAll();

        if (0 == count($searchableModules)) {
            return $this->render('@ZikulaSearchModule/Search/unsearchable.html.twig');
        }

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
            $result = $searchApi->search($formData['q'], $page < 1, $formData['searchType'], $formData['searchOrder'], $this->getVar('itemsperpage', 25), $page, $formData['modules']);
            $searchApiErrors = $result['errors'];
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
            'adminMail' => $variableApi->getSystemVar('adminmail'),
            'hasAdultContent' => $variableApi->get('ZikulaSearchModule', 'opensearch_adult_content', false)
        ];

        return new PlainResponse($this->renderView('@ZikulaSearchModule/Search/opensearch.xml.twig', $templateParameters), Response::HTTP_OK, ['Content-Type' => 'text/xml']);
    }
}
