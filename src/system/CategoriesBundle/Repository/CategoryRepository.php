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

namespace Zikula\CategoriesBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use LogicException;
use Zikula\CategoriesBundle\Entity\Category;

class CategoryRepository extends NestedTreeRepository implements CategoryRepositoryInterface
{
    /**
     * Code from Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
     */
    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = Category::class;

        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass($entityClass);
        if (null === $manager) {
            throw new LogicException(sprintf('Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.', $entityClass));
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }

    public function countForContext(string $name = '', int $parentId = 0, int $excludedId = null): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');
        if ('' !== $name) {
            $qb->where('c.name = :name')
                ->setParameter('name', $name);
        }

        if ($parentId > 0) {
            $qb->andWhere('c.parent = :parentid')
               ->setParameter('parentid', $parentId);
        }

        if (null !== $excludedId && $excludedId > 0) {
            $qb->andWhere('c.id != :id')
               ->setParameter('id', $excludedId);
        }

        $query = $qb->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    public function getLastByParent(int $parentId = 0): ?Category
    {
        if ($parentId < 1) {
            return null;
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.parent = :parentId')
            ->setParameter('parentId', $parentId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getSingleResult();
    }
}
