<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;

/**
 * Class CategoryRepository
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/tree.md
 */
class CategoryRepository extends NestedTreeRepository implements CategoryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function countForContext($name = '', $parentId = 0, $excludedId = 0)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');
        if ('' != $name) {
            $qb->where('c.name = :name')
                ->setParameter('name', $name);
        }

        if ($parentId > 0) {
            $qb->andWhere('c.parent = :parentid')
               ->setParameter('parentid', $parentId);
        }

        if ($excludedId > 0) {
            $qb->andWhere('c.id != :id')
               ->setParameter('id', $excludedId);
        }

        $query = $qb->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastByParent($parentId = 0)
    {
        if (!is_numeric($parentId) || $parentId < 1) {
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

    /**
     * {@inheritdoc}
     */
    public function updateParent($oldParentId = 0, $newParentId = 0, $includeRoot = true)
    {
        if (!is_numeric($oldParentId) || $oldParentId < 1 || !is_numeric($newParentId) || $newParentId < 1 || !is_bool($includeRoot)) {
            return;
        }
        $searchBy = $includeRoot ? 'id' : 'parent';
        $entities = $this->findBy([$searchBy => $oldParentId]);
        $newParent = $this->find($newParentId);
        /** @var CategoryEntity[] $entities */
        foreach ($entities as $entity) {
            $entity->setParent($newParent);
        }
        $this->_em->flush();
    }
}
