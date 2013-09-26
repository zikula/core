<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package FilterUtil
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
     * Category registrys.
     *
     * @var array
     */
    protected $property;

    /**
     * Constructor.
     *
     * @param array $property Set of registry propertys to use, see setProperty() (optional) (default=null).
     * @param array $fields  Set of fields to use, see setFields() (optional) (default='category').
     * @param array $ops  Operators to enable, see activateOperators() (optional) (default=null).
     * @param bool  $default set the plugin to default (optional) (default=false).
     */
    public function __construct($property = null, $fields = 'category', $ops = null, $default = false)
    {
        parent::__construct($fields, $ops, $default);

        $this->setProperty($property);
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
     *
     */
    public function addJoinsToQuery() {
        $config = $this->config;
        $alias = $config->getAlias();
        $qb = $config->getQueryBuilder();
        $qb->join($alias.'.categories', $alias.'_cat_plugin')
           ->join($alias.'_cat_plugin.category', $alias.'_cat_plugin_category');
           //->join($alias.'_cat_plugin.registries', $alias.'_cat_plugin_registry');
    }

    /**
     * Sets the category registry.
     *
     * @param mixed $property Category Property.
     *
     * @see    CategoryUtil
     * @return void
     */
    public function setProperty($property)
    {
        if (empty($property)) {
            $this->property = null;
        } else {
            $this->property = (array)$registry;
        }
    }
    
    /**
     * Get the Doctrine2 expression object
     *
     * @param string $field
     *            Field name.
     * @param string $op
     *            Operator.
     * @param string $value
     *            Value.
     *
     * @return Expr\Base Doctrine2 expression
     */
    public function getExprObj($field, $op, $value)
    {
        $config = $this->config;
        $alias = $config->getAlias();
        $expr = $config->getQueryBuilder()->expr();

        if ($op == 'sub' || is_numeric($value)) {
            $column = $alias.'_cat_plugin_category.id';
        } else {
            $column = $alias.'_cat_plugin_category.name';
        }
        
        $con = null;
        switch ($op) {
        	case 'eq':        	    
    	        $con = $expr->eq($column, $config->toParam($value, 'category', $field));
        	        
    	    case 'ne':
    	        $con = $expr->neq($column, $config->toParam($value, 'category', $field));
    	        
	        case 'sub':
	            $items = array($value);
	            $cats = CategoryUtil::getSubCategories($value);
	            foreach ($cats as $item) {
	                $items[] = $item['id'];
	            }
	            $con = $expr->in($column, $config->toParam($items, 'category', $field));
        }
        
        if ($this->property !== null) {
            $propertyCon = $expr->in($alias.'_cat_plugin_registry.property', $this->property);
            if ($con !== null) {
                return $expr->andX($con, $propertyCon);
            } else {
                return $propertyCon;
            } 
        }
        
        return $con;
    }
}
