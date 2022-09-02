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

namespace Zikula\GroupsBundle\Initializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\BundleInitializer\BundleInitializerInterface;
use Zikula\GroupsBundle\GroupsConstant;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

class GroupsInitializer implements BundleInitializerInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function init(): void
    {
        /** @var User $adminUser */
        $adminUser = $this->userRepository->find(UsersConstant::USER_ID_ADMIN);

        $group = (new GroupEntity())
            ->setGid(GroupsConstant::GROUP_ID_USERS)
            ->setName($this->translator->trans('Users'))
            ->setDescription($this->translator->trans('By default, all users are made members of this group.'));
        $adminUser->addGroups($group);
        $this->entityManager->persist($group);

        $group = (new GroupEntity())
            ->setGid(GroupsConstant::GROUP_ID_ADMIN)
            ->setName($this->translator->trans('Administrators'))
            ->setDescription($this->translator->trans('Group of administrators of this site.'));
        $adminUser->addGroups($group);
        $this->entityManager->persist($group);

        $this->entityManager->flush();
    }
}
