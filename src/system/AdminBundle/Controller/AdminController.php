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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\AdminBundle\Helper\AdminCategoryHelper;
use Zikula\AdminBundle\Helper\AdminLinksHelper;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsBundle\Api\ApiInterface\CapabilityApiInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

/**
 * Administrative controllers for the admin module
 */
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'zikulaadminbundle_admin_index')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('zikulaadminbundle_admin_view');
    }

    /**
     * @PermissionCheck("edit")
     * @Theme("admin")
     * @Template("@ZikulaAdmin/Admin/view.html.twig")
     *
     * Views all admin categories.
     */
    #[Route('/categories', name: 'zikulaadminbundle_admin_view', methods: ['GET'])]
    public function view(AdminCategoryHelper $categoryHelper): array
    {
        return [
            'categories' => $categoryHelper->getCategories(),
        ];
    }

    /**
     * @PermissionCheck("edit")
     * @Theme("admin")
     * @Template("@ZikulaAdmin/Admin/adminpanel.html.twig")
     *
     * Displays main admin panel for a category.
     *
     * @return array|Response
     * @throws AccessDeniedException Thrown if the user doesn't have edit permission for the module
     */
    #[Route('/panel/{acslug}', name: 'zikulaadminbundle_admin_adminpanel', methods: ['GET'])]
    public function adminpanel(
        CapabilityApiInterface $capabilityApi,
        RouterInterface $router,
        AdminCategoryHelper $categoryHelper,
        AdminLinksHelper $adminLinksHelper,
        ExtensionMenuCollector $extensionMenuCollector,
        string $acslug = null
    ) {
        $category = $categoryHelper->getCurrentCategory();

        // Check to see if we have access to the requested category.
        if (!$this->hasPermission('ZikulaAdminModule::', '::' . $category->getSlug(), ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $templateParameters = [
            'category' => $category,
        ];

        $bundleNames = $categoryHelper->getBundleAssignments($category);

        // get admin capable modules
        $adminModules = $capabilityApi->getExtensionsCapableOf('admin');
        $adminLinks = [];
        $sortOrder = 1;
        foreach ($adminModules as $adminModule) {
            if (!in_array($adminModule->getName(), $bundleNames, true)) {
                continue;
            }
            if (!$this->hasPermission($adminModule->getName() . '::', 'ANY', ACCESS_EDIT)) {
                continue;
            }

            $bundleInfo = $adminModule->getMetaData();
            $menuText = $bundleInfo->getDisplayName(); // . ' (' . $adminModule->getName() . ')';

            try {
                $menuTextUrl = isset($bundleInfo->getCapabilities()['admin']['route'])
                    ? $router->generate($bundleInfo->getCapabilities()['admin']['route'])
                    : '';
            } catch (RouteNotFoundException $routeNotFoundException) {
                $menuTextUrl = 'javascript:void(0)';
                $menuText .= ' (⚠️ ' . $this->trans('invalid route') . ')';
            }

            $moduleName = (string) $adminModule->getName();
            /** @var \Knp\Menu\ItemInterface $extensionMenu */
            $extensionMenu = $extensionMenuCollector->get($moduleName, ExtensionMenuInterface::TYPE_ADMIN);
            if (isset($extensionMenu)) {
                $extensionMenu->setChildrenAttribute('class', 'dropdown-menu');
            }

            $adminLinks[] = [
                'menuTextUrl' => $menuTextUrl,
                'menuText' => $menuText,
                'menuTextTitle' => $bundleInfo->getDescription(),
                'moduleName' => $adminModule->getName(),
                'adminIcon' => $bundleInfo->getIcon(),
                'order' => ++$sortOrder,
                'extensionMenu' => $extensionMenu
            ];
        }
        $templateParameters['adminLinks'] = $adminLinksHelper->sortAdminModsByOrder($adminLinks);

        return $templateParameters;
    }

    /**
     * @Theme("admin")
     *
     * Displays main category menu.
     */
    #[Route('/categorymenu/{acslug}', name: 'zikulaadminbundle_admin_categorymenu', methods: ['GET'])]
    public function categorymenu(
        CapabilityApiInterface $capabilityApi,
        RouterInterface $router,
        AdminCategoryHelper $categoryHelper,
        AdminLinksHelper $adminLinksHelper,
        string $acslug = null
    ): Response {
        $categories = $categoryHelper->getCategories();

        // get admin capable modules
        $adminModules = $capabilityApi->getExtensionsCapableOf('admin');
        $adminLinks = [];
        foreach ($categories as $category) {
            $sortOrder = 1;
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

                $slug = $category->getSlug();

                $adminLinks[$slug][] = [
                    'menuTextUrl' => $menuTextUrl,
                    'menuText' => $menuText,
                    'menuTextTitle' => $bundleInfo->getDescription(),
                    'moduleName' => $adminModule->getName(),
                    'order' => ++$sortOrder,
                    'icon' => $bundleInfo->getIcon(),
                ];
            }
        }

        foreach ($adminLinks as $slug => $links) {
            $adminLinks[$slug] = $adminLinksHelper->sortAdminModsByOrder($links);
        }

        $menuOptions = [];
        $possibleCategoryIds = [];
        $permission = false;

        if (isset($categories) && is_array($categories)) {
            foreach ($categories as $category) {
                $slug = $category->getSlug();
                // only categories containing modules where the current user has permissions will
                // be shown, all others will be hidden
                // admin will see all categories
                if ($this->hasPermission('.*', '.*', ACCESS_ADMIN)
                    || (isset($adminLinks[$slug]) && count($adminLinks[$slug]))
                ) {
                    $menuOption = [
                        'url' => $router->generate('zikulaadminbundle_admin_adminpanel', ['acslug' => $slug]),
                        'title' => $category['name'],
                        'description' => $category['description'],
                        'slug' => $slug,
                        'items' => $adminLinks[$slug] ?? []
                    ];

                    $menuOptions[$slug] = $menuOption;
                    $possibleCategoryIds[] = $slug;

                    if ($acslug === $slug) {
                        $permission = true;
                    }
                }
            }
        }

        // if permission is false we are not allowed to see this category because its
        // empty and we are not admin
        if (false === $permission) {
            // show the first category
            $acslug = !empty($possibleCategoryIds) ? (int) $possibleCategoryIds[0] : null;
        }

        return $this->render('@ZikulaAdmin/Admin/categoryMenu.html.twig', [
            'currentCategory' => $acslug,
            'menuOptions' => $menuOptions
        ]);
    }
}
