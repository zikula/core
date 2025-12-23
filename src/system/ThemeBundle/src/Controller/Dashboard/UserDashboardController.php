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

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\HttpFoundation\Response;
use Zikula\CoreBundle\Bundle\MetaData\MetaDataAwareBundleInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
use function Symfony\Component\Translation\t;

#[AdminDashboard(routePath: '/', routeName: 'user')]
class UserDashboardController extends AbstractThemedDashboardController
{
    protected function getName(): string
    {
        return 'user';
    }

    public function configureDashboard(): Dashboard
    {
        return $this->getDashboardWithBranding(true);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(t('Home'), 'fas fa-home');
        yield MenuItem::linkToRoute(t('Administration'), 'fas fa-wrench', 'admin_home')->setPermission('ROLE_ADMIN');

        yield MenuItem::section();
        $menuItemsByBundle = $this->extensionMenuCollector->getAllByContext(ExtensionMenuInterface::CONTEXT_USER);
        foreach ($menuItemsByBundle as $bundleName => $extensionMenuItems) {
            $bundle = $this->kernel->getBundle($bundleName);
            if (!($bundle instanceof MetaDataAwareBundleInterface)) {
                continue;
            }

            $menuItems = is_array($extensionMenuItems) ? $extensionMenuItems : iterator_to_array($extensionMenuItems);
            if (!count($menuItems)) {
                continue;
            }
            $bundleInfo = $bundle->getMetaData();
            yield MenuItem::subMenu($bundleInfo->getDisplayName(), $bundleInfo->getIcon())->setSubItems($menuItems);
            /*foreach ($menuItems as $item) {
                yield $item;
            }*/
        }
    }

    #[AdminRoute('/', name: 'dashboard')]
    public function index(): Response
    {
        return parent::index();
    }
}
