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

namespace Zikula\SettingsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\SettingsBundle\Helper\RouteDumperHelper;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

/**
 * @PermissionCheck("admin")
 */
#[Route('/settings')]
class RouteController extends AbstractController
{
    /**
     * Dumps the routes exposed to javascript.
     *
     * @Theme("admin")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    #[Route('/dump-routes', name: 'zikulasettingsbundle_route_dumpjsroutes', methods: ['GET'])]
    public function dumpJsRoutes(RouteDumperHelper $routeDumperHelper): Response
    {
        if (!$permissionHelper->hasPermission(ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $result = $routeDumperHelper->dumpJsRoutes();

        if ('' === $result) {
            $this->addFlash('status', $this->trans('Done! Exposed JS Routes dumped to %path%.', ['%path%' => 'public/js/fos_js_routes.js']));
        } else {
            $this->addFlash('error', $this->trans('Error! There was an error dumping exposed JS routes: %result%', ['%result%' => $result]));
        }

        return $this->redirectToRoute('zikulasettingsbundle_settings_mainsettings');
    }
}
