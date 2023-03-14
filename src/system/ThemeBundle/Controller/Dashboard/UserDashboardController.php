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
use Zikula\CategoriesBundle\Entity\Category;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\UsersBundle\Entity\User;
use function Symfony\Component\Translation\t;

class UserDashboardController extends AbstractThemedDashboardController
{
    protected function getName(): string
    {
        return 'user';
    }

    public function configureDashboard(): Dashboard
    {
        return parent::getDashboardWithBranding(true);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(t('Home'), 'fas fa-home');
        // yield MenuItem::linktoRoute(t('Administration'), 'fas fa-wrench', 'home_admin');
        yield MenuItem::linkToUrl(t('Administration'), 'fas fa-wrench', '/admin');
        yield MenuItem::linkToCrud(t('Users'), 'fas fa-people-group', User::class);
        yield MenuItem::linkToCrud(t('Categories'), 'fas fa-sitemap', Category::class);

        yield MenuItem::section();
        $menuItemsByBundle = $this->extensionMenuCollector->getAllByContext(ExtensionMenuInterface::CONTEXT_USER);
        foreach ($menuItemsByBundle as $bundleName => $extensionMenuItems) {
            $bundle = $this->kernel->getBundle($bundleName);
            $bundleInfo = $bundle->getMetaData();

            $menuItems = is_array($extensionMenuItems) ? $extensionMenuItems : iterator_to_array($extensionMenuItems);
            if (!count($menuItems)) {
                continue;
            }
            // yield MenuItem::subMenu($bundleInfo->getDisplayName(), $bundleInfo->getIcon())->setSubItems($menuItems);
            foreach ($menuItems as $item) {
                yield $item;
            }
        }
    }

    #[Route('/', name: 'user_dashboard')]
    public function index(): Response
    {
        return parent::index();
    }
}
