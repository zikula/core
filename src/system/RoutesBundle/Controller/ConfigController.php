<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\RoutesBundle\Helper\RouteDumperHelper;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

/**
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     */
    public function config(): Response
    {
        return new Response('<h3>Settings</h3><p>Nothing to do here.</p>');
    }

   /**
     * Dumps the routes exposed to javascript.
     *
     * @Route("/update/dump",
     *        name = "zikularoutesbundle_config_dumpjsroutes",
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function dumpJsRoutes(RouteDumperHelper $routeDumperHelper): Response
    {
        if (!$permissionHelper->hasPermission(ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $result = $routeDumperHelper->dumpJsRoutes();

        if ('' === $result) {
            $this->addFlash('status', $this->trans('Done! Exposed JS Routes dumped to %path%.', ['%path%' => 'public/js/fos_js_routes.js']));
        } else {
            $this->addFlash('error', $this->trans('Error! There was an error dumping exposed JS Routes: %result%', ['%result%' => $result]));
        }

        return $this->redirectToRoute('zikularoutesbundle_config_config');
    }
}
