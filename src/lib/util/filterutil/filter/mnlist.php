<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class FilterUtil_Filter_mnlist extends FilterUtil_PluginCommon implements FilterUtil_Build
{
    private $ops = array();
    private $fields = array();
    private $mnpntable = array();
    private $mntable = array();
    private $mncolumn = array();
    private $comparefield = array();

    /**
     * Constructor
     *
     * @access public
     * @param array $config Configuration
     * @param array $config[fields] array of name => array(field, table, comparefield)
     * @return object FilterUtil_Plugin_nmlist
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['fields']) && is_array($config['fields'])) {
            $this->addFields($config['fields']);
        }

        if (isset($config['ops']) && (!isset($this->ops) || !is_array($this->ops))) {
            $this->activateOperators($config['ops']);
        } else {
            $this->activateOperators($this->availableOperators());
        }
    }

    /**
     * Adds fields to list in common way
     *
     * @access public
     * @param mixed $fields Fields to add
     */
    public function addFields($fields)
    {
        foreach ($fields as $f => $r) {
            $this->fields[$f] = $r['field'];
            $this->setListTable($f, $r['table']);
            $this->setCompareField($f, $r['comparefield']);
        }
    }

    /**
     * Adds fields to list in common way
     *
     * @access public
     * @param mixed $op Operators to activate
     */
    public function activateOperators($op)
    {
        static $ops = array(
                        'eq',
                        'ne');
        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op) && array_search($op, $this->ops) === false && array_search($op, $ops) !== false) {
            $this->ops[] = $op;
        }
    }

    public function getFields()
    {
        return array_keys($this->fields);
    }

    /**
     * Get operators
     *
     * @access public
     * @return array Set of Operators and Arrays
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

    public function availableOperators()
    {
        return array('eq', 'ne');
    }

    /**
     * Set the n:m-Table
     *
     * @param string $table Table name
     */
    public function setListTable($name, $table)
    {
        $this->mnpntable[$name] = $table;
        $pntable = & pnDBGetTables();
        $this->mntable[$name] = $pntable[$table];
        $this->mncolumn[$name] = $pntable[$table . '_column'];
    }

    /**
     * Set the Compare field
     *
     * @param string $field Field name
     */
    public function setCompareField($name, $field)
    {
        if (isset($this->mncolumn[$name][$field]) && $this->fieldExists($field)) {
            $this->comparefield[$name] = $field;
        }
    }

    /**
     * return SQL code
     *
     * @access public
     * @param string $field Field name
     * @param string $op Operator
     * @param string $value Test value
     * @return string SQL code
     */
    public function getSQL($field, $op, $value)
    {
        if (!isset($this->fields[$field])) {
            return '';
        }
        $alias = 'plg' . $this->id . $field;
        switch ($op) {
            case 'ne':
                return array(
                                'where' => $value . ' NOT IN (SELECT ' . $this->mncolumn[$field][$this->fields[$field]] . ' FROM ' . $this->mntable[$field] . ' ' . $alias . ' WHERE ' . $this->column[$this->comparefield[$field]] . " = $alias." . $this->mncolumn[$field][$this->comparefield[$field]] . ')');
                break;
            case 'eq':
                return array(
                                'where' => $value . ' IN (SELECT ' . $this->mncolumn[$field][$this->fields[$field]] . ' FROM ' . $this->mntable[$field] . ' ' . $alias . ' WHERE ' . $this->column[$this->comparefield[$field]] . " = $alias." . $this->mncolumn[$field][$this->comparefield[$field]] . ')');
                break;
            default:
                return '';
        }
    }
}

