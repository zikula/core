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

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\Helper\AdminBundleHelper;
use Zikula\ThemeBundle\Helper\AdminCategoryHelper;
use Zikula\ThemeBundle\Helper\UserMenuExtensionHelper;

abstract class AbstractThemedDashboardController extends AbstractDashboardController
{
    public function __construct(
        protected readonly KernelInterface $kernel,
        protected readonly AdminUrlGenerator $urlGenerator,
        protected readonly AdminCategoryHelper $adminCategoryHelper,
        protected readonly AdminBundleHelper $adminBundleHelper,
        protected readonly ExtensionMenuCollector $extensionMenuCollector,
        protected readonly UserMenuExtensionHelper $userMenuExtensionHelper,
        protected readonly SiteDefinitionInterface $site,
        protected readonly array $themeConfig
    ) {
    }

    abstract protected function getName(): string;

    protected function getDashboardWithBranding(bool $showLogo, ?TranslatableMessage $title = null): Dashboard
    {
        $siteName = $this->site->getName();
        if ($showLogo) {
            $logoPath = $this->site->getMobileLogoPath() ?? $this->site->getLogoPath();
            $logo = $logoPath ? '<img src="' . $logoPath . '" alt="' . str_replace('"', '', $siteName) . '" /><br />' : '';
        } else {
            $logo = '';
        }

        $titlePrefix = $logo ?: $siteName;
        $titleSuffix = $title ? ' ' . $title : '';

        $dashboard = parent::configureDashboard()
            ->setTitle($titlePrefix . $titleSuffix);

        $iconPath = $this->site->getIconPath();
        if (null !== $iconPath) {
            $dashboard->setFaviconPath($iconPath);
        }

        $dashboard->renderContentMaximized($this->themeConfig['view']['content_maximized'])
            ->renderSidebarMinimized($this->themeConfig['view']['sidebar_minimized'])
            ->setTranslationDomain('dashboard');

        return $dashboard;
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addCssFile('bundles/zikulatheme/dashboard/' . $this->getName() . '.css')
            ->addJsFile('bundles/zikulatheme/dashboard/' . $this->getName() . '.js')
        ;
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->overrideTemplate('layout', '@ZikulaTheme/Dashboard/layout_' . $this->getName() . '.html.twig')
        ;
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return $this->userMenuExtensionHelper->configureUserMenu(parent::configureUserMenu($user), $user);
    }

    public function index(): Response
    {
        $contentConfig = $this->themeConfig['content'];
        if (null !== $contentConfig['template']) {
            // render a custom template
            return $this->render($contentConfig['template']);
        }

        if (null !== $contentConfig['redirect']['crud']) {
            // redirect to a CRUD controller page
            return $this->redirect($this->urlGenerator->setController($contentConfig['redirect']['crud'])->generateUrl());
        }

        if (null !== $contentConfig['redirect']['route']) {
            // redirect to a Symfony route
            return $this->redirect($this->urlGenerator->setRoute($contentConfig['redirect']['route'], $contentConfig['redirect']['route_parameters'])->generateUrl());
        }

        // render EAB welcome page
        return parent::index();
    }
}
