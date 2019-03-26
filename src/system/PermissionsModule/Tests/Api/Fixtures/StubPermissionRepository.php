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

namespace Zikula\PermissionsModule\Tests\Api\Fixtures;

use Zikula\GroupsModule\Constant;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\PermissionsModule\Entity\PermissionEntity;
use Zikula\PermissionsModule\Entity\RepositoryInterface\PermissionRepositoryInterface;

class StubPermissionRepository implements PermissionRepositoryInterface
{
    private $entities;

    /**
     * StubRepository constructor.
     */
    public function __construct()
    {
        $datas = [
            [
                'gid' => Constant::GROUP_ID_ADMIN, // 2
                'sequence' => 1,
                'realm' => 0,
                'component' => '.*',
                'instance' => '.*',
                'level' => ACCESS_ADMIN,
                'bond' => 0,
            ],
            [
                'gid' => PermissionApi::ALL_GROUPS, // -1
                'sequence' => 2,
                'realm' => 0,
                'component' => 'ExtendedMenublock::',
                'instance' => '1:1:',
                'level' => ACCESS_NONE,
                'bond' => 0,
            ],
            [
                'gid' => Constant::GROUP_ID_USERS, // 1
                'sequence' => 3,
                'realm' => 0,
                'component' => '.*',
                'instance' => '.*',
                'level' => ACCESS_COMMENT,
                'bond' => 0,
            ],
            [
                'gid' => PermissionApi::UNREGISTERED_USER_GROUP, // 0
                'sequence' => 4,
                'realm' => 0,
                'component' => 'ExtendedMenublock::',
                'instance' => '1:(1|2|3):',
                'level' => ACCESS_NONE,
                'bond' => 0,
            ],
            [
                'gid' => PermissionApi::UNREGISTERED_USER_GROUP, // 0
                'sequence' => 5,
                'realm' => 0,
                'component' => '.*',
                'instance' => '.*',
                'level' => ACCESS_READ,
                'bond' => 0,
            ],
        ];
        foreach ($datas as $data) {
            $entity = new PermissionEntity();
            $entity->merge($data);
            $this->entities[] = $entity;
        }
    }

    public function getPermissionsByGroups(array $groups)
    {
        $result = [];
        foreach ($this->entities as $entity) {
            if (in_array($entity['gid'], $groups)) {
                $result[] = $entity;
            }
        }

        return $result;
    }

    public function getFilteredPermissions($group = PermissionApi::ALL_GROUPS, $component = null)
    {
        // TODO: Implement getFilteredPermissions() method.
    }

    public function getAllComponents()
    {
        // TODO: Implement getAllComponents() method.
    }

    public function persistAndFlush(PermissionEntity $entity)
    {
        // TODO: Implement persistAndFlush() method.
    }

    public function getMaxSequence()
    {
        // TODO: Implement getMaxSequence() method.
    }

    public function updateSequencesFrom($value, $amount = 1)
    {
        // TODO: Implement updateSequencesFrom() method.
    }

    public function reSequence()
    {
        // TODO: Implement reSequence() method.
    }
}
