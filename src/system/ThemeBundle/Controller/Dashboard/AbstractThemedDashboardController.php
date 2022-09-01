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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\Helper\AdminBundleHelper;
use Zikula\ThemeBundle\Helper\AdminCategoryHelper;

abstract class AbstractThemedDashboardController extends AbstractDashboardController
{
    public function __construct(
        protected readonly KernelInterface $kernel,
        protected readonly AdminUrlGenerator $adminUrlGenerator,
        protected readonly AdminCategoryHelper $adminCategoryHelper,
        protected readonly AdminBundleHelper $adminBundleHelper,
        protected readonly ExtensionMenuCollector $extensionMenuCollector,
        protected readonly PermissionApiInterface $permissionApi,
        protected readonly SiteDefinitionInterface $site
    ) {
    }

    abstract protected function getName(): string;

    protected function getDashboardWithBranding(bool $showLogo, ?string $title = null): Dashboard
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
}
