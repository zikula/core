<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * FilterUtil plugin to access a single category field.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
 *
 * Operator "sub" can filter for a category and all its subcategories
 */
class FilterUtil_Filter_Pmlist extends FilterUtil_AbstractPlugin implements FilterUtil_BuildInterface
{
    /**
     * Enabled operators.
     *
     * @var array
     */
    protected $ops = [];

    /**
     * Fields to use the plugin for.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Constructor
     *
     * Argument $config may contain:
     *  fields:  Set of fields to work on.
     *  ops:     Enabled Operators.
     *  default: This plugin is the default plugin for all fields?
     *
     * @param array $config Configuration
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
            $this->activateOperators(['eq', 'ne', 'lt', 'le', 'gt', 'ge', 'like', 'null', 'notnull']);
        }

        if ($config['default'] == true || count($this->fields) <= 0) {
            $this->default = true;
        }
    }

    /**
     * Adds fields to list in common way.
     *
     * @param mixed $fields Fields to add
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
     * @return array List of fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Activates the requested Operators.
     *
     * @param mixed $op Operators to activate
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
     * @return array Set of Operators and Arrays
     */
    public function getOperators()
    {
        $fields = $this->getFields();
        if ($this->default == true) {
            $fields[] = '-';
        }

        $ops = [];
        foreach ($this->ops as $op) {
            $ops[$op] = $fields;
        }

        return $ops;
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
     * Returns SQL code.
     *
     * @param string $field Field name
     * @param string $op    Operator
     * @param string $value Test value
     *
     * @return array SQL code array
     */
    public function getSQL($field, $op, $value)
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
                $items = [];
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

        return ['where' => $where];
    }

    /**
     * Returns DQL code.
     *
     * @param string $field Field name
     * @param string $op    Operator
     * @param string $value Test value
     *
     * @return array Doctrine Query where clause and parameters
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
                $items = [$value];
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

        return ['where' => $where];
    }
}
