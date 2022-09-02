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
use Zikula\GroupsBundle\Controller\GroupEntityCrudController;
use Zikula\GroupsBundle\Entity\Group;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;

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
        yield MenuItem::linkToCrud(t('Groups'), 'fas fa-people-group', Group::class);

        yield MenuItem::section();
        $menuItemsByBundle = $this->extensionMenuCollector->getAllByContext(ExtensionMenuInterface::CONTEXT_USER);
        foreach ($menuItemsByBundle as $bundleName => $extensionMenuItems) {
            $bundle = $this->kernel->getBundle($bundleName);
            /*if (!$this->permissionApi->hasPermission($bundle->getName() . '::', 'ANY', ACCESS_OVERVIEW)) {
                continue;
            }*/

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
        // redirect to a common dashboard page
        return $this->redirect($this->adminUrlGenerator->setController(GroupEntityCrudController::class)->generateUrl());

        // display a dashboard with widgets, etc. (template should extend @EasyAdmin/page/content.html.twig)
        // return $this->render('some/path/my-dashboard.html.twig');
    }
}
