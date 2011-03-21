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

/**
 * FilterUtil category filter plugin
 */
class FilterUtil_Filter_Category extends FilterUtil_AbstractPlugin implements FilterUtil_BuildInterface
{
    /**
     * Enabled operators.
     *
     * @var array
     */
    protected $ops = array();

    /**
     * Fields to use the plugin for.
     *
     * @var array
     */
    protected $fields = array();

    /**
     * Category property.
     *
     * @var array
     */
    protected $property;

    /**
     * Constructor.
     *
     * Argument $config may contain
     *  fields:   Set of fields to use, see setFields().
     *  property: Property set of the categories to filter by.
     *            As in DBUtil categoryFilter. See setProperty().
     *  ops:      Operators to enable, see activateOperators().
     *
     * @param array $config Configuration.
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['fields']) && is_array($config['fields'])) {
            $this->addFields($config['fields']);
        }

        if (isset($config['property'])) {
            $this->setProperty($config['property']);
        } else {
            $this->setProperty('Main');
        }

        if (isset($config['ops']) && (!isset($this->ops) || !is_array($this->ops))) {
            $this->activateOperators($config['ops']);
        } else {
            $this->activateOperators($this->availableOperators());
        }
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
     * Adds fields to list in common way.
     *
     * @param mixed $fields Fields to add.
     *
     * @return void
     */
    public function addFields($fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $fld) {
                $this->addFields($fld);
            }
        } elseif (!empty($fields) && !$this->fieldExists($fields) && array_search($fields, $this->fields) === false) {
            // the field must not be a column of the table as it should be using the zikula categorization system
            $this->fields[] = $fields;
        }
    }

    /**
     * Returns the fields.
     *
     * @return array List of fields.
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Adds operators.
     *
     * @param mixed $op Operators to activate.
     *
     * @return void
     */
    public function activateOperators($op)
    {
        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op) && array_search($op, $this->ops) === false && array_search($op, $this->availableOperators()) !== false) {
            $this->ops[] = $op;
        }
    }

    /**
     * Get operators
     *
     * @return array Set of Operators and Arrays.
     */
    public function getOperators()
    {
        $fields = $this->getFields();
        if ($this->default == true) {
            $fields[] = '-';
        }

        $ops = array();
        foreach ($this->ops as $op) {
            $ops[$op] = $fields;
        }

        return $ops;
    }

    /**
     * Sets the category property.
     *
     * @param mixed $property Category Property.
     *
     * @see    CategoryUtil
     * @return void
     */
    public function setProperty($property)
    {
        $this->property = (array)$property;
    }

    /**
     * Returns SQL code.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return array SQL code array.
     */
    function getSQL($field, $op, $value)
    {
        if (array_search($op, $this->availableOperators()) === false || array_search($field, $this->fields) === false) {
            return '';
        }

        $items = array($value);
        if ($op == 'sub') {
            $cats = CategoryUtil::getSubCategories($value);
            foreach ($cats as $item) {
                $items[] = $item['id'];
            }
        }

        $filter = array('__META__' => array('module' => $this->module));
        foreach ($this->property as $prop) {
            $filter[$prop] = $items;
        }

        $where = DBUtil::generateCategoryFilterWhere($this->dbtable, false, $filter);
        if ($op == 'ne') {
            $where = str_replace(' IN ', ' NOT IN ', $where);
        }

        return array('where' => $where);
    }
}
