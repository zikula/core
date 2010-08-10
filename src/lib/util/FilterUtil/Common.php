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
     * Table name in tables.php.
     *
     * @var string
     */
    protected $dbtable;

    /**
     * Module's name.
     *
     * @var string
     */
    protected $module;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table;

    /**
     * Table alias to use.
     *
     * @var array
     */
    protected $alias;

    /**
     * Table columns.
     *
     * @var array
     */
    protected $column;

    /**
     * Join array.
     *
     * @var array
     */
    protected $join;

    /**
     * Constructor.
     *
     * Sets parameters each Class could need.
     * Array $args must hold:
     *   module: The module name.
     *   table: The table name or Doctrine_Record class name.
     * It also may contain:
     *   join: The join array.
     *   alias: Alias to use with the main table.
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

        if (isset($args['alias'])) {
            $this->setAlias($args['alias']);
        } else {
            $this->alias = 'tbl';
        }

        if (isset($args['join'])) {
            $this->setJoin($args['join']);
        } else {
            $this->join = array();
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
     * @param string $table Table or Doctrine_Record class name.
     *
     * @return bool true on success, false otherwise.
     */
    protected function setTable($table)
    {
        // check if it's a Doctrine_Record class name
        if (class_exists($table)) {
            $tableobj = Doctrine_Core::getTable($table);

            if (!$tableobj) {
                return false;
            }

            $fields = $tableobj->getFieldNames();

            $this->dbtable = $table;
            $this->table   = $tableobj->getTableName();
            $this->column  = array_combine($fields, $fields);
        } else {
            // tables.php support
            $tables = DBUtil::getTables();

            if (!isset($tables[$table]) || !isset($tables[$table . '_column'])) {
                return false;
            }

            $this->dbtable = $table;
            $this->table   = $tables[$table];
            $this->column  = $tables[$table . '_column'];
        }

        return true;
    }

    /**
     * Reset columns.
     *
     * @return void
     */
    protected function resetColumns()
    {
        // check if it's a Doctrine_Record class name
        if (class_exists($this->dbtable, false)) {
            $tableobj = Doctrine_Core::getTable($this->dbtable);

            $fields = $tableobj->getFieldNames();

            $this->column  = array_combine($fields, $fields);
        } else {
            // tables.php support
            $tables = DBUtil::getTables();

            if (!isset($tables[$this->dbtable]) || !isset($tables[$this->dbtable . '_column'])) {
                return false;
            }

            $this->column  = $tables[$table . '_column'];
        }

        return true;
    }

    /**
     * Sets alias.
     *
     * @param string $alias Table alias.
     *
     * @return void
     */
    protected function setAlias($alias)
    {
        if (empty($alias)) {
            return;
        }

        $this->alias = $alias;

        // now add the alias to all fields
        foreach ($this->column as &$a) {
            $a = $this->alias . '.' . $a;
        }
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

        // reset columns
        $this->resetColumns();
        // now add the alias to all fields
        foreach ($this->column as &$a) {
            $a = $this->alias . '.' . $a;
        }

        $tables = DBUtil::getTables();

        // store the fixed aliases
        $aliases = array();
        foreach ($this->join as $join) {
            if (isset($join['join_alias'])) {
                $aliases[] = $join['join_alias'];
            }
        }

        // add fields of all joins
        $alias = 'a';
        foreach ($this->join as $join) {
            // check if the alias is ok
            if (!isset($join['join_alias'])) {
                if (in_array($alias, $aliases)) {
                    do {
                        $alias++;
                    } while(in_array($alias, $aliases));
                }
                $join['join_alias'] = $alias;
            }
            // process the fields
            $jc = isset($tables[$join['join_table'] . '_column']) ? $tables[$join['join_table'] . '_column'] : false;
            foreach ($join['join_field'] as $k => $f) {
                $a = $join['object_field_name'][$k];
                if (isset($this->column[$a])) {
                    // Oh, that won't work! Two fields with the same alias!
                    return z_exit(__f('%s: Invalid join information!', 'FilterUtil'));
                }
                // so, let's add the field to the column array
                $this->column[$a] = $join['join_alias'] . '.' . ($jc ? $jc[$f] : $f);
            }
            // now increase the alias ('a'++ = 'b')
            $alias++;
        }
    }

    /**
     * Field exists checker.
     *
     * @param string $field Field name.
     *
     * @return bool True if the field exists, false if not.
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
        $config['table']  = $this->dbtable;
        $config['alias']  = $this->alias;
        $config['module'] = $this->module;
        $config['join']   = & $this->join;
    }
}
