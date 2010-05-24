<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


class FilterUtil_Filter_default extends FilterUtil_PluginCommon implements FilterUtil_Build
{
    private $ops = array();
    private $fields = array();

    /**
     * Constructor
     *
     * @access public
     * @param array $config Configuration
     * @return object FilterUtil_Plugin_Default
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['fields']) && (!isset($this->fields) || !is_array($this->fields))) {
            $this->addFields($config['fields']);
        }

        if (isset($config['ops']) && (!isset($this->ops) || !is_array($this->ops))) {
            $this->activateOperators($config['ops']);
        } else {
            $this->activateOperators(array(
                            'eq',
                            'ne',
                            'lt',
                            'le',
                            'gt',
                            'ge',
                            'like',
                            'likefirst',
                            'null',
                            'notnull'));
        }

        if (isset($config['default']) && $config['default'] == true || count($this->fields) <= 0) {
            $this->default = true;
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
                        'ne',
                        'lt',
                        'le',
                        'gt',
                        'ge',
                        'like',
                        'likefirst',
                        'null',
                        'notnull');
        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op) && array_search($op, $this->ops) === false && array_search($op, $ops) !== false) {
            $this->ops[] = $op;
        }
    }

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

    public function getFields()
    {
        return $this->fields;
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
        if (!$this->fieldExists($field)) {
            return '';
        }
        switch ($op) {
            case 'ne':
                return array(
                                'where' => $this->column[$field] . " <> '" . $value . "'");
                break;
            case 'lt':
                return array(
                                'where' => $this->column[$field] . " < '" . $value . "'");
                break;
            case 'le':
                return array(
                                'where' => $this->column[$field] . " <= '" . $value . "'");
                break;
            case 'gt':
                return array(
                                'where' => $this->column[$field] . " > '" . $value . "'");
                break;
            case 'ge':
                return array(
                                'where' => $this->column[$field] . " >= '" . $value . "'");
                break;
            case 'like':
                return array(
                                'where' => $this->column[$field] . " like '" . $value . "'");
                break;
            case 'likefirst':
                return array(
                                'where' => $this->column[$field] . " like '" . $value . "%'");
                break;
            case 'null':
                return array(
                                'where' => $this->column[$field] . " = '' OR " . $this->column[$field] . " IS NULL");
                break;
            case 'notnull':
                return array(
                                'where' => $this->column[$field] . " <> '' AND " . $this->column[$field] . " IS NOT NULL");
                break;
            case 'eq':
                return array(
                                'where' => $this->column[$field] . " = '" . $value . "'");
                break;
            default:
                return '';
        }
    }
}

