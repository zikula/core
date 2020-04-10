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

class FooController extends AbstractController
{
    /**
     * @PermissionCheck("admin")
     */
    public function firstAction()
    {
        return true;
    }

    /**
     * @PermissionCheck({"AcmeFooModule::", ".*", "overview"})
     */
    public function secondAction()
    {
        return true;
    }

    /**
     * @PermissionCheck({"$_zkModule", "::$gid", "ACCESS_EDIT"})
     */
    public function thirdAction()
    {
        return true;
    }

    /**
     * @PermissionCheck("admin")
     * @PermissionCheck("edit")
     * @PermissionCheck("delete")
     * @PermissionCheck("moderate")
     * @PermissionCheck("comment")
     * @PermissionCheck("overview")
     */
    public function fourthAction()
    {
        return true;
    }
}
