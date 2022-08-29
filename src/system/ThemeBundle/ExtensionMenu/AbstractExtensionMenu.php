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

namespace Zikula\ThemeBundle\ExtensionMenu;

use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;

abstract class AbstractExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(protected readonly PermissionApiInterface $permissionApi)
    {
    }

    public function get(MenuContext $context = MenuContext::ADMIN): iterable
    {
        if (MenuContext::ADMIN === $context) {
            return $this->getAdmin();
        }
        if (MenuContext::USER === $context) {
            return $this->getUser();
        }
        if (MenuContext::ACCOUNT === $context) {
            return $this->getAccount();
        }

        return [];
    }

    protected function getAdmin(): iterable
    {
        return [];
    }

    protected function getUser(): iterable
    {
        return [];
    }

    protected function getAccount(): iterable
    {
        return [];
    }
}
