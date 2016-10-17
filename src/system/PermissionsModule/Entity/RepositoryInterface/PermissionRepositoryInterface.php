<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Entity\RepositoryInterface;

use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\PermissionsModule\Entity\PermissionEntity;

interface PermissionRepositoryInterface
{
    public function getPermissionsByGroups(array $groups);

    public function getFilteredPermissions($group = PermissionApi::ALL_GROUPS, $component = null);

    public function getAllComponents();

    public function persistAndFlush(PermissionEntity $entity);
}
