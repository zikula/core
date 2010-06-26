<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This is the base class for all FilterUtil classes.
 */
class FilterUtil_Common
{
    /**
     * Table name in pntable.php.
     *
     * @var string
     */
    protected static $dbtable;

    /**
     * Module's name.
     *
     * @var string
     */
    protected static $module;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $table;

    /**
     * Table columns.
     *
     * @var array
     */
    protected static $column;

    /**
     * Join array.
     *
     * @var array
     */
    protected static $join = array();

    /**
     * Constructor.
     *
     * Sets parameters each Class could need.
     * Array $args must hold:
     *   module: The module name.
     *   table: The table name.
     * It also may contain:
     *   join: The join array.
     *
     * @param array $args Arguments as listed above.
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
     * Sets Module.
     *
     * @param string $module Module name.
     *
     * @return bool true on success, false otherwise.
     */
    protected function setModule($module)
    {
        if (ModUtil::available($module)) {
            ModUtil::dbInfoLoad($module);
            $this->module = $module;
            return true;
        } elseif (strtolower($module) == 'core') {
            $this->module = 'core';
            return true;
        }

        return false;
    }

    /**
     * Sets table.
     *
     * @param string $table Table name.
     *
     * @return bool true on success, false otherwise.
     */
    protected function setTable($table)
    {
        $tables = DBUtil::getTables();

        if (!isset($tables[$table]) || !isset($tables[$table . '_column'])) {
            return false;
        }

        $this->pntable = $table;
        $this->table   = $tables[$table];
        $this->column  = $tables[$table . '_column'];

        return true;
    }

    /**
     * Sets join.
     *
     * Sets a reference to a join array for right column names.
     *
     * @param array &$join Join array.
     *
     * @return void
     */
    protected function setJoin(&$join)
    {
        $this->join = & $join;
        $this->addJoinToColumn();
    }

    /**
     * Adds Join to columns.
     *
     * Edits the column array for use with a join array.
     * We must call it whenever we edited the join information!
     *
     * @return void
     */
    protected function addJoinToColumn()
    {
        if (count($this->join) <= 0) {
            return;
        }

        $tables = DBUtil::getTables();
        $c = & $this->column;
        // reset column array...
        $c = $tables[$this->pntable . '_column'];
        // now add alias "tbl" to all fields
        foreach ($this->column as &$a) {
            $a = 'tbl.' . $a;
        }

        // add fields of all joins
        $alias = 'a';
        foreach ($this->join as &$join) {
            $jc = & $tables[$join['join_table'] . '_column'];
            foreach ($join['join_field'] as $k => $f) {
                $a = $join['object_field_name'][$k];
                if (isset($c[$a])) {
                    // Oh, that won't work! Two fields with the same alias!
                    return z_exit('Invalid join information!');
                }
                // so, let's add the field to the column array
                $c[$a] = $alias . '.' . $jc[$f];
            }
            // now increase the alias ('a'++ = 'b')
            $alias++;
        }
    }

    /**
     * Field exists?
     *
     * @param string $field Field name.
     *
     * @return bool true if the field exists, else if not.
     */
    protected function fieldExists($field)
    {
        if (!isset($this->column[$field]) || empty($this->column[$field])) {
            return false;
        }

        return true;
    }

    /**
     * Adds common config variables to config array.
     *
     * @param array &$config Config array.
     *
     * @return void
     */
    protected function addCommon(&$config)
    {
        $config['table']  = $this->pntable;
        $config['module'] = $this->module;
        $config['join']   = & $this->join;
    }
}
