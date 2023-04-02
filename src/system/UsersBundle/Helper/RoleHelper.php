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

namespace Zikula\UsersBundle\Helper;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RoleHelper
{
    private array $roleHierarchy;

    public function __construct(
        #[Autowire('%security.role_hierarchy.roles%')]
        array $roleHierarchy
    ) {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function getRoleOptions(): array
    {
        $systemRoles = [
            'User' => 'ROLE_USER',
            'Editor' => 'ROLE_EDITOR',
            'Administrator' => 'ROLE_ADMIN',
            'Super administrator' => 'ROLE_SUPER_ADMIN',
        ];

        $definedRoles = [];
        array_walk_recursive($this->roleHierarchy, function($role) use (&$roles) {
            $definedRoles[$role] = $role;
        });

        $roles = $systemRoles;
        foreach ($definedRoles as $role) {
            if (!in_array($role, $roles, true)) {
                $roles[$role] = $role;
            }
        }

        return $roles;
    }
}
