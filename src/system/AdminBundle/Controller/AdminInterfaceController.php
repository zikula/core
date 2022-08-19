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

namespace Zikula\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Zikula\AdminBundle\Helper\AdminCategoryHelper;
use Zikula\AdminBundle\Helper\UpdateCheckHelper;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsBundle\Api\ApiInterface\CapabilityApiInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;

#[Route('/admin/interface')]
class AdminInterfaceController extends AbstractController
{
    /**
     * Open the admin container
     */
    #[Route('/header', name: 'zikulaadminbundle_admininterface_header')]
    public function header(RequestStack $requestStack, ZikulaHttpKernelInterface $kernel): Response
    {
        return $this->render('@ZikulaAdmin/AdminInterface/header.html.twig', [
            'caller' => $this->getCallerInfo($requestStack, $kernel),
        ]);
    }

    /**
     * Close the admin container
     */
    #[Route('/footer', name: 'zikulaadminbundle_admininterface_footer')]
    public function footer(RequestStack $requestStack, ZikulaHttpKernelInterface $kernel): Response
    {
        return $this->render('@ZikulaAdmin/AdminInterface/footer.html.twig', [
            'caller' => $this->getCallerInfo($requestStack, $kernel),
            'symfonyVersion' => Kernel::VERSION,
            'phpVersion' => PHP_VERSION
        ]);
    }

    /**
     * @PermissionCheck("admin")
     *
     * Admin breadcrumbs
     */
    #[Route('/breadcrumbs', name: 'zikulaadminbundle_admininterface_breadcrumbs', methods: ['GET'])]
    public function breadcrumbs(RequestStack $requestStack, ZikulaHttpKernelInterface $kernel, AdminCategoryHelper $categoryHelper): Response
    {
        $caller = $this->getCallerInfo($requestStack, $kernel);
        $caller['category'] = $categoryHelper->getCurrentCategory();

        return $this->render('@ZikulaAdmin/AdminInterface/breadCrumbs.html.twig', [
            'caller' => $caller
        ]);
    }

    private function getCallerInfo(RequestStack $requestStack, ZikulaHttpKernelInterface $kernel): array
    {
        $caller = $requestStack->getMainRequest()->attributes->all();
        $caller['info'] = !empty($caller['_zkModule']) ? $kernel->getBundle($caller['_zkModule'])->getMetaData() : [];

        return $caller;
    }

    /**
     * @PermissionCheck("admin")
     *
     * Display update check
     */
    #[Route('/updatecheck', name: 'zikulaadminbundle_admininterface_updatecheck', methods: ['GET'])]
    public function updatecheck(
        RequestStack $requestStack,
        ZikulaHttpKernelInterface $kernel,
        UpdateCheckHelper $updateCheckHelper
    ): Response {
        $mainRequest = $requestStack->getMainRequest();

        return $this->render('@ZikulaAdmin/AdminInterface/updateCheck.html.twig', [
            'mode' => $kernel->getEnvironment(),
            'caller' => [
                '_route' => $mainRequest->attributes->get('_route'),
                '_route_params' => $mainRequest->attributes->get('_route_params')
            ],
            'updateCheckHelper' => $updateCheckHelper
        ]);
    }

    /**
     * @PermissionCheck("admin")
     *
     * Display admin menu
     */
    #[Route('/menu', name: 'zikulaadminbundle_admininterface_menu', methods: ['GET'])]
    public function menu(
        RequestStack $requestStack,
        ZikulaHttpKernelInterface $kernel,
        RouterInterface $router,
        ExtensionMenuCollector $extensionMenuCollector,
        CapabilityApiInterface $capabilityApi,
        AdminCategoryHelper $categoryHelper
    ): Response {
        $mainRequest = $requestStack->getMainRequest();
        $currentRequest = $requestStack->getCurrentRequest();

        $caller = $this->getCallerInfo($requestStack, $kernel);
        $caller['_zkModule'] = $mainRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $mainRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $mainRequest->attributes->get('_zkFunc');
        $caller['path'] = $mainRequest->getPathInfo();
        $caller['category'] = $categoryHelper->getCurrentCategory();

        // mode requested
        $mode = $currentRequest->attributes->has('mode') ? $currentRequest->attributes->get('mode') : 'categories';
        $mode = in_array($mode, ['categories', 'modules'], true) ? $mode : 'categories';
        // template requested
        $template = $currentRequest->attributes->has('template') ? $currentRequest->attributes->get('template') : 'tabs';
        $template = in_array($template, ['tabs', 'panel'], true) ? $template : 'tabs';

        $categories = $categoryHelper->getCategories();

        // category data
        foreach ($categories as $category) {
            if (!$this->hasPermission('ZikulaAdminModule:Category:', $category->getName() . '::' . $category->getSlug(), ACCESS_ADMIN)) {
                continue;
            }

            $menuCategories[$category->getSortOrder()] = [
                'title' => $category->getName(),
                'url' => $router->generate('zikulaadminbundle_admin_adminpanel', [
                    'acslug' => $category->getSlug()
                ]),
                'description' => $category->getDescription(),
                'slug' => $category->getSlug(),
                'icon' => $category->getIcon(),
                'bundles' => []
            ];
        }
        ksort($menuCategories);

        // bundle data
        // get admin capable modules
        $adminModules = $capabilityApi->getExtensionsCapableOf('admin');
        $menuModules = [];
        foreach ($categories as $category) {
            foreach ($adminModules as $adminModule) {
                $bundleName = $adminModule->getName();
                $bundleNames = $categoryHelper->getBundleAssignments($category);
                if (!in_array($bundleName, $bundleNames, true)) {
                    continue;
                }
                if (!$this->hasPermission($bundleName . '::', '::', ACCESS_EDIT)) {
                    continue;
                }

                $bundleInfo = $adminModule->getMetaData();
                $menuText = $bundleInfo->getDisplayName();
                try {
                    $menuTextUrl = isset($bundleInfo->getCapabilities()['admin']['route'])
                        ? $router->generate($bundleInfo->getCapabilities()['admin']['route'])
                        : '';
                } catch (RouteNotFoundException $routeNotFoundException) {
                    $menuTextUrl = 'javascript:void(0)';
                    $menuText .= ' (<i class="fas fa-exclamation-triangle"></i> ' . $this->trans('invalid route') . ')';
                }

                $extensionMenu = $extensionMenuCollector->get($bundleName, ExtensionMenuInterface::TYPE_ADMIN);
                if (isset($extensionMenu) && 'modules' === $mode && 'tabs' === $template) {
                    $extensionMenu->setChildrenAttribute('class', 'dropdown-menu');
                }

                $bundleData = [
                    'menuTextUrl' => $menuTextUrl,
                    'menuText' => $menuText,
                    'menuTextTitle' => $bundleInfo->getDescription(),
                    'moduleName' => $adminModule->getName(),
                    'order' => $category->getSortOrder(),
                    'extensionMenu' => $extensionMenu,
                    'icon' => $bundleInfo->getIcon(),
                ];

                $menuModules[$adminModule->getName()] = $bundleData;

                // category menu
                if (!$this->hasPermission('ZikulaAdminModule:Category:', $category->getName() . '::', ACCESS_ADMIN)) {
                    continue;
                }

                $menuCategories[$category->getSortOrder()]['bundles'][$adminModule->getName()] = $bundleData;
            }
        }

        $fullTemplateName = $mode . '.' . $template;

        return $this->render('@ZikulaAdmin/AdminInterface/' . $fullTemplateName . '.html.twig', [
            'adminMenu' => ('categories' === $mode) ? $menuCategories : $menuModules,
            'mode' => $mode,
            'caller' => $caller
        ]);
    }
}
