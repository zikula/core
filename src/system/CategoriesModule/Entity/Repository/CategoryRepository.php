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
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;

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
     * Returns list of category ids which are placed within a given path.
     * @deprecated
     *
     * @param string $pathField Path field name (defaults to ipath)
     * @param string $path      Given path value
     *
     * @return array
     */
    public function getIdsInPath($pathField = 'ipath', $path = '')
    {
        $categories = $this->getCategoriesInPath($pathField, $path);
        $ids = [];
        foreach ($categories as $category) {
            $ids[] = $category->getId();
        }

        return $ids;
    }

    /**
     * Returns list of categories which are placed within a given path including the path itself.
     * @deprecated
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
        $fieldMap = ['path' => 'name', 'ipath' => 'id'];
        $value = array_pop(explode('/', $path));

        $qb = $this->createQueryBuilder('c')
            ->where('c.' . $fieldMap[$pathField] . ' = :value')
            ->setParameter('value', $value);
        $categories = $qb->getQuery()->getResult();
        foreach ($categories as $category) {
            if ('path' == $pathField && $path == $category->getPath()) {
                break; // will leave $category as last tested
            }
            // if 'ipath' == $pathField, the there will only be one category in the array and it will be set to $category
        }

        return $this->children($category, false, null, 'asc', true);
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
     * {@inheritdoc}
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
     * @deprecated
     *
     * @param integer $categoryId The categoryID of the category to be updated
     * @param string  $pathField  Path field name (defaults to path)
     * @param string  $path       Given path value
     */
    public function updatePath($categoryId = 0, $pathField = 'path', $path = '')
    {
        // do nothing. path is no longer an entity property
    }
}
