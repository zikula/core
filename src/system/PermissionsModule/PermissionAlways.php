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
 * Class PermissionAlwaysApi
 *
 * This class exists for testing and to ensure a functional Api is provided in a default situation.
 * ALL hasPermission tests return TRUE regardless of settings.
 * Access level names are returned untranslated.
 * Permissions cannot be reset for a user.
 */
class PermissionAlways implements PermissionApiInterface
{
    /**
     * {@inheritdoc}
     */
    public function hasPermission($component = null, $instance = null, $level = ACCESS_NONE, $user = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function accessLevelNames($level = null)
    {
        if (isset($level) && !is_numeric($level)) {
            throw new \InvalidArgumentException();
        } elseif (isset($level)) {
            $level = (int) $level;
        }

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

    /**
     * {@inheritdoc}
     */
    public function resetPermissionsForUser($uid)
    {
        // nothing to do
    }
}
