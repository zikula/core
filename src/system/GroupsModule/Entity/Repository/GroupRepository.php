<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;

class GroupRepository extends EntityRepository implements GroupRepositoryInterface, ObjectRepository , Selectable
{
    /**
     * @param string $indexField
     * @return array
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findAllAndIndexBy($indexField)
    {
        return $this->createQueryBuilder('g')
            ->select('g')
            ->indexBy('g', 'g.' . $indexField)
            ->getQuery()
            ->getResult();
    }
}
