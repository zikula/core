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

// use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Zikula\CoreBundle\Bundle\MetaData\MetaDataAwareBundleInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\ThemeBundle\Helper\ResourceMenuProvider;
use function Symfony\Component\Translation\t;

#[IsGranted('ROLE_ADMIN')]
// TODO blocked by https://github.com/EasyCorp/EasyAdminBundle/issues/6792
// #[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractThemedDashboardController
{
    protected function getName(): string
    {
        return 'admin';
    }

    public function configureDashboard(): Dashboard
    {
        return $this->getDashboardWithBranding(true, t('Administration'));
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(t('Dashboard'), 'fa fa-gauge-high');
        yield MenuItem::linktoRoute(t('Website frontend'), 'fas fa-home', 'user_home');

        $menuItemsByBundle = $this->extensionMenuCollector->getAllByContext(ExtensionMenuInterface::CONTEXT_ADMIN);
        $defaultCategory = $this->adminCategoryHelper->getDefaultCategory();
        $bundleCategoryAssignments = $this->adminCategoryHelper->getBundleAssignments();

        foreach ($this->adminCategoryHelper->getCategories() as $category) {
            yield MenuItem::section($category->getName(), $category->getIcon());
            foreach ($menuItemsByBundle as $bundleName => $extensionMenuItems) {
                $bundle = $this->kernel->getBundle($bundleName);
                if (!($bundle instanceof MetaDataAwareBundleInterface)) {
                    continue;
                }
                $bundleCategory = $bundleCategoryAssignments[$bundleName] ?? $defaultCategory->getSlug();
                if ($bundleCategory !== $category->getSlug()) {
                    continue;
                }
                $bundleInfo = $bundle->getMetaData();
                yield MenuItem::subMenu($bundleInfo->getDisplayName(), $bundleInfo->getIcon())->setSubItems(iterator_to_array($extensionMenuItems));
            }
        }

        $resources = ResourceMenuProvider::getResources();
        foreach ($resources as $resourceItem) {
            yield $resourceItem;
        }
    }

    #[Route('/admin', name: 'admin_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('admin_dashboard', ['_locale' => $this->defaultLocale]);
    }

    #[Route('/admin/{_locale}', name: 'admin_dashboard')]
    public function index(): Response
    {
        if (!extension_loaded('intl')) {
            $this->addFlash('error', t('WARNING: The PHP extension intl is not loaded. All functions using this will default to "en". Seek assistance from your provider to install.'));
        }

        return parent::index();
    }
}
