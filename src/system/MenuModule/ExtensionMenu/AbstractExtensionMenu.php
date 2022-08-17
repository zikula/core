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

namespace Zikula\MenuModule\ExtensionMenu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

abstract class AbstractExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        protected readonly FactoryInterface $factory,
        protected readonly PermissionApiInterface $permissionApi
    ) {
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }

        return null;
    }

    abstract protected function getAdmin(): ?ItemInterface;
}
