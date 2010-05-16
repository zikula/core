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


class FilterUtil_Common
{

    /**
     * Table name in pntable.php
     */
    protected static $pntable;

    /**
     * Module's name
     */
    protected static $module;

    /**
     * Table name
     */
    protected static $table;

    /**
     * Table columns
     */
    protected static $column;

    /**
     * Join array
     */
    protected static $join = array();

    /**
     * Constructor
     * Set parameters each Class could need
     *
     * @param string $args['table'] Tablename
     */
    public function __construct($args = array())
    {
        if (isset($args['module'])) {
            $this->setModule($args['module']);
        } else {
            return false;
        }

        if (isset($args['table'])) {
            $this->setTable($args['table']);
        } else {
            return false;
        }

        if (isset($args['join'])) {
            $this->setJoin($args['join']);
        }
    }

    /**
     * Set Module
     *
     * @access public
     * @param string $module Module name
     * @return bool true on success, false otherwise
     */
    protected function setModule($module)
    {
        if (pnModAvailable($module)) {
            pnModDBInfoLoad($module);
            $this->module = $module;
            return true;
        } elseif (strtolower($module) == 'core') {
            $this->module = 'core';
            return true;
        }
        return false;
    }

    /**
     * Set table
     *
     * @access public
     * @param string $table Table name
     * @return bool true on success, false otherwise
     */
    protected function setTable($table)
    {
        $pntable = & pnDBGetTables();

        if (!isset($pntable[$table]) || !isset($pntable[$table . '_column'])) {
            return false;
        }

        $this->pntable = $table;
        $this->table = $pntable[$table];
        $this->column = $pntable[$table . '_column'];

        return true;
    }

    /**
     * Set join
     *
     * Sets a reference to a join array for right column names
     *
     * @param array &$join Join array
     * @return void
     * @access public
     */
    protected function setJoin(&$join)
    {
        $this->join = & $join;
        $this->addJoinToColumn();
    }

    /**
     * Add Join to columns
     *
     * Edits the column array for use with a join array.
     * We must call it whenever we edited the join information!
     */
    protected function addJoinToColumn()
    {
        if (count($this->join) <= 0) {
            return;
        }

        $pntable = & pnDBGetTables();
        $c = & $this->column;
        // reset column array...
        $c = $pntable[$this->pntable . '_column'];
        // now add alias "tbl" to all fields
        foreach ($this->column as &$a) {
            $a = 'tbl.' . $a;
        }

        // add fields of all joins
        $alias = 'a';
        foreach ($this->join as &$join) {
            $jc = & $pntable[$join['join_table'] . '_column'];
            foreach ($join['join_field'] as $k => $f) {
                $a = $join['object_field_name'][$k];
                if (isset($c[$a])) {
                    //Oh, that won't work! Two fields with the same alias!
                    return pn_exit('Invalid join information!');
                }
                //so, let's add the field to the column array
                $c[$a] = $alias . '.' . $jc[$f];
            }
            //now increase the alias ('a'++ = 'b')
            $alias++;
        }
    }

    /**
     * Field exists?
     *
     * @access private
     * @param string $field Field name
     * @return bool true if the field exists, else if not
     */
    protected function fieldExists($field)
    {
        if (!isset($this->column[$field]) || empty($this->column[$field])) {
            return false;
        }

        return true;
    }

    /**
     * Add common config variables to config array
     *
     * @access protected
     * @param array $config Config array
     * @return array Config array including common config
     */
    protected function addCommon(&$config)
    {
        $config['table'] = $this->pntable;
        $config['module'] = $this->module;
        $config['join'] = & $this->join;
    }
}