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

use Symfony\Component\HttpFoundation\Request;
use Zikula\ThemeBundle\Controller\Dashboard\AdminDashboardController;
use Zikula\ThemeBundle\Controller\Dashboard\UserDashboardController;

/**
 * Helper for dynamic default dashboard selection, used by CreateThemedResponseSubscriber and non-EAB controllers.
 */
class FallbackDashboardDetector
{
    private const ROUTE_PART_ADMIN = '_admin_';

    public function isAdminArea(Request $request): bool
    {
        $route = $this->getRoute($request);

        return str_contains($route, self::ROUTE_PART_ADMIN);
    }

    public function getDashboardControllerFqcn(Request $request): string
    {
        $isAdminArea = $this->isAdminArea($request);
        $request->attributes->set('isAdminArea', $isAdminArea);

        return $isAdminArea ? AdminDashboardController::class : UserDashboardController::class;
    }

    private function getRoute(Request $request): string
    {
        return $request->attributes->get('_route') ?? '';
    }
}
