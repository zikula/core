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
 * Provide a set of default filter operations.
 */
class FilterUtil_Filter_Default extends FilterUtil_AbstractPlugin implements FilterUtil_BuildInterface
{
    /**
     * Enabled operators.
     *
     * @var array
     */
    protected $_ops = array();

    /**
     * Fields to use the plugin for.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Constructor.
     *
     * Argument $config may contain
     *  fields:   Set of fields to use, see setFields().
     *  ops:      Operators to enable, see activateOperators().
     *
     * @param array $config Configuration.
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
            $this->activateOperators(array('eq', 'ne', 'lt', 'le', 'gt', 'ge', 'search', 'like', 'likefirst', 'null', 'notnull'));
        }

        if (isset($config['default']) && $config['default'] == true || count($this->_fields) <= 0) {
            $this->default = true;
        }
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
        static $ops = array(
                            'eq',
                            'ne',
                            'lt',
                            'le',
                            'gt',
                            'ge',
                            'search',
                            'like',
                            'likefirst',
                            'null',
                            'notnull'
                           );

        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op) && array_search($op, $this->_ops) === false && array_search($op, $ops) !== false) {
            $this->_ops[] = $op;
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
        } elseif (!empty($fields) && $this->fieldExists($fields) && array_search($fields, $this->_fields) === false) {
            $this->_fields[] = $fields;
        }
    }

    /**
     * Returns the fields.
     *
     * @return array List of fields.
     */
    public function getFields()
    {
        return $this->_fields;
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
        foreach ($this->_ops as $op) {
            $ops[$op] = $fields;
        }

        return $ops;
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
        if (!$this->fieldExists($field)) {
            return '';
        }

        $where = '';
        $params = array();
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

        return array('where' => $where, 'params' => $params);
    }
}
