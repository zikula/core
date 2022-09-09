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

namespace Zikula\ThemeBundle\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\AbstractModule;
use Zikula\Bundle\CoreBundle\Composer\MetaData;

class AdminBundleHelper
{
    private const CAPABILITY_NAME = 'admin';

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getAdminCapableBundles(): array
    {
        $result = [];
        $bundles = $this->kernel->getBundles();
        foreach ($bundles as $bundle) {
            if (!($bundle instanceof AbstractModule)) {
                continue;
            }
            if (isset($bundle->getMetaData()->getCapabilities()[self::CAPABILITY_NAME])) {
                $result[] = $bundle;
            }
        }

        return $result;
    }

    public function getAdminRouteInformation(MetaData $bundleInfo): array
    {
        $menuText = $bundleInfo->getDisplayName(); // . ' (' . $adminBundle->getName() . ')';

        try {
            $menuTextUrl = isset($bundleInfo->getCapabilities()[self::CAPABILITY_NAME]['route'])
                ? $this->router->generate($bundleInfo->getCapabilities()[self::CAPABILITY_NAME]['route'])
                : '';
        } catch (RouteNotFoundException $routeNotFoundException) {
            $menuTextUrl = 'javascript:void(0)';
            // $menuText .= ' (⚠️ ' . $this->translator->trans('invalid route') . ')';
            $menuText .= ' (<i class="fas fa-exclamation-triangle"></i> ' . $this->translator->trans('invalid route') . ')';
        }

        return [$menuTextUrl, $menuText];
    }
}