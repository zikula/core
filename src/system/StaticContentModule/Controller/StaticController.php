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

namespace Zikula\StaticContentModule\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

final class StaticController
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        PermissionApiInterface $permissionApi,
        Environment $twig
    ) {
        $this->permissionApi = $permissionApi;
        $this->twig = $twig;
    }

    /**
     * @Route("/p/{name}", methods = {"GET"}, options={"zkNoBundlePrefix"=1}, requirements={"name" = "[^&/?*:;{}\\]+"})
     */
    public function view(string $name): Response
    {
        if (!$this->permissionApi->hasPermission('ZikulaStaticContentModule::name', '::' . $name, ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        if (!$this->twig->getLoader()->exists('p/' . $name . '.html.twig')) {
            throw new NotFoundHttpException(sprintf('No route found for "%s"', 'GET /p/' . $name));
        }

        return new Response($this->twig->render('p/' . $name . '.html.twig'));
    }
}
