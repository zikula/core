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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\GroupsBundle\Controller\GroupEntityCrudController;
use Zikula\GroupsBundle\Entity\GroupEntity;
use Zikula\PermissionsBundle\Entity\PermissionEntity;
use Zikula\ThemeBundle\ExtensionMenu\MenuContext;

class AdminDashboardController extends AbstractThemedDashboardController
{
    protected function getName(): string
    {
        return 'admin';
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->site->getName() . ' Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('TEST', 'fas fa-flask');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linktoRoute('Back to the website', 'fas fa-home', 'home');
        yield MenuItem::linktoUrl('Back to the website', 'fas fa-home', '/');
        yield MenuItem::linkToUrl('Symfony', 'fab fa-symfony', 'https://symfony.com')->setLinkTarget('_target');
        yield MenuItem::linkToUrl('Zikula', 'fas fa-rocket', 'https://ziku.la')->setLinkTarget('_target');
        yield MenuItem::linkToUrl('Zikula Docs', 'fas fa-book', 'https://docs.ziku.la/')->setLinkTarget('_target');
        yield MenuItem::linkToUrl('ModuleStudio', 'fas fa-wand-sparkles', 'https://modulestudio.de/en/')->setLinkTarget('_target');

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
                /*if (!$this->permissionApi->hasPermission($adminBundle->getName() . '::', 'ANY', ACCESS_EDIT)) {
                    continue;
                }*/

                $bundleInfo = $adminBundle->getMetaData();
                [$menuTextUrl, $menuText] = $this->adminBundleHelper->getAdminRouteInformation($bundleInfo);

                $bundleName = (string) $adminBundle->getName();
                $extensionMenuItems = $this->extensionMenuCollector->get($bundleName, MenuContext::ADMIN);
                $isSubMenu = isset($extensionMenuItems);

                if ($isSubMenu) {
                    yield MenuItem::subMenu($menuText, $bundleInfo->getIcon())->setSubItems(iterator_to_array($extensionMenuItems));
                } else {
                    yield MenuItem::linktoRoute($menuText, $bundleInfo->getIcon(), $menuTextUrl);
                }
            }
        }
    }

    #[Route('/admin', name: 'home_admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        return $this->redirect($this->adminUrlGenerator->setController(GroupEntityCrudController::class)->generateUrl());

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
}
