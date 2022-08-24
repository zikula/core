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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\GroupsBundle\GroupsConstant;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Event\ActiveUserPostDeletedEvent;
use Zikula\UsersBundle\Event\RegistrationPostDeletedEvent;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

class DeleteHelper
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UserRepositoryInterface $userRepository,
        private readonly GroupRepositoryInterface $groupRespository
    ) {
    }

    /**
     * @param string $param gid|status|uid
     */
    public function getUserCollection(string $param, string $value, string $date = null): Collection
    {
        switch ($param) {
            case 'gid':
                if (in_array((int) $value, [GroupsConstant::GROUP_ID_USERS, GroupsConstant::GROUP_ID_ADMIN], true)) {
                    throw new \InvalidArgumentException('Cannot delete from main User or Administrator group.');
                }
                $users = $this->groupRespository->find((int) $value)->getUsers();
                break;
            case 'status':
                $statuses = [
                    'I' => UsersConstant::ACTIVATED_INACTIVE,
                    'P' => UsersConstant::ACTIVATED_PENDING_REG,
                    'M' => UsersConstant::ACTIVATED_PENDING_DELETE
                ];
                if (!array_key_exists($value, $statuses)) {
                    throw new \InvalidArgumentException('Invalid status key');
                }
                $users = $this->userRepository->findBy(['activated' => $statuses[$value]]);
                $users = new ArrayCollection($users);
                break;
            case 'uid':
                $user = $this->userRepository->find((int) $value);
                $users = new ArrayCollection([$user]);
                break;
            default:
                throw new \InvalidArgumentException('Invalid option name');
        }
        if (isset($date)) {
            $date = \DateTime::createFromFormat('YmdHis', $date, new \DateTimeZone('UTC'));
            $users = $users->filter(function (UserEntity $user) use ($date) {
                return $user->getRegistrationDate() < $date;
            });
        }
        $adminUser = $this->userRepository->find(UsersConstant::USER_ID_ADMIN);
        if ($users->contains($adminUser)) {
            $users->removeElement($adminUser);
        }

        return $users;
    }

    public function deleteUser(UserEntity $user, bool $fullDeletion = false): void
    {
        if (UsersConstant::ACTIVATED_ACTIVE === $user->getActivated()) {
            $this->eventDispatcher->dispatch(new ActiveUserPostDeletedEvent($user, $fullDeletion));
        } else {
            $this->eventDispatcher->dispatch(new RegistrationPostDeletedEvent($user));
        }
        if ($fullDeletion) {
            $this->userRepository->removeAndFlush($user);
        } else {
            $this->convertUserToGhost($user);
        }
    }

    private function convertUserToGhost(UserEntity $userEntity): void
    {
        $userEntity->setUname('ghost');
        $userEntity->setEmail('nobody@mailinator.com');
        $userEntity->setActivated(UsersConstant::ACTIVATED_PENDING_DELETE);
        $userEntity->removeGroups();
        foreach ($userEntity->getAttributes() as $attribute) {
            $userEntity->delAttribute($attribute->getName());
        }
        $this->userRepository->persistAndFlush($userEntity);
    }
}
