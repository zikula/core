<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\RoutesModule\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Core\Controller\AbstractController;
use Zikula\RoutesModule\Helper\MultilingualRoutingHelper;
use Zikula\RoutesModule\Helper\PermissionHelper;
use Zikula\RoutesModule\Helper\RouteDumperHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Update controller for renewing route information on demand.
 */
class UpdateController extends AbstractController
{
    /**
     * Reloads the routes and dumps exposed JS routes.
     *
     * @Route("/update/reload",
     *        methods = {"GET", "POST"}
     * )
     * @Theme("admin")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function reloadAction(
        PermissionHelper $permissionHelper,
        CacheClearer $cacheClearer,
        RouteDumperHelper $routeDumperHelper
    ): Response {
        if (!$permissionHelper->hasPermission(ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $cacheClearer->clear('symfony.routing');

        $this->addFlash('status', 'Done! Routes reloaded.');

        // reload **all** JS routes
        $this->dumpJsRoutes($routeDumperHelper);

        return $this->redirectToRoute('zikularoutesmodule_route_adminview');
    }

    /**
     * Renews multilingual routing settings.
     *
     * @Route("/update/renew",
     *        methods = {"GET", "POST"}
     * )
     * @Theme("admin")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function renewAction(
        PermissionHelper $permissionHelper,
        MultilingualRoutingHelper $multilingualRoutingHelper
    ): Response {
        if (!$permissionHelper->hasPermission(ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Renew the routing settings.
        $multilingualRoutingHelper->reloadMultilingualRoutingSettings();

        $this->addFlash('status', 'Done! Routing settings renewed.');

        return $this->redirectToRoute('zikularoutesmodule_route_adminview');
    }

    /**
     * Dumps the routes exposed to javascript.
     *
     * @Route("/update/dump/{lang}",
     *        name = "zikularoutesmodule_update_dumpjsroutes",
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function dumpJsRoutesAction(
        PermissionHelper $permissionHelper,
        RouteDumperHelper $routeDumperHelper,
        string $lang = null
    ): Response {
        if (!$permissionHelper->hasPermission(ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->dumpJsRoutes($routeDumperHelper, $lang);

        return $this->redirectToRoute('zikularoutesmodule_route_adminview');
    }

    /**
     * Dumps exposed JS routes to '/web/js/fos_js_routes.js'.
     */
    private function dumpJsRoutes(RouteDumperHelper $routeDumperHelper, string $lang = null): void
    {
        $result = $routeDumperHelper->dumpJsRoutes($lang);

        if ('' === $result) {
            $this->addFlash('status', $this->trans('Done! Exposed JS Routes dumped to %path%.', ['%path%' => 'web/js/fos_js_routes.js']));
        } else {
            $this->addFlash('error', $this->trans('Error! There was an error dumping exposed JS Routes: %result%', ['%result%' => $result]));
        }
    }
}
