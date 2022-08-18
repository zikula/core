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

namespace Zikula\PermissionsBundle\Twig\Runtime;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;

class PermissionsRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly PermissionApiInterface $permissionApi)
    {
    }

    public function hasPermission(string $component, string $instance, string $level): bool
    {
        if (empty($component) || empty($instance) || empty($level)) {
            throw new InvalidArgumentException($this->translator->trans('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->permissionApi->hasPermission($component, $instance, constant($level));
    }
}
