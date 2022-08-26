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
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\GroupsBundle\Controller\GroupEntityCrudController;
use Zikula\GroupsBundle\Entity\GroupEntity;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;

class UserDashboardController extends AbstractDashboardController
{
    public function __construct(private readonly ExtensionMenuCollector $extensionMenuCollector, private readonly SiteDefinitionInterface $site)
    {
    }

    #[Route('/', name: 'home')]
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
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->site->getName());
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('TEST', 'fas fa-flask');
        yield MenuItem::linkToDashboard('Dashboard', 'fas fa-home');
        //yield MenuItem::linktoRoute('Administration', 'fas fa-wrench', 'home_admin');
        yield MenuItem::linkToUrl('Administration', 'fas fa-wrench', '/admin');
        yield MenuItem::linkToUrl('Symfony', 'fab fa-symfony', 'https://symfony.com');
        yield MenuItem::linkToUrl('Zikula', 'fas fa-rocket', 'https://ziku.la');
        yield MenuItem::linkToUrl('Zikula Docs', 'fas fa-book', 'https://docs.ziku.la/');
        yield MenuItem::linkToUrl('ModuleStudio', 'fas fa-wand-sparkles', 'https://modulestudio.de/en/');
        yield MenuItem::linkToCrud('Groups', 'fas fa-people-group', GroupEntity::class);
    }
}
