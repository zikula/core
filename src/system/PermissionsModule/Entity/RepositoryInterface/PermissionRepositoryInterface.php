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

namespace Zikula\PermissionsModule\Entity\RepositoryInterface;

use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\PermissionsModule\Entity\PermissionEntity;

interface PermissionRepositoryInterface
{
    public function getPermissionsByGroups(array $groups): array;

    /**
     * Optionally filter a selection of permissions by group or component or both.
     */
    public function getFilteredPermissions(int $group = PermissionApi::ALL_GROUPS, string $component = null): array;

    public function getAllComponents(): array;

    public function persistAndFlush(PermissionEntity $entity): void;

    /**
     * Get the highest sequential number.
     */
    public function getMaxSequence(): int;

    /**
     * Update all sequence values >= the provided $value by the provided $amount
     *   to increment, amount = 1; to decrement, amount = -1
     */
    public function updateSequencesFrom(int $value, int $amount = 1): void;

    /**
     * ReSequence all permissions.
     */
    public function reSequence(): void;

    /**
     * Deletes all permissions for a given group.
     */
    public function deleteGroupPermissions(int $groupId = 0): void;
}
