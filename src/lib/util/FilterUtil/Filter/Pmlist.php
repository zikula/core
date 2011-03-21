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
 * FilterUtil plugin to access a single category field.
 *
 * Operator "sub" can filter for a category and all its subcategories.
 */
class FilterUtil_Filter_Pmlist extends FilterUtil_AbstractPlugin implements FilterUtil_BuildInterface
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
     * Constructor
     *
     * Argument $config may contain:
     *  fields:  Set of fields to work on.
     *  ops:     Enabled Operators.
     *  default: This plugin is the default plugin for all fields?
     *
     * @param array $config Configuration.
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['fields'])) {
            $this->addFields($config['fields']);
        }

        if (isset($config['ops']) && (!isset($this->ops) || !is_array($this->ops))) {
            $this->activateOperators($config['ops']);
        } else {
            $this->activateOperators(array('eq', 'ne', 'lt', 'le', 'gt', 'ge', 'like', 'null', 'notnull'));
        }

        if ($config['default'] == true || count($this->fields) <= 0) {
            $this->default = true;
        }
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
        } elseif (!empty($fields) && $this->fieldExists($fields) && array_search($fields, $this->fields) === false) {
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
     * Activates the requested Operators.
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
     * Get activated operators.
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

        $where  = '';
        $column = $this->column[$field];

        switch ($op) {
            case 'eq':
                $where = "$column = '$value'";
                break;

            case 'ne':
                $where = "$column <> '$value'";
                break;

            case 'sub':
                $cats = CategoryUtil::getSubCategories($value);
                $items = array();
                $items[] = $value;
                foreach ($cats as $item) {
                    $items[] = $item['id'];
                }
                if (count($items) == 1) {
                    $where = "$column = '".implode("", $items)."'";
                } else {
                    $where = "$column IN ('".implode("','", $items)."')";
                }
                break;
        }

        return array('where' => $where);
    }

    /**
     * Returns DQL code.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return array Doctrine Query where clause and parameters.
     */
    public function getDql($field, $op, $value)
    {
        if (array_search($op, $this->ops) === false || !$this->fieldExists($field)) {
            return '';
        }

        $where = '';
        $column = $this->getColumn($field);

        switch ($op) {
            case 'eq':
                $where = "$column = '$value'";
                break;

            case 'ne':
                $where = "$column <> '$value'";
                break;

            case 'sub':
                $cats = CategoryUtil::getSubCategories($value);
                $items = array();
                $items[] = $value;
                foreach ($cats as $item) {
                    $items[] = $item['id'];
                }
                if (count($items) == 1) {
                    $where = "$column = '".implode("", $items)."'";
                } else {
                    $where = "$column IN ('".implode("','", $items)."')";
                }
                break;
        }

        return array('where' => $where);
    }
}
