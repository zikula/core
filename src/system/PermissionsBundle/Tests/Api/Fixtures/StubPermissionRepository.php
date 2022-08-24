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

namespace Zikula\PermissionsBundle\Tests\Api\Fixtures;

use Zikula\GroupsBundle\GroupsConstant;
use Zikula\PermissionsBundle\Api\PermissionApi;
use Zikula\PermissionsBundle\Entity\PermissionEntity;
use Zikula\PermissionsBundle\Repository\PermissionRepositoryInterface;

class StubPermissionRepository implements PermissionRepositoryInterface
{
    /** @var PermissionEntity[] */
    private array $entities;

    public function __construct()
    {
        $datas = [
            [
                'gid' => GroupsConstant::GROUP_ID_ADMIN, // 2
                'sequence' => 1,
                'component' => '.*',
                'instance' => '.*',
                'level' => ACCESS_ADMIN
            ],
            [
                'gid' => PermissionApi::ALL_GROUPS, // -1
                'sequence' => 2,
                'component' => 'ExtendedMenublock::',
                'instance' => '1:1:',
                'level' => ACCESS_NONE
            ],
            [
                'gid' => GroupsConstant::GROUP_ID_USERS, // 1
                'sequence' => 3,
                'component' => '.*',
                'instance' => '.*',
                'level' => ACCESS_COMMENT
            ],
            [
                'gid' => PermissionApi::UNREGISTERED_USER_GROUP, // 0
                'sequence' => 4,
                'component' => 'ExtendedMenublock::',
                'instance' => '1:(1|2|3):',
                'level' => ACCESS_NONE
            ],
            [
                'gid' => PermissionApi::UNREGISTERED_USER_GROUP, // 0
                'sequence' => 5,
                'component' => '.*',
                'instance' => '.*',
                'level' => ACCESS_READ
            ]
        ];
        foreach ($datas as $data) {
            $this->entities[] = (new PermissionEntity())
                ->setGid($data['gid'])
                ->setSequence($data['sequence'])
                ->setComponent($data['component'])
                ->setInstance($data['instance'])
                ->setLevel($data['level']);
        }
    }

    public function getPermissionsByGroups(array $groups): array
    {
        $result = [];
        foreach ($this->entities as $entity) {
            if (in_array($entity->getGid(), $groups, true)) {
                $result[] = $entity;
            }
        }

        return $result;
    }

    public function getFilteredPermissions(int $group = PermissionApi::ALL_GROUPS, string $component = null): array
    {
        return [];
    }

    public function getAllComponents(): array
    {
        return [];
    }

    public function getAllColours(): array
    {
        return [];
    }

    public function persistAndFlush(PermissionEntity $entity): void
    {
        // nothing
    }

    public function getMaxSequence(): int
    {
        return 999;
    }

    public function updateSequencesFrom(int $value, int $amount = 1): void
    {
        // nothing
    }

    public function reSequence(): void
    {
        // nothing
    }

    public function deleteGroupPermissions(int $groupId = 0): void
    {
        // nothing
    }
}
