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

namespace Zikula\GroupsModule;

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;

class GroupsModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        GroupEntity::class,
        GroupApplicationEntity::class
    ];

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);

        $this->setVar('itemsperpage', 25);
        $this->setVar('defaultgroup', 1);
        $this->setVar('mailwarning', false);
        $this->setVar('hideclosed', false);
        $this->setVar('hidePrivate', false);

        // create the default data
        $this->createDefaultData();

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '2.4.0': // shipped with Core-1.4.3
                $this->setVar('mailwarning', (bool)$this->getVar('mailwarning'));
                $this->setVar('hideclosed', (bool)$this->getVar('hideclosed'));
                $this->setVar('hidePrivate', false);
                // no break
            case '2.4.1':
                /** @var UserEntity $anonymousUser */
                $anonymousUser = $this->managerRegistry->getRepository(UserEntity::class)->find(UsersConstant::USER_ID_ANONYMOUS);
                $usersGroup = $this->managerRegistry->getRepository(GroupEntity::class)->find(GroupsConstant::GROUP_ID_USERS);
                $anonymousUser->getGroups()->removeElement($usersGroup);
                $this->entityManager->flush();
                $this->addFlash('info', 'NOTICE: The old type of "anonymous" user has been removed from the Users group. This may require manual adjustment of your permission schema.');
                // no break
            case '2.4.2': // shipped with Core-2.0.15
                // do nothing
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Create the default data for the groups module.
     */
    public function createDefaultData(): void
    {
        $records = [
            [
                'gid' => GroupsConstant::GROUP_ID_USERS,
                'name' => $this->trans('Users'),
                'description' => $this->trans('By default, all users are made members of this group.'),
                'users' => [UsersConstant::USER_ID_ADMIN]
            ],
            [
                'gid' => GroupsConstant::GROUP_ID_ADMIN,
                'name' => $this->trans('Administrators'),
                'description' => $this->trans('Group of administrators of this site.'),
                'users' => [UsersConstant::USER_ID_ADMIN]
            ]
        ];

        foreach ($records as $record) {
            $group = new GroupEntity();
            $group->setGid($record['gid']);
            $group->setName($record['name']);
            $group->setDescription($record['description']);
            foreach ($record['users'] as $uid) {
                /** @var UserEntity $user */
                $user = $this->entityManager->find('ZikulaUsersModule:UserEntity', $uid);
                $user->addGroup($group);
            }
            $this->entityManager->persist($group);
        }

        $this->entityManager->flush();
    }
}
