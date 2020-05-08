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
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\ZAuthModule\ZAuthConstant;

class BatchPasswordChangeHelper
{
    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    public function __construct(
        CurrentUserApiInterface $currentUserApi,
        ManagerRegistry $managerRegistry,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->currentUserApi = $currentUserApi;
        $this->manager = $managerRegistry->getManager();
        $this->groupRepository = $groupRepository;
    }

    public function requirePasswordChangeByGroup(int $groupId): int
    {
        $group = $this->groupRepository->find($groupId);
        $currentUid = $this->currentUserApi->get('uid');
        $count = 0;

        /** @var \Zikula\UsersModule\Entity\UserEntity $user */
        foreach ($group->getUsers() as $user) {
            $authMethod = $user->getAttributeValue('authenticationMethod');
            $uid = $user->getUid();
            if ((Constant::USER_ID_ANONYMOUS === $uid) || ($currentUid === $uid) || ('native_' !== mb_substr($authMethod, 0, 7))) {
                continue;
            }
            $user->setAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY, true);
            $count++;
        }
        $this->manager->flush();

        return $count;
    }
}
