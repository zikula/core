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

namespace Zikula\PermissionsModule\Tests\Api\Fixtures;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Zikula\PermissionsModule\Annotation\PermissionCheck;

/**
 * @PermissionCheck("admin")
 */
class BarController extends AbstractController
{
    public function firstAction()
    {
        return true;
    }
}
