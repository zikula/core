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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\Helper\AdminBundleHelper;
use Zikula\ThemeBundle\Helper\AdminCategoryHelper;

abstract class AbstractThemedDashboardController extends AbstractDashboardController
{
    public function __construct(
        protected readonly AdminUrlGenerator $adminUrlGenerator,
        protected readonly AdminCategoryHelper $adminCategoryHelper,
        protected readonly AdminBundleHelper $adminBundleHelper,
        protected readonly ExtensionMenuCollector $extensionMenuCollector,
        protected readonly PermissionApiInterface $permissionApi,
        protected readonly SiteDefinitionInterface $site
    ) {
    }

    abstract protected function getName(): string;

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
