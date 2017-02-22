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

use Doctrine\ORM\EntityRepository;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;

class CategoryRepository extends EntityRepository implements CategoryRepositoryInterface
{
    /**
     * Returns amount of categories for specified filters.
     *
     * @param string $name       Name filter
     * @param int    $parentId   Optional parent category id filter
     * @param int    $excludedId Optional category id filter for exclusion
     *
     * @return integer
     */
    public function countForContext($name = '', $parentId = 0, $excludedId = 0)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.name = :name')
            ->setParameter('name', $name);

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
     * Returns list of category ids which are placed within a given path.
     *
     * @param string $pathField Path field name (defaults to ipath)
     * @param string $path      Given path value
     *
     * @return array
     */
    public function getIdsInPath($pathField = 'ipath', $path = '')
    {
        if (!in_array($pathField, ['path', 'ipath']) || $path == '') {
            return null;
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.' . $pathField . ' = :path')
            ->setParameter('path', $path . '%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns list of categories which are placed within a given path including the path itself.
     *
     * @param string $pathField Path field name (defaults to ipath)
     * @param string $path      Given path value
     *
     * @return array
     */
    public function getCategoriesInPath($pathField = 'ipath', $path = '')
    {
        if (!in_array($pathField, ['path', 'ipath']) || $path == '') {
            return null;
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.' . $pathField . ' = :path')
            ->orWhere('c.' . $pathField . ' LIKE :pathwc')
            ->setParameter('path', $path)
            ->setParameter('pathwc', $path . '/%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the last added category within a given parent category.
     *
     * @param int $parentId Parent category id
     *
     * @return CategoryEntity|null
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
     * Selects categories using arbitrary parameters.
     *
     * @param string  $where       The where clause to use in the select (optional) (default='')
     * @param string  $sort        The order-by clause to use in the select (optional) (default='')
     * @param array   $columnArray Array of columns to select (optional) (default=null)
     *
     * @return array
     * @deprecated
     */
    public function freeSelect($where = '', $sort = '', $columnArray = null)
    {
        $selection = 'c';
        if (!empty($columnArray)) {
            $columns = [];
            foreach ($columnArray as $column) {
                $columns[] = 'c.' . $column;
            }
            $selection = implode(', ', $columns);
        }

        $qb = $this->createQueryBuilder('c')
            ->select($selection);

        if (!empty($where)) {
            $qb->where($where);
        }

        if ($sort != '') {
            $sort = str_replace('ORDER BY', '', $sort);
            if (false !== stripos($sort, 'ASC')) {
                $qb->orderBy(str_ireplace('ASC', '', $sort), 'ASC');
            } elseif (false !== stripos($sort, 'DESC')) {
                $qb->orderBy(str_ireplace('DESC', '', $sort), 'DESC');
            } else {
                $qb->orderBy($sort);
            }
        } else {
            $qb->orderBy('c.sort_value, c.path');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Updates the parent id of one or multiple categories.
     *
     * @param integer $oldParentId The categoryID of the category to be updated
     * @param integer $newParentId The categoryID of the new parent category
     * @param boolean $includeRoot Whether or not to also move the root folder (optional) (default=true)
     */
    public function updateParent($oldParentId = 0, $newParentId = 0, $includeRoot = true)
    {
        if (!is_numeric($oldParentId) || $oldParentId < 1 || !is_numeric($newParentId) || $newParentId < 1 || !is_bool($includeRoot)) {
            return;
        }

        $whereField = $includeRoot ? 'id' : 'parent';

        $qb = $this->_em->createQueryBuilder()
            ->update('Zikula\CategoriesModule\Entity\CategoryEntity', 'c')
            ->set('c.parent', ':newParent')
            ->setParameter('newParent', $newParentId)
            ->where('c.' . $whereField . ' = :pid')
            ->setParameter('pid', $oldParentId);

        $qb->getQuery()->execute();
    }

    /**
     * Updates the path for a given category id.
     *
     * @param integer $categoryId The categoryID of the category to be updated
     * @param string  $pathField  Path field name (defaults to path)
     * @param string  $path       Given path value
     */
    public function updatePath($categoryId = 0, $pathField = 'path', $path = '')
    {
        if (!is_numeric($categoryId) || $categoryId < 1 || !in_array($pathField, ['path', 'ipath']) || $path == '') {
            return;
        }

        $qb = $this->_em->createQueryBuilder()
            ->update('Zikula\CategoriesModule\Entity\CategoryEntity', 'c')
            ->set('c.' . $pathField, ':path')
            ->setParameter('path', $path)
            ->where('c.id = :id')
            ->setParameter('id', $categoryId);

        $qb->getQuery()->execute();
    }
}
