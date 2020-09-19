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

namespace Zikula\MenuModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsModule\Annotation\PermissionCheck;

/**
 * Class ConfigController
 *
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    public function config(Request $request): void
    {
        // do nothing
    }
}
