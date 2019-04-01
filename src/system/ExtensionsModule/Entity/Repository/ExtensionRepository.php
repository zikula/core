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

namespace Zikula\ExtensionsModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class ExtensionRepository extends ServiceEntityRepository implements ExtensionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtensionEntity::class);
    }

    public function get(string $name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function getPagedCollectionBy(
        array $criteria,
        array $orderBy = null,
        int $limit = 0,
        int $offset = 1
    ): Paginator {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')->from($this->_entityName, 'e');
        $i = 1;
        foreach ($criteria as $field => $value) {
            $comparator = is_array($value) ? 'IN' : '=';
            $qb->andWhere("e.${field} ${comparator} param${i}")
                ->setParameter("param${i}", $value);
            $i++;
        }
        if (isset($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $qb->orderBy("e.${field}", $direction);
            }
        }
        $query = $qb->getQuery();
        if ($limit > 0) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset - 1);
        }

        return new Paginator($query);
    }

    public function getIndexedArrayCollection(string $indexBy): array
    {
        $qb = $this->createQueryBuilder('e')->indexBy('e', 'e.' . $indexBy);

        return $qb->getQuery()->getArrayResult();
    }

    public function updateName(string $oldName, string $newName): void
    {
        $query = $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'e')
            ->set('e.name', ':newname')
            ->setParameter('newname', $newName)
            ->where('e.name = :oldname')
            ->setParameter('oldname', $oldName)
            ->getQuery();
        $query->execute();
    }

    public function persistAndFlush(ExtensionEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    public function removeAndFlush(ExtensionEntity $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }
}
