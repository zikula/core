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

namespace Zikula\PermissionsModule\Api\ApiInterface;

interface PermissionApiInterface
{
    /**
     * Check permissions
     * @api Core-2.0
     */
    public function hasPermission(string $component = null, string $instance = null, int $level = ACCESS_NONE, int $user = null): bool;

    /**
     * Translation functions
     * Translate level -> name
     * @api Core-2.0
     * @return string|array
     */
    public function accessLevelNames(int $level = null);

    /**
     * Set permissions for user to false, forcing a reload if called upon again.
     * @api Core-2.0
     */
    public function resetPermissionsForUser(int $userId): void;
}
