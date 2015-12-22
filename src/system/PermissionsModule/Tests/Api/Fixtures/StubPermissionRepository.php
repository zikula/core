<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\PermissionsModule\Tests\Api\Fixtures;

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
                'gid' => 2, // 'Administrators' group
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
                'gid' => 1, // 'Users' group
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
}
