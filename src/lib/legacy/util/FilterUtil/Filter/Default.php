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
 * Provide a set of default filter operations.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
 */
class FilterUtil_Filter_Default extends FilterUtil_AbstractPlugin implements FilterUtil_BuildInterface
{
    /**
     * Enabled operators.
     *
     * @var array
     */
    protected $_ops = [];

    /**
     * Fields to use the plugin for.
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * Constructor.
     *
     * Argument $config may contain
     *  fields:   Set of fields to use, see setFields().
     *  ops:      Operators to enable, see activateOperators().
     *
     * @param array $config Configuration
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['fields']) && (!isset($this->_fields) || !is_array($this->_fields))) {
            $this->addFields($config['fields']);
        }

        if (isset($config['ops']) && (!isset($this->_ops) || !is_array($this->_ops))) {
            $this->activateOperators($config['ops']);
        } else {
            $this->activateOperators(['eq', 'ne', 'lt', 'le', 'gt', 'ge', 'search', 'like', 'likefirst', 'null', 'notnull']);
        }

        if (isset($config['default']) && true == $config['default'] || count($this->_fields) <= 0) {
            $this->default = true;
        }
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
        static $ops = [
            'eq', 'ne', 'lt', 'le', 'gt', 'ge',
            'search', 'like', 'likefirst',
            'null', 'notnull'
        ];

        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op) && false === array_search($op, $this->_ops) && false !== array_search($op, $ops)) {
            $this->_ops[] = $op;
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
        } elseif (!empty($fields) && $this->fieldExists($fields) && false === array_search($fields, $this->_fields)) {
            $this->_fields[] = $fields;
        }
    }

    /**
     * Returns the fields.
     *
     * @return array List of fields
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Get activated operators.
     *
     * @return array Set of Operators and Arrays
     */
    public function getOperators()
    {
        $fields = $this->getFields();
        if (true == $this->default) {
            $fields[] = '-';
        }

        $ops = [];
        foreach ($this->_ops as $op) {
            $ops[$op] = $fields;
        }

        return $ops;
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
        if (!$this->fieldExists($field)) {
            return '';
        }

        $where = '';
        $column = $this->column[$field];

        switch ($op) {
            case 'eq':
                $where = "$column = '$value'";
                break;

            case 'ne':
                $where = "$column <> '$value'";
                break;

            case 'lt':
                $where = "$column < '$value'";
                break;

            case 'le':
                $where = "$column <= '$value'";
                break;

            case 'gt':
                $where = "$column > '$value'";
                break;

            case 'ge':
                $where = "$column >= '$value'";
                break;

            case 'search':
                $where = "$column LIKE '%$value%'";
                break;

            case 'like':
                $where = "$column LIKE '$value'";
                break;

            case 'likefirst':
                $where = "$column LIKE '$value%'";
                break;

            case 'null':
                $where = "($column = '' OR $column IS NULL)";
                break;

            case 'notnull':
                $where = "($column <> '' OR $column IS NOT NULL)";
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
        if (!$this->fieldExists($field)) {
            return '';
        }

        $where = '';
        $params = [];
        $column = $this->getColumn($field);

        switch ($op) {
            case 'eq':
                $where = "$column = ?";
                $params[] = $value;
                break;

            case 'ne':
                $where = "$column <> ?";
                $params[] = $value;
                break;

            case 'lt':
                $where = "$column < ?";
                $params[] = $value;
                break;

            case 'le':
                $where = "$column <= ?";
                $params[] = $value;
                break;

            case 'gt':
                $where = "$column > ?";
                $params[] = $value;
                break;

            case 'ge':
                $where = "$column >= ?";
                $params[] = $value;
                break;

            case 'search':
                $where = "$column LIKE ?";
                $params[] = '%'.$value.'%';
                break;

            case 'like':
                $where = "$column LIKE ?";
                $params[] = $value;
                break;

            case 'likefirst':
                $where = "$column LIKE ?";
                $params[] = $value."%";
                break;

            case 'null':
                $where = "($column = '' OR $column IS NULL)";
                break;

            case 'notnull':
                $where = "($column <> '' OR $column IS NOT NULL)";
                break;
        }

        return ['where' => $where, 'params' => $params];
    }
}
