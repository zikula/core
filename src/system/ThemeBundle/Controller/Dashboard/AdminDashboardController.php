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
use function Symfony\Component\Translation\t;
use Zikula\GroupsBundle\Entity\Group;
use Zikula\PermissionsBundle\Entity\Permission;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;

class AdminDashboardController extends AbstractThemedDashboardController
{
    protected function getName(): string
    {
        return 'admin';
    }

    public function configureDashboard(): Dashboard
    {
        return parent::getDashboardWithBranding(true, t('Administration'));
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(t('Dashboard'), 'fa fa-gauge-high');
        // yield MenuItem::linktoRoute(t('Website frontend'), 'fas fa-home', 'user_dashboard');
        yield MenuItem::linktoUrl(t('Website frontend'), 'fas fa-home', '/');

        yield MenuItem::linkToCrud(t('Groups'), 'fas fa-people-group', Group::class);
        yield MenuItem::linkToCrud(t('Permissions'), 'fas fa-lock', Permission::class);
        yield MenuItem::linkToCrud(t('Add permission'), 'fas fa-plus', Permission::class)
            ->setAction('new');

        foreach ($this->adminCategoryHelper->getCategories() as $category) {
            yield MenuItem::section($category->getName(), $category->getIcon());
            $bundleNames = $this->adminCategoryHelper->getBundleAssignments($category);
            $adminBundles = $this->adminBundleHelper->getAdminCapableBundles();
            foreach ($adminBundles as $bundle) {
                if (!in_array($bundle->getName(), $bundleNames, true)) {
                    continue;
                }
                /*if (!$this->permissionApi->hasPermission($bundle->getName() . '::', 'ANY', ACCESS_EDIT)) {
                    continue;
                }*/

                $bundleInfo = $bundle->getMetaData();
                [$menuTextUrl, $menuText] = $this->adminBundleHelper->getAdminRouteInformation($bundleInfo);

                $bundleName = (string) $bundle->getName();
                $extensionMenuItems = $this->extensionMenuCollector->get($bundleName, ExtensionMenuInterface::CONTEXT_ADMIN);
                $isSubMenu = isset($extensionMenuItems);

                if ($isSubMenu) {
                    yield MenuItem::subMenu($menuText, $bundleInfo->getIcon())->setSubItems(iterator_to_array($extensionMenuItems));
                } else {
                    yield MenuItem::linktoRoute($menuText, $bundleInfo->getIcon(), $menuTextUrl);
                }
            }
        }

        yield MenuItem::section(t('Resources'), 'fas fa-book');
        yield MenuItem::subMenu(t('Zikula'), 'fas fa-rocket')->setSubItems([
            MenuItem::linkToUrl(t('Website'), 'fas fa-house', 'https://ziku.la/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Docs'), 'fas fa-file-contract', 'https://docs.ziku.la/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Support Slack'), 'fab fa-slack', 'https://joinslack.ziku.la/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('ModuleStudio'), 'fas fa-wand-sparkles', 'https://modulestudio.de/en/documentation/')->setLinkTarget('_blank'),
        ]);
        yield MenuItem::subMenu(t('Foundation'), 'fas fa-cubes-stacked')->setSubItems([
            MenuItem::linkToUrl(t('Symfony'), 'fab fa-symfony', 'https://symfony.com/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Twig'), 'fas fa-file-lines', 'https://twig.symfony.com/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Doctrine'), 'fas fa-database', 'https://www.doctrine-project.org/')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('EasyAdmin'), 'fas fa-screwdriver-wrench', 'https://symfony.com/bundles/EasyAdminBundle/current/index.html')->setLinkTarget('_blank'),
            MenuItem::linkToUrl(t('Bootstrap'), 'fab fa-bootstrap', 'https://getbootstrap.com/')->setLinkTarget('_blank'),
        ]);
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        if (!extension_loaded('intl')) {
            $this->addFlash('error', t('WARNING: The PHP extension intl is not loaded. All functions using this will default to "en". Seek assistance from your provider to install.'));
        }

        return parent::index();
    }
}
