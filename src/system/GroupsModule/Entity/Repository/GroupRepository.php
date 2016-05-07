<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Constant as UsersConstant;

class GroupRepository extends EntityRepository
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
