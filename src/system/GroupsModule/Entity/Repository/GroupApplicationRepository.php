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

namespace Zikula\GroupsModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupApplicationRepositoryInterface;

class GroupApplicationRepository extends ServiceEntityRepository implements GroupApplicationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupApplicationEntity::class);
    }
}
