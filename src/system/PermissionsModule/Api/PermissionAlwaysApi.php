<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Api;

use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * Class PermissionAlwaysApi
 *
 * This class exists for testing and to ensure a functional Api is provided in a default situation.
 * ALL hasPermission tests return TRUE regardless of settings.
 * Access level names are returned untranslated.
 * Permissions cannot be reset for a user.
 */
class PermissionAlwaysApi implements PermissionApiInterface
{
    /**
* @inheritDoc
     */
    public function hasPermission($component = null, $instance = null, $level = ACCESS_NONE, $user = null)
    {
        return true;
    }

    /**
* @inheritDoc
     */
    public function accessLevelNames($level = null)
    {
        if (isset($level) && !is_numeric($level)) {
            throw new \InvalidArgumentException();
        } elseif (isset($level)) {
            $level = intval($level);
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
* @inheritDoc
     */
    public function resetPermissionsForUser($uid)
    {
        // nothing to do
    }
}
