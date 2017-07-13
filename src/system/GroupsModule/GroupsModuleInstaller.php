<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule;

use Zikula\Core\AbstractExtensionInstaller;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Installation and upgrade routines for the groups module.
 */
class GroupsModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * initialise the groups module
     *
     * @return bool true if initialisation successful, false otherwise
     */
    public function install()
    {
        try {
            $this->schemaTool->create([
                'Zikula\GroupsModule\Entity\GroupEntity',
                'Zikula\GroupsModule\Entity\GroupApplicationEntity'
            ]);
        } catch (\Exception $e) {
            return false;
        }

        // set all our module vars
        $this->setVar('itemsperpage', 25);
        $this->setVar('defaultgroup', 1);
        $this->setVar('mailwarning', false);
        $this->setVar('hideclosed', false);
        $this->setVar('hidePrivate', false);

        // create the default data for the modules module
        $this->defaultdata();

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * @param string $oldVersion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldVersion)
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '2.3.2':
            case '2.4.0':
                $this->setVar('mailwarning', (bool)$this->getVar('mailwarning'));
                $this->setVar('hideclosed', (bool)$this->getVar('hideclosed'));
                $this->setVar('hidePrivate', false);
            case '2.4.1':
                /** @var UserEntity $anonymousUser */
                $anonymousUser = $this->container->get('zikula_users_module.user_repository')->find(UsersConstant::USER_ID_ANONYMOUS);
                $usersGroup = $this->container->get('zikula_groups_module.group_repository')->find(GroupsConstant::GROUP_ID_USERS);
                $anonymousUser->getGroups()->removeElement($usersGroup);
                $this->entityManager->flush($anonymousUser);
                $this->addFlash('info', $this->__('NOTICE: The old type of "anonymous" user has been removed from the Users group. This may require manual adjustment of your permission schema.'));
            case '2.4.2':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the groups module
     *
     * @return bool false this module cannot be deleted
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * create the default data for the groups module
     *
     * @return void
     */
    public function defaultdata()
    {
        $records = [
            [
                'gid' => GroupsConstant::GROUP_ID_USERS,
                'name' => $this->__('Users'),
                'description' => $this->__('By default, all users are made members of this group.'),
                'prefix' => $this->__('usr'),
                'users' => [UsersConstant::USER_ID_ADMIN]
            ],
            [
                'gid' => GroupsConstant::GROUP_ID_ADMIN,
                'name' => $this->__('Administrators'),
                'description' => $this->__('Group of administrators of this site.'),
                'prefix' => $this->__('adm'),
                'users' => [UsersConstant::USER_ID_ADMIN]
            ]
        ];

        foreach ($records as $record) {
            $group = new GroupEntity();
            $group->setGid($record['gid']);
            $group->setName($record['name']);
            $group->setDescription($record['description']);
            $group->setPrefix($record['prefix']);
            foreach ($record['users'] as $uid) {
                $user = $this->entityManager->find('ZikulaUsersModule:UserEntity', $uid);
                $user->addGroup($group);
            }
            $this->entityManager->persist($group);
        }

        $this->entityManager->flush();
    }
}
