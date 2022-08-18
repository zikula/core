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

namespace Zikula\ZAuthModule\Helper;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use function Symfony\Component\String\s;
use Zikula\GroupsModule\Repository\GroupRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\ZAuthModule\ZAuthConstant;

class BatchPasswordChangeHelper
{
    private ObjectManager $manager;

    public function __construct(
        private readonly CurrentUserApiInterface $currentUserApi,
        ManagerRegistry $managerRegistry,
        private readonly GroupRepositoryInterface $groupRepository
    ) {
        $this->manager = $managerRegistry->getManager();
    }

    public function requirePasswordChangeByGroup(int $groupId): int
    {
        $group = $this->groupRepository->find($groupId);
        $currentUid = $this->currentUserApi->get('uid');
        $count = 0;

        /** @var \Zikula\UsersModule\Entity\UserEntity $user */
        foreach ($group->getUsers() as $user) {
            $authMethod = s($user->getAttributeValue('authenticationMethod'));
            $uid = $user->getUid();
            if (Constant::USER_ID_ANONYMOUS === $uid || $currentUid === $uid || !$authMethod->startsWith('native_')) {
                continue;
            }
            $user->setAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY, true);
            $count++;
        }
        $this->manager->flush();

        return $count;
    }
}
