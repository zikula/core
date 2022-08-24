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

namespace Zikula\PermissionsBundle\Initializer;

use Doctrine\ORM\EntityManagerInterface;
use Zikula\ExtensionsBundle\Initializer\BundleInitializerInterface;
use Zikula\GroupsBundle\GroupsConstant;

class PermissionsInitializer implements BundleInitializerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function init(): void
    {
        // give administrator group full access to everything as top priority
        $record = (new PermissionEntity())
            ->setGid(GroupsConstant::GROUP_ID_ADMIN)
            ->setSequence(1)
            ->setComponent('.*')
            ->setInstance('.*')
            ->setLevel(ACCESS_ADMIN);
        $this->entityManager->persist($record);

        // give user group comment access to everything as second priority
        $record = (new PermissionEntity())
            ->setGid(GroupsConstant::GROUP_ID_USERS)
            ->setSequence(2)
            ->setComponent('.*')
            ->setInstance('.*')
            ->setLevel(ACCESS_COMMENT);
        $this->entityManager->persist($record);

        // allow unregistered users only read access to everything as lowest priority
        $record = (new PermissionEntity())
            ->setGid(GroupsConstant::GROUP_ID_GUESTS)
            ->setSequence(3)
            ->setComponent('.*')
            ->setInstance('.*')
            ->setLevel(ACCESS_READ);
        $this->entityManager->persist($record);

        $this->entityManager->flush();
    }
}
