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

namespace Zikula\AdminModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminModuleRepositoryInterface;
use Zikula\AdminModule\Helper\UpdateCheckHelper;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Annotation\PermissionCheck;

/**
 * @Route("/admininterface")
 */
class AdminInterfaceController extends AbstractController
{
    /**
     * @Route("/header")
     *
     * Open the admin container
     */
    public function header(RequestStack $requestStack): Response
    {
        return $this->render('@ZikulaAdminModule/AdminInterface/header.html.twig', [
            'caller' => $requestStack->getMasterRequest()->attributes->all()
        ]);
    }

    /**
     * @Route("/footer")
     *
     * Close the admin container
     */
    public function footer(
        RequestStack $requestStack,
        ExtensionRepositoryInterface $extensionRepository
    ): Response {
        $caller = $requestStack->getMasterRequest()->attributes->all();
        $caller['info'] = !empty($caller['_zkModule']) ? $extensionRepository->get($caller['_zkModule']) : [];

        return $this->render('@ZikulaAdminModule/AdminInterface/footer.html.twig', [
            'caller' => $caller,
            'symfonyVersion' => Kernel::VERSION,
            'phpVersion' => PHP_VERSION
        ]);
    }

    /**
     * @Route("/breadcrumbs", methods = {"GET"})
     * @PermissionCheck("admin")
     *
     * Admin breadcrumbs
     */
    public function breadcrumbs(
        RequestStack $requestStack,
        ExtensionRepositoryInterface $extensionRepository,
        AdminModuleRepositoryInterface $adminModuleRepository,
        AdminCategoryRepositoryInterface $adminCategoryRepository
    ): Response {
        $masterRequest = $requestStack->getMasterRequest();
        $caller = $masterRequest->attributes->all();
        $caller['info'] = !empty($caller['_zkModule']) ? $extensionRepository->get($caller['_zkModule']) : [];

        $requestedCid = $masterRequest->attributes->get('acid');
        $defaultCid = empty($requestedCid) ? $this->getVar('startcategory') : $requestedCid;

        $categoryId = $defaultCid;
        if (!empty($caller['_zkModule']) && 'ZikulaAdminModule' !== $caller['_zkModule']) {
            $moduleRelation = $adminModuleRepository->findOneBy(['mid' => $caller['info']['id']]);
            if (null !== $moduleRelation) {
                $categoryId = $moduleRelation->getCid();
            }
        }
        $caller['category'] = $adminCategoryRepository->find($categoryId);

        return $this->render('@ZikulaAdminModule/AdminInterface/breadCrumbs.html.twig', [
            'caller' => $caller
        ]);
    }

    /**
     * @Route("/securityanalyzer")
     * @PermissionCheck("admin")
     *
     * Display security analyzer
     */
    public function securityanalyzer(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        VariableApiInterface $variableApi
    ): Response {
        $hasSecurityCenter = $kernel->isBundle('ZikulaSecurityCenterModule');

        return $this->render('@ZikulaAdminModule/AdminInterface/securityAnalyzer.html.twig', [
            'security' => [
                'updatecheck' => $variableApi->getSystemVar('updatecheck'),
                'scactive' => $hasSecurityCenter,
                // check for outputfilter
                'useids' => $hasSecurityCenter && 1 === $variableApi->getSystemVar('useids'),
                'idssoftblock' => $variableApi->getSystemVar('idssoftblock')
            ]
        ]);
    }

    /**
     * @Route("/updatecheck")
     * @PermissionCheck("admin")
     *
     * Display update check
     */
    public function updatecheck(
        RequestStack $requestStack,
        ZikulaHttpKernelInterface $kernel,
        UpdateCheckHelper $updateCheckHelper
    ): Response {
        $masterRequest = $requestStack->getMasterRequest();

        return $this->render('@ZikulaAdminModule/AdminInterface/updateCheck.html.twig', [
            'mode' => $kernel->getEnvironment(),
            'caller' => [
                '_route' => $masterRequest->attributes->get('_route'),
                '_route_params' => $masterRequest->attributes->get('_route_params')
            ],
            'updateCheckHelper' => $updateCheckHelper
        ]);
    }

    /**
     * @Route("/menu")
     * @PermissionCheck("admin")
     *
     * Display admin menu
     */
    public function menu(
        RequestStack $requestStack,
        RouterInterface $router,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionMenuCollector $extensionMenuCollector,
        CapabilityApiInterface $capabilityApi,
        AdminModuleRepositoryInterface $adminModuleRepository,
        AdminCategoryRepositoryInterface $adminCategoryRepository
    ): Response {
        $masterRequest = $requestStack->getMasterRequest();
        $currentRequest = $requestStack->getCurrentRequest();

        // get caller info
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $masterRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $masterRequest->attributes->get('_zkFunc');
        $caller['path'] = $masterRequest->getPathInfo();
        $caller['info'] = !empty($caller['_zkModule']) ? $extensionRepository->get($caller['_zkModule']) : [];

        // category we are in
        $requestedCid = $masterRequest->attributes->get('acid');
        $defaultCid = empty($requestedCid) ? $this->getVar('startcategory') : $requestedCid;

        $categoryId = $defaultCid;
        if (!empty($caller['_zkModule']) && 'ZikulaAdminModule' !== $caller['_zkModule']) {
            $moduleRelation = $adminModuleRepository->findOneBy(['mid' => $caller['info']['id']]);
            if (null !== $moduleRelation) {
                $categoryId = $moduleRelation->getCid();
            }
        }
        $caller['category'] = $adminCategoryRepository->find($categoryId);

        // mode requested
        $mode = $currentRequest->attributes->has('mode') ? $currentRequest->attributes->get('mode') : 'categories';
        $mode = in_array($mode, ['categories', 'modules']) ? $mode : 'categories';
        // template requested
        $template = $currentRequest->attributes->has('template') ? $currentRequest->attributes->get('template') : 'tabs';
        $template = in_array($template, ['tabs', 'panel']) ? $template : 'tabs';

        // get admin capable modules
        $adminModules = $capabilityApi->getExtensionsCapableOf('admin');

        // sort modules by displayname
        $moduleNames = [];
        foreach ($adminModules as $key => $module) {
            $moduleNames[$key] = $module['displayname'];
        }
        array_multisort($moduleNames, SORT_ASC, $adminModules);

        $moduleCategories = $adminCategoryRepository->getIndexedCollection('cid');
        $menuModules = [];
        $menuCategories = [];
        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission($adminModule['name'] . '::', '::', ACCESS_EDIT)) {
                continue;
            }

            $categoryAssignment = $adminModuleRepository->findOneBy(['mid' => $adminModule['id']]);
            if (null !== $categoryAssignment) {
                $catid = $categoryAssignment->getCid();
                $order = $categoryAssignment->getSortorder();
            } else {
                $catid = $this->getVar('startcategory');
                $order = 999;
            }

            $menuText = $adminModule['displayname'];

            // url
            try {
                $menuTextUrl = isset($adminModule['capabilities']['admin']['route'])
                    ? $router->generate($adminModule['capabilities']['admin']['route'])
                    : '';
            } catch (RouteNotFoundException $routeNotFoundException) {
                $menuTextUrl = 'javascript:void(0)';
                $menuText .= ' (<i class="fas fa-exclamation-triangle"></i> ' . $this->trans('invalid route') . ')';
            }

            $moduleName = (string) $adminModule['name'];
            $extensionMenu = $extensionMenuCollector->get($moduleName, ExtensionMenuInterface::TYPE_ADMIN);
            if (isset($extensionMenu) && 'modules' === $mode && 'tabs' === $template) {
                $extensionMenu->setChildrenAttribute('class', 'dropdown-menu');
            }

            $module = [
                'menutexturl' => $menuTextUrl,
                'menutext' => $menuText,
                'menutexttitle' => $adminModule['description'],
                'modname' => $adminModule['name'],
                'order' => $order,
                'id' => $adminModule['id'],
                'extensionMenu' => $extensionMenu,
                'icon' => $adminModule['icon']
            ];

            $menuModules[$adminModule['name']] = $module;

            // category menu
            if (!$this->hasPermission('ZikulaAdminModule:Category:', $moduleCategories[$catid]['name'] . '::' . $moduleCategories[$catid]['cid'], ACCESS_ADMIN)) {
                continue;
            }

            $categorySortOrder = $moduleCategories[$catid]['sortorder'];
            $menuCategories[$categorySortOrder]['title'] = $moduleCategories[$catid]['name'];
            $menuCategories[$categorySortOrder]['url'] = $router->generate('zikulaadminmodule_admin_adminpanel', [
                'acid' => $moduleCategories[$catid]['cid']
            ]);
            $menuCategories[$categorySortOrder]['description'] = $moduleCategories[$catid]['description'];
            $menuCategories[$categorySortOrder]['icon'] = $moduleCategories[$catid]['icon'];
            $menuCategories[$categorySortOrder]['cid'] = $moduleCategories[$catid]['cid'];
            $menuCategories[$categorySortOrder]['modules'][$adminModule['name']] = $module;
        }

        // add empty categories
        /** @var AdminCategoryEntity[] $moduleCategories */
        foreach ($moduleCategories as $moduleCategory) {
            if (array_key_exists($moduleCategory->getSortorder(), $menuCategories)) {
                continue;
            }
            if (!$this->hasPermission('ZikulaAdminModule:Category:', $moduleCategory->getName() . '::' . $moduleCategory->getCid(), ACCESS_ADMIN)) {
                continue;
            }

            $menuCategories[$moduleCategory->getSortOrder()] = [
                'title' => $moduleCategory->getName(),
                'url' => $router->generate('zikulaadminmodule_admin_adminpanel', [
                    'acid' => $moduleCategory->getCid()
                ]),
                'description' => $moduleCategory->getDescription(),
                'cid' => $moduleCategory->getCid(),
                'modules' => []
            ];
        }
        ksort($menuCategories);
        $fullTemplateName = $mode . '.' . $template;

        return $this->render("@ZikulaAdminModule/AdminInterface/${fullTemplateName}.html.twig", [
            'adminMenu' => ('categories' === $mode) ? $menuCategories : $menuModules,
            'mode' => $mode,
            'caller' => $caller
        ]);
    }
}
