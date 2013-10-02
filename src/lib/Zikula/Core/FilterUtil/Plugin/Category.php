<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license    GNU/LGPv3 (or at your option any later version).
 * @package    FilterUtil
 * @subpackage Filter
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Core\FilterUtil\Plugin;

use Zikula\Core\FilterUtil;
use \CategoryUtil;

/**
 * FilterUtil category filter plugin
 */
class Category extends FilterUtil\AbstractBuildPlugin implements FilterUtil\JoinInterface
{
    /**
     * modulename of the entity.
     *
     * @var string
     */
    protected $modname;

    /**
     * filter on this propery
     *
     * @var array
     */
    protected $property;

    /**
     * Constructor.
     *
     * @param string modulename of the entity.
     * @param array $property Set of registry propertys to use, see setProperty() (optional) (default=null).
     * @param array $fields Set of fields to use, see setFields() (optional) (default='category').
     * @param array $ops Operators to enable, see activateOperators() (optional) (default=null).
     * @param bool $default set the plugin to default (optional) (default=false).
     */
    public function __construct($modname = null, $property = null, $fields = 'category', $ops = null, $default = false)
    {
        parent::__construct($fields, $ops, $default);

        $this->setProperty($property);
        $this->modname = $modname;
    }

    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators.
     */
    public function availableOperators()
    {
        return array(
            'eq',
            'ne',
            'sub'
        );
    }

    /**
     * add Join to QueryBuilder.
     */
    public function addJoinsToQuery()
    {
        $config = $this->config;
        $alias = $config->getAlias();
        $qb = $config->getQueryBuilder();
        $qb->join($alias . '.categories', $alias . '_cat_plugin')->join(
            $alias . '_cat_plugin.category', $alias . '_cat_plugin_category');
    }

    /**
     * Sets the category registry.
     *
     * @param mixed $property Category Property.
     *
     * @see CategoryUtil
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
     * get the id of the registrys defined by $module and $propery.
     *
     * @return array();
     */
    // TODO protected
    public function getRegistryIds()
    {
        $from = $this->config->getQueryBuilder()->getDQLPart('from');
        $parts = explode('\\', $from[0]->getFrom());
        $entityname = str_replace('Entity', '', end($parts));

        $em = $this->config->getEntityManager();

        $rCategories = $em->getRepository(
            'Zikula\Module\CategoriesModule\Entity\CategoryRegistryEntity')->findBy(
            array(
                'modname' => $this->modname,
                'entityname' => $entityname
            ));

        $ids = array();
        foreach ($rCategories as $cat) {
            if (in_array($cat->getProperty(), $this->property)) {
                $ids[] = $cat->getId();
            }
        }

        return $ids;
    }

    /**
     * Get the Doctrine2 expression object
     *
     * @param string $field Field name.
     * @param string $op Operator.
     * @param string $value Value.
     *
     * @return Expr\Base Doctrine2 expression
     */
    public function getExprObj($field, $op, $value)
    {
        $config = $this->config;
        $alias = $config->getAlias();
        $expr = $config->getQueryBuilder()->expr();

        if ($op == 'sub' || is_numeric($value)) {
            $column = $alias . '_cat_plugin_category.id';
        } else {
            $column = $alias . '_cat_plugin_category.name';
        }

        $con = null;
        switch ($op) {
            case 'eq':
                $con = $expr->eq($column, $config->toParam($value, 'category', $field));

            case 'ne':
                $con = $expr->neq($column, $config->toParam($value, 'category', $field));

            case 'sub':
                $items = array(
                    $value
                );
                $cats = CategoryUtil::getSubCategories($value);
                foreach ($cats as $item) {
                    $items[] = $item['id'];
                }
                $con = $expr->in($column, $config->toParam($items, 'category', $field));
        }

        if ($this->modname !== null && $this->property !== null) {
            $propertyCon = $expr->in($alias . '_cat_plugin.categoryRegistryId',
                $config->toParam($this->getRegistryIds(), 'category', $field));
            if ($con !== null) {
                return $expr->andX($con, $propertyCon);
            } else {
                return $propertyCon;
            }
        }

        return $con;
    }
}
