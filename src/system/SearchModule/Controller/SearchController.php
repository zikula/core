<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\SearchModule\Api\ApiInterface\SearchApiInterface;
use Zikula\SearchModule\Collector\SearchableModuleCollector;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Form\Type\AmendableModuleSearchType;
use Zikula\SearchModule\Form\Type\SearchType;

/**
 * @PermissionCheck("read")
 */
class SearchController extends AbstractController
{
    /**
     * @Route("/{page}", requirements={"page"="\d+"})
     * @Template("@ZikulaSearchModule/Search/execute.html.twig")
     *
     * @return array|Response
     */
    public function executeAction(
        Request $request,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        ZikulaHttpKernelInterface $kernel,
        SearchableModuleCollector $collector,
        SearchApiInterface $searchApi,
        int $page = 1
    ) {
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

        $moduleFormBuilder = $formFactory
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
                'label' => $kernel->getModule($moduleName)->getMetaData()->getDisplayName(),
                'active' => !$setActiveDefaults || (isset($activeModules[$moduleName]) && (1 === $activeModules[$moduleName]))
            ]);
            $searchableInstance->amendForm($moduleFormBuilder->get($moduleName));
        }
        $keyword = $request->query->get('q');
        $form = $this->createForm(SearchType::class, ['q' => $keyword]);
        $form->add($moduleFormBuilder->getForm());

        $form->handleRequest($request);
        $noResultsFound = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $pageSize = $this->getVar('itemsperpage', 25);
            $formData['numlimit'] = $pageSize;
            $formData['firstPage'] = $page < 2;
            $formData['page'] = 0 < $page ? $page : 1;
            $result = $searchApi->search($formData['q'], 2 > $page, $formData['searchType'], $formData['searchOrder'], $page, $pageSize, $formData['modules']);
            $searchApiErrors = $result['errors'];
            if (0 < $result['paginator']->getNumResults()) {
                $result['paginator']->setRoute('zikulasearchmodule_search_execute');
                $templateParameters = array_merge($formData, [
                    'paginator' => $result['paginator'],
                    'router' => $router,
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
     * @Route("/recent/{page}", methods = {"GET"}, requirements={"page" = "\d+"})
     * @Template("@ZikulaSearchModule/Search/recent.html.twig")
     *
     * Display a list of recent searches.
     */
    public function recentAction(
        Request $request,
        SearchStatRepositoryInterface $searchStatRepository,
        int $page = 1
    ): array {
        $pageSize = $this->getVar('itemsperpage', 25);
        $paginator = $searchStatRepository->getStats([], ['date' => 'DESC'], $page, $pageSize);
        $paginator->setRoute('zikulasearchmodule_search_recent');

        return [
            'paginator' => $paginator
        ];
    }

    /**
     * @Route("/opensearch", options={"i18n"=false})
     *
     * Generate xml for opensearch syndication.
     */
    public function opensearchAction(
        SiteDefinitionInterface $site,
        VariableApiInterface $variableApi
    ): PlainResponse {
        $templateParameters = [
            'siteName' => $site->getName(),
            'slogan' => $site->getSlogan(),
            'adminMail' => $variableApi->getSystemVar('adminmail'),
            'hasAdultContent' => $variableApi->get('ZikulaSearchModule', 'opensearch_adult_content')
        ];

        $output = $this->renderView('@ZikulaSearchModule/Search/opensearch.xml.twig', $templateParameters);

        return new PlainResponse($output, Response::HTTP_OK, ['Content-Type' => 'text/xml']);
    }
}
