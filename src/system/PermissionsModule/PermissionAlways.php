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

namespace Zikula\PermissionsModule;

use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * This class exists for testing and to ensure a functional Api is provided in a default situation.
 * ALL hasPermission tests return TRUE regardless of settings.
 * Access level names are returned untranslated.
 * Permissions cannot be reset for a user.
 */
class PermissionAlways implements PermissionApiInterface
{
    public function hasPermission(string $component = null, string $instance = null, int $level = ACCESS_NONE, int $user = null): bool
    {
        return true;
    }

    public function accessLevelNames(int $level = null)
    {
        $accessNames = [
            ACCESS_INVALID => 'Invalid',
            ACCESS_NONE => 'No access',
            ACCESS_OVERVIEW => 'Overview access',
            ACCESS_READ => 'Read access',
            ACCESS_COMMENT => 'Comment access',
            ACCESS_MODERATE => 'Moderate access',
            ACCESS_EDIT => 'Edit access',
            ACCESS_ADD => 'Add access',
            ACCESS_DELETE => 'Delete access',
            ACCESS_ADMIN => 'Admin access',
        ];

        return isset($level) ? $accessNames[$level] : $accessNames;
    }

    public function resetPermissionsForUser(int $uid): void
    {
        // nothing to do
    }
}
