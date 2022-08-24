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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\AdminBundle\Helper\AdminCategoryHelper;
use Zikula\AdminBundle\Helper\AdminLinksHelper;
use Zikula\ExtensionsBundle\Api\ApiInterface\CapabilityApiInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

#[Route('/admin')]
class AdminController extends AbstractController
{
    public function __construct(private readonly PermissionApiInterface $permissionApi, private readonly TranslatorInterface $translator)
    {
    }

    #[Route('', name: 'zikulaadminbundle_admin_index')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('zikulaadminbundle_admin_view');
    }

    /**
     * @PermissionCheck("edit")
     * @Theme("admin")
     *
     * Views all admin categories.
     */
    #[Route('/categories', name: 'zikulaadminbundle_admin_view', methods: ['GET'])]
    public function view(AdminCategoryHelper $categoryHelper): Response
    {
        return $this->render('@ZikulaAdmin/Admin/view.html.twig', [
            'categories' => $categoryHelper->getCategories(),
        ]);
    }

    /**
     * @PermissionCheck("edit")
     * @Theme("admin")
     *
     * Displays main admin panel for a category.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permission for the bundle
     */
    #[Route('/panel/{acslug}', name: 'zikulaadminbundle_admin_adminpanel', methods: ['GET'])]
    public function adminpanel(
        CapabilityApiInterface $capabilityApi,
        RouterInterface $router,
        AdminCategoryHelper $categoryHelper,
        AdminLinksHelper $adminLinksHelper,
        ExtensionMenuCollector $extensionMenuCollector,
        string $acslug = null
    ): Response {
        $category = $categoryHelper->getCurrentCategory();

        // Check to see if we have access to the requested category.
        if (!$this->permissionApi->hasPermission('ZikulaAdminBundle::', '::' . $category->getSlug(), ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $templateParameters = [
            'intl_installed' => extension_loaded('intl'),
            'category' => $category,
        ];

        $bundleNames = $categoryHelper->getBundleAssignments($category);

        // get admin capable bundles
        $adminBundles = $capabilityApi->getExtensionsCapableOf('admin');
        $adminLinks = [];
        $sortOrder = 1;
        foreach ($adminBundles as $adminBundle) {
            if (!in_array($adminBundle->getName(), $bundleNames, true)) {
                continue;
            }
            if (!$this->permissionApi->hasPermission($adminBundle->getName() . '::', 'ANY', ACCESS_EDIT)) {
                continue;
            }

            $bundleInfo = $adminBundle->getMetaData();
            $menuText = $bundleInfo->getDisplayName(); // . ' (' . $adminBundle->getName() . ')';

            try {
                $menuTextUrl = isset($bundleInfo->getCapabilities()['admin']['route'])
                    ? $router->generate($bundleInfo->getCapabilities()['admin']['route'])
                    : '';
            } catch (RouteNotFoundException $routeNotFoundException) {
                $menuTextUrl = 'javascript:void(0)';
                $menuText .= ' (⚠️ ' . $this->translator->trans('invalid route') . ')';
            }

            $bundleName = (string) $adminBundle->getName();
            /** @var \Knp\Menu\ItemInterface $extensionMenu */
            $extensionMenu = $extensionMenuCollector->get($bundleName, ExtensionMenuInterface::TYPE_ADMIN);
            if (isset($extensionMenu)) {
                $extensionMenu->setChildrenAttribute('class', 'dropdown-menu');
            }

            $adminLinks[] = [
                'menuTextUrl' => $menuTextUrl,
                'menuText' => $menuText,
                'menuTextTitle' => $bundleInfo->getDescription(),
                'bundleName' => $adminBundle->getName(),
                'adminIcon' => $bundleInfo->getIcon(),
                'order' => ++$sortOrder,
                'extensionMenu' => $extensionMenu
            ];
        }
        $templateParameters['adminLinks'] = $adminLinksHelper->sortAdminModsByOrder($adminLinks);

        return $this->render('@ZikulaAdmin/Admin/adminpanel.html.twig', $templateParameters);
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

        // get admin capable bundles
        $adminBundles = $capabilityApi->getExtensionsCapableOf('admin');
        $adminLinks = [];
        foreach ($categories as $category) {
            $sortOrder = 1;
            foreach ($adminBundles as $adminBundle) {
                $bundleName = $adminBundle->getName();
                $bundleNames = $categoryHelper->getBundleAssignments($category);
                if (!in_array($bundleName, $bundleNames, true)) {
                    continue;
                }
                if (!$this->permissionApi->hasPermission($bundleName . '::', '::', ACCESS_EDIT)) {
                    continue;
                }

                $bundleInfo = $adminBundle->getMetaData();
                $menuText = $bundleInfo->getDisplayName();
                try {
                    $menuTextUrl = isset($bundleInfo->getCapabilities()['admin']['route'])
                        ? $router->generate($bundleInfo->getCapabilities()['admin']['route'])
                        : '';
                } catch (RouteNotFoundException $routeNotFoundException) {
                    $menuTextUrl = 'javascript:void(0)';
                    $menuText .= ' (<i class="fas fa-exclamation-triangle"></i> ' . $this->translator->trans('invalid route') . ')';
                }

                $slug = $category->getSlug();

                $adminLinks[$slug][] = [
                    'menuTextUrl' => $menuTextUrl,
                    'menuText' => $menuText,
                    'menuTextTitle' => $bundleInfo->getDescription(),
                    'bundleName' => $adminBundle->getName(),
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
                // only categories containing bundles where the current user has permissions will
                // be shown, all others will be hidden
                // admin will see all categories
                if ($this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN)
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

    /**
     * @Theme("admin")
     *
     * Displays the content of {@see phpinfo()}.
     */
    #[Route('/phpinfo', name: 'zikulaadminbundle_admin_phpinfo', methods: ['GET'])]
    public function phpinfo(): Response
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        $phpinfo = str_replace(
            'bundle_Zend Optimizer',
            'bundle_Zend_Optimizer',
            preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo)
        );

        return $this->render('@ZikulaAdmin/Admin/phpinfo.html.twig', [
            'phpinfo' => $phpinfo,
        ]);
    }
}
