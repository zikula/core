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
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
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
        yield MenuItem::linkToUrl(t('Administration'), 'fas fa-wrench', '/admin')->setPermission('ROLE_ADMIN');

        yield MenuItem::section();
        $menuItemsByBundle = $this->extensionMenuCollector->getAllByContext(ExtensionMenuInterface::CONTEXT_USER);
        foreach ($menuItemsByBundle as $bundleName => $extensionMenuItems) {
            $bundle = $this->kernel->getBundle($bundleName);

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

    #[Route('/', name: 'user_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('user_dashboard', ['_locale' => $this->defaultLocale]);
    }

    #[Route('/{_locale}', name: 'user_dashboard')]
    public function index(): Response
    {
        return parent::index();
    }
}
