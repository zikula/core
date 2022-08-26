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

namespace Zikula\ThemeBundle\Controller\Dashboard;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\GroupsBundle\Controller\GroupEntityCrudController;
use Zikula\GroupsBundle\Entity\GroupEntity;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\PermissionsBundle\Entity\PermissionEntity;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\ThemeBundle\Helper\AdminBundleHelper;
use Zikula\ThemeBundle\Helper\AdminCategoryHelper;

class AdminDashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminCategoryHelper $adminCategoryHelper,
        private readonly AdminBundleHelper $adminBundleHelper,
        private readonly ExtensionMenuCollector $extensionMenuCollector,
        private readonly PermissionApiInterface $permissionApi
    ) {
    }

    #[Route('/admin', name: 'home_admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(GroupEntityCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');

        /**
         * TODO
            {% if not intl_installed|default(false) %}
                <p class="alert alert-danger">
                    {% set args = {'%ext%': '<code>INTL</code>'|raw, '%locale%': '<code>EN</code>'|raw} %}
                    {% trans with args %}WARNING: The PHP Extension %ext% is not loaded. All functions using this will default to %locale%. Seek assistance from your provider to install.{% endtrans %}
                </p>
            {% endif %}
         */
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Zikula Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('TEST', 'fas fa-flask');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        //yield MenuItem::linktoRoute('Back to the website', 'fas fa-home', 'home');
        yield MenuItem::linktoUrl('Back to the website', 'fas fa-home', '/');
        yield MenuItem::linkToUrl('Symfony', 'fab fa-symfony', 'https://symfony.com');
        yield MenuItem::linkToUrl('Zikula', 'fas fa-rocket', 'https://ziku.la');
        yield MenuItem::linkToUrl('Zikula Docs', 'fas fa-book', 'https://docs.ziku.la/');
        yield MenuItem::linkToUrl('ModuleStudio', 'fas fa-wand-sparkles', 'https://modulestudio.de/en/');

        yield MenuItem::linkToCrud('Groups', 'fas fa-people-group', GroupEntity::class);
        yield MenuItem::linkToCrud('Permissions', 'fas fa-lock', PermissionEntity::class);

        foreach ($this->adminCategoryHelper->getCategories() as $category) {
            yield MenuItem::section($category->getName(), $category->getIcon());
            $bundleNames = $this->adminCategoryHelper->getBundleAssignments($category);
            $adminBundles = $this->adminBundleHelper->getAdminCapableBundles();
            foreach ($adminBundles as $adminBundle) {
                if (!in_array($adminBundle->getName(), $bundleNames, true)) {
                    continue;
                }
                if (!$this->permissionApi->hasPermission($adminBundle->getName() . '::', 'ANY', ACCESS_EDIT)) {
                    continue;
                }

                $bundleInfo = $adminBundle->getMetaData();
                [$menuTextUrl, $menuText] = $this->adminBundleHelper->getAdminRouteInformation($bundleInfo);

                $bundleName = (string) $adminBundle->getName();
                /** @var \Knp\Menu\ItemInterface $extensionMenu */
                $extensionMenu = $this->extensionMenuCollector->get($bundleName, ExtensionMenuInterface::TYPE_ADMIN);
                $isSubMenu = isset($extensionMenu);

                if ($isSubMenu) {
                    yield MenuItem::subMenu($menuText, $bundleInfo->getIcon())->setSubItems([
                        MenuItem::linktoRoute('Start', $bundleInfo->getIcon(), $menuTextUrl),
                        MenuItem::linkToUrl('Symfony', 'fab fa-symfony', 'https://symfony.com'),
                        MenuItem::linkToUrl('Zikula', 'fas fa-rocket', 'https://ziku.la'),
                        MenuItem::linkToUrl('ModuleStudio', 'fas fa-wand-sparkles', 'https://modulestudio.de/en/'),
                        //MenuItem::linkToCrud('Categories', 'fa fa-tags', Category::class),
                        //MenuItem::linkToCrud('Posts', 'fa fa-file-text', BlogPost::class),
                        //MenuItem::linkToCrud('Comments', 'fa fa-comment', Comment::class),
                    ]);
                } else {
                    yield MenuItem::linktoRoute($menuText, $bundleInfo->getIcon(), $menuTextUrl);
                }
            }
        }
    }
}
