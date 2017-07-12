<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\FilterUtil;

use Doctrine\ORM\Query\Expr\Base as BaseExpr;
use Zikula\Component\FilterUtil;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;

/**
 * FilterUtil category filter plugin
 *
 * @deprecated
 */
class CategoryPlugin extends FilterUtil\AbstractBuildPlugin implements FilterUtil\JoinInterface
{
    /**
     * Module name of the entity.
     *
     * @var string
     */
    private $modname;

    /**
     * filter on this property
     *
     * @var array
     */
    private $property;

    /**
     * Constructor.
     *
     * @param string       $modname  Module name of the entity
     * @param array        $property Set of registry properties to use, see setProperty()
     * @param array|string $fields   Set of fields to use, see setFields() (optional) (default='category')
     * @param array        $ops      Operators to enable, see activateOperators() (optional) (default=null)
     * @param bool         $default  set the plugin to default (optional) (default=false)
     */
    public function __construct($modname = null, $property = null, $fields = 'category', $ops = [], $default = false)
    {
        $this->setProperty($property);
        $this->modname = $modname;

        parent::__construct($fields, $ops, $default);
    }

    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators
     */
    public function availableOperators()
    {
        return ['eq', 'ne', 'sub'];
    }

    /**
     * add Join to QueryBuilder.
     */
    public function addJoinsToQuery()
    {
        $config = $this->config;
        $alias = $config->getAlias();
        $qb = $config->getQueryBuilder();
        $qb->join($alias.'.categories', $alias.'_cat_plugin')->join(
            $alias.'_cat_plugin.category',
            $alias.'_cat_plugin_category'
        );
    }

    /**
     * Sets the category registry.
     *
     * @param mixed $property Category Property
     */
    public function setProperty($property)
    {
        if (empty($property)) {
            $this->property = null;
        } else {
            $this->property = (array) $property;
        }
    }

    /**
     * Get the id of the registry defined by $module and $property.
     *
     * @return array;
     */
    protected function getRegistryIds()
    {
        $from = $this->config->getQueryBuilder()->getDQLPart('from');
        $parts = explode('\\', $from[0]->getFrom());
        $entityName = str_replace('Entity', '', end($parts));
        $em = $this->config->getEntityManager();
        $rCategories = $em->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')
            ->findBy([
                'modname' => $this->modname,
                'entityname' => $entityName,
            ]);
        $ids = [];
        /** @var $cat CategoryRegistryEntity */
        foreach ($rCategories as $cat) {
            if (in_array($cat->getProperty(), $this->property)) {
                $ids[] = $cat->getId();
            }
        }

        return $ids;
    }

    /**
     * Get the Doctrine expression object
     *
     * @param string $field Field name
     * @param string $op    Operator
     * @param string $value Value
     *
     * @return BaseExpr Doctrine expression
     */
    public function getExprObj($field, $op, $value)
    {
        $config = $this->config;
        $alias = $config->getAlias();
        $expr = $config->getQueryBuilder()->expr();
        if ($op == 'sub' || is_numeric($value)) {
            $column = $alias.'_cat_plugin_category.id';
            $prop = 'id';
        } else {
            $column = $alias.'_cat_plugin_category.name';
            $prop = 'name';
        }
        $con = null;
        switch ($op) {
            case 'eq':
                $con = $expr->eq($column, $config->toParam($value, 'category', $field));
                break;
            case 'ne':
                $con = $expr->neq($column, $config->toParam($value, 'category', $field));
                break;
            case 'sub':
                $items = [];
                $repo = $this->config->getEntityManager()->getRepository('ZikulaCategoriesModule:CategoryEntity');
                $parent = $repo->findOneBy([$prop => $value]);
                $categories = $repo->getChildren($parent, false, null, 'ASC', true);
                foreach ($categories as $category) {
                    $items[] = $category->getId();
                }
                $con = $expr->in($column, $config->toParam($items, 'category', $field));
                break;
        }
        if (null !== $this->modname && null !== $this->property) {
            $propertyCon = $expr->in(
                $alias.'_cat_plugin.categoryRegistryId',
                $config->toParam($this->getRegistryIds(), 'category', $field)
            );
            if (null !== $con) {
                return $expr->andX($con, $propertyCon);
            }

            return $propertyCon;
        }

        return $con;
    }
}
