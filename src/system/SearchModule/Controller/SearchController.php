<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SearchModule\Api\ApiInterface\SearchApiInterface;
use Zikula\SearchModule\Collector\SearchableModuleCollector;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Form\Type\AmendableModuleSearchType;
use Zikula\SearchModule\Form\Type\SearchType;

class SearchController extends AbstractController
{
    /**
     * @Route("/{page}", requirements={"page"="\d+"})
     * @Template("ZikulaSearchModule:Search:execute.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access
     * @return array|Response
     */
    public function executeAction(
        Request $request,
        SearchableModuleCollector $collector,
        SearchApiInterface $searchApi,
        int $page = -1
    ) {
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
        $searchableModules = $collector->getAll();
        if (0 === count($searchableModules)) {
            return $this->render('@ZikulaSearchModule/Search/unsearchable.html.twig');
        }

        $moduleFormBuilder = $this->get('form.factory')
            ->createNamedBuilder('modules', FormType::class, [], [
                'auto_initialize' => false,
                'required' => false
            ])
        ;
        foreach ($searchableModules as $moduleName => $searchableInstance) {
            if ($setActiveDefaults) {
                $activeModules[$moduleName] = 1;
            }
            if ($this->getVar('disable_' . $moduleName)) {
                continue;
            }
            if (!$this->hasPermission('ZikulaSearchModule::Item', $moduleName . '::', ACCESS_READ)) {
                continue;
            }
            $moduleFormBuilder->add($moduleName, AmendableModuleSearchType::class, [
                'label' => $this->get('kernel')->getModule($moduleName)->getMetaData()->getDisplayName(),
                'active' => !$setActiveDefaults || (isset($activeModules[$moduleName]) && (1 === $activeModules[$moduleName]))
            ]);
            $searchableInstance->amendForm($moduleFormBuilder->get($moduleName));
        }
        $form = $this->createForm(SearchType::class, []);
        $form->add($moduleFormBuilder->getForm());

        $form->handleRequest($request);
        $noResultsFound = false;

        if ($form->isSubmitted() && $form->isValid()) {
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
                    'errors' => $searchApiErrors ?? []
                ]);
                // log the search if on first page
                if ($formData['firstPage']) {
                    $searchApi->log($formData['q']);
                }

                return $this->render('@ZikulaSearchModule/Search/results.html.twig', $templateParameters);
            }
            $noResultsFound = true;
        }

        return [
            'noResultsFound' => $noResultsFound,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/recent")
     * @Template("ZikulaSearchModule:Search:recent.html.twig")
     *
     * Display a list of recent searches.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access
     */
    public function recentAction(
        Request $request,
        SearchStatRepositoryInterface $searchStatRepository
    ): array {
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $startnum = $request->query->getInt('startnum');
        $itemsPerPage = $this->getVar('itemsperpage', 25);
        $items = $searchStatRepository->getStats([], ['date' => 'DESC'], $itemsPerPage, $startnum);

        return [
            'recentSearches' => $items,
            'pager' => [
                'amountOfItems' => $searchStatRepository->countStats(),
                'itemsPerPage'  => $itemsPerPage
            ]
        ];
    }

    /**
     * @Route("/opensearch", options={"i18n"=false})
     *
     * Generate xml for opensearch syndication.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have read access
     */
    public function opensearchAction(VariableApiInterface $variableApi): PlainResponse
    {
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $templateParameters = [
            'siteName' => $variableApi->getSystemVar('sitename', $variableApi->getSystemVar('sitename_en')),
            'slogan' => $variableApi->getSystemVar('slogan', $variableApi->getSystemVar('slogan_en')),
            'adminMail' => $variableApi->getSystemVar('adminmail'),
            'hasAdultContent' => $variableApi->get('ZikulaSearchModule', 'opensearch_adult_content')
        ];

        $output = $this->renderView('@ZikulaSearchModule/Search/opensearch.xml.twig', $templateParameters);

        return new PlainResponse($output, Response::HTTP_OK, ['Content-Type' => 'text/xml']);
    }
}
