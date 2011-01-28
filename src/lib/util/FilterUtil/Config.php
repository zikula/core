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
 * This is the configuration class for all FilterUtil classes.
 */
class FilterUtil_Config
{

    /**
     * Table mode.
     *
     * Enummeration what table type is used.
     */
    const TABLE_TABLES_MODE   = 1;
    const TABLE_DOCTRINE_MODE = 2;

    /**
     * Table name in tables.php.
     *
     * @var string
     */
    private $_dbtable;

    /**
     * Doctrine Table.
     *
     * @var Doctrine_Table
     */
    private $_doctrineTable;

    /**
     * Module's name.
     *
     * @var string
     */
    private $_module;

    /**
     * Table name.
     *
     * @var string
     */
    private $_table;

    /**
     * Root table alias to use.
     *
     * @var array
     */
    private $_alias;

    /**
     * Table columns.
     *
     * @var array
     */
    private $_column;

    /**
     * Join array for tables.php table.
     *
     * @var array
     */
    private $_join;

    /**
     * Doctrine Query object.
     *
     * @var Doctrine_Query
     */
    private $_doctrineQuery;

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
    public function __construct($args)
    {
        if (isset($args['module'])) {
            $this->setModule($args['module']);
        } else {
            return false;
        }

        if (isset($args['table'])) {
            if ($args['table'] instanceof Doctrine_Table) {
                $this->setDoctrineTable($args['table']);
            } else {
                $this->setTable($args['table']);
            }
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
    public function setModule($module)
    {
        if (ModUtil::available($module)) {
            $this->_module = $module;
            return true;
        } elseif (strtolower($module) == 'core') {
            $this->_module = 'core';
            return true;
        }

        return false;
    }

    /**
     * Gets Module.
     *
     * @return string Module name.
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * Sets table.
     *
     * @param string $table Table name.
     *
     * @return bool true on success, false otherwise.
     */
    public function setTable($table)
    {
        if (!$this->getModule()) {
            ModUtil::dbInfoLoad($this->getModule());
        }
        // tables.php support
        $tables = DBUtil::getTables();

        if (!isset($tables[$table]) || !isset($tables[$table . '_column'])) {
            return false;
        }

        $this->_dbtable = $table;
        $this->_table   = $tables[$table];
        $this->_column  = $tables[$table . '_column'];

        return true;
    }

    /**
     * Gets table.
     *
     * @return string Table name.
     */
    public function getTable()
    {
        return $this->_dbtable;
    }

    /**
     * Sets Doctrine table.
     *
     * @param Doctrine_Table $table Doctrine_Table object.
     *
     * @return bool true on success, false otherwise.
     */
    public function setDoctrineTable(Doctrine_Table $table)
    {
        $fields = $table->getFieldNames();

        $this->_doctrineTable = $table;
        $this->_table         = $table->getTableName();
        $this->_column        = array_combine($fields, $fields);

        return true;
    }

    /**
     * Gets Doctrine table.
     *
     * @return Doctrine_Table Doctrine_Table object.
     */
    public function getDoctrineTable()
    {
        return $this->_doctrineTable;
    }

    /**
     * Reset columns.
     *
     * @return boolean True.
     */
    public function resetColumns()
    {
        // check if we're using Doctrine
        if ($this->_doctrineTable instanceof Doctrine_Table) {

            $fields = $this->_doctrineTable->getFieldNames();

            $this->_column  = array_combine($fields, $fields);
        } else {
            // tables.php support
            $tables = DBUtil::getTables();

            if (!isset($tables[$this->dbtable]) || !isset($tables[$this->dbtable . '_column'])) {
                return false;
            }

            $this->_column  = $tables[$table . '_column'];
        }

        return true;
    }

    /**
     * Get columns.
     *
     * @return array Column set.
     */
    public function getColumns()
    {
        return $this->_column;
    }

    /**
     * Get single column.
     *
     * @param string $alias Alias.
     *
     * @return string Column name.
     */
    public function getColumn($alias)
    {
        if (isset($this->_column[$alias])) {
            return $this->_column[$alias];
        }

        return false;
    }

    /**
     * Sets alias.
     *
     * @param string $alias Table alias.
     *
     * @return void
     */
    public function setAlias($alias)
    {
        if (empty($alias)) {
            return;
        }

        $this->_alias = $alias;

        // reset column set.
        $this->resetColumns();

        // now add the alias to all fields
        foreach ($this->_column as &$a) {
            $a = $this->_alias . '.' . $a;
        }

        // finaly readd the joined tables.
        $this->addJoinToColumn();
    }

    /**
     * Gets alias.
     *
     * @return string Table alias.
     */
    public function getAlias()
    {
        return $this->_alias;
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
    public function setJoin(&$join)
    {
        $this->_join = & $join;
        $this->updateColumns();
    }

    /**
     * Gets join.
     *
     * Gets the reference to the given join array.
     *
     * @return array Join array
     */
    public function getJoin()
    {
        return $this->_join;
    }

    /**
     * Adds Join to columns.
     *
     * Edits the column array for use with a join array.
     * We must call it whenever we edited the join information!
     *
     * @return void
     */
    public function addJoinToColumn()
    {
        if (count($this->_join) <= 0) {
            return;
        }

        // reset columns
        $this->resetColumns();

        // now add the alias to all fields
        foreach ($this->_column as &$a) {
            $a = $this->_alias . '.' . $a;
        }

        $tables = DBUtil::getTables();

        // store the fixed aliases
        $aliases = array();
        foreach ($this->_join as $join) {
            if (isset($join['join_alias'])) {
                $aliases[] = $join['join_alias'];
            }
        }

        // add fields of all joins
        $alias = 'a';
        foreach ($this->_join as $join) {
            // check if the alias is ok
            if (!isset($join['join_alias'])) {
                if (in_array($alias, $aliases)) {
                    do {
                        $alias++;
                    } while (in_array($alias, $aliases));
                }
                $join['join_alias'] = $alias;
            }
            // process the fields
            $jc = isset($tables[$join['join_table'] . '_column']) ? $tables[$join['join_table'] . '_column'] : false;
            foreach ($join['join_field'] as $k => $f) {
                $a = $join['object_field_name'][$k];
                if (isset($this->_column[$a])) {
                    // Oh, that won't work! Two fields with the same alias!
                    return z_exit(__f('%s: Invalid join information!', 'FilterUtil'));
                }
                // so, let's add the field to the column array
                $this->_column[$a] = $join['join_alias'] . '.' . ($jc ? $jc[$f] : $f);
            }
            // now increase the alias ('a'++ = 'b')
            $alias++;
        }
    }

    /**
     * Set Doctrine Query.
     *
     * Set the Doctrine Query Object and expand configuration with it's information.
     *
     * @param Doctrine_Query $query Doctrine Query Object.
     *
     * @return void
     */
    public function setDoctrineQuery(Doctrine_Query $query)
    {
        $this->_doctrineQuery = $query;

        $this->resetColumns();

        $tables   = $this->_getTableInformation();
        $aliasMap = $this->_doctrineQuery->getTableAliasMap();

        $tables = $this->_enrichTablesWithAlias($tables, $aliasMap);

        $this->_setColumnsFromDoctrineTables($tables);
    }

    /**
     * Gets the Doctrine Query object.
     *
     * @return Doctrine_Query Doctrine Query Object.
     */
    public function getDoctrineQuery()
    {
        return $this->_doctrineQuery;
    }

    /**
     * Load the table objects from the "from" info.
     *
     * @return array Array of Doctrine_Table objects.
     */
    private function _getTableInformation()
    {
        $tables = array();
        foreach ($this->_doctrineQuery->getDQLPart('from') as $str) {
            $str = trim($str);
            $parts = explode('JOIN ', $str);

            $operator = false;

            switch (trim($parts[0])) {
                case 'INNER':
                    $operator = ':';
                case 'LEFT':
                    array_shift($parts);
                    break;
            }

            $last = '';

            foreach ($parts as $k => $part) {
                $part = trim($part);

                if (empty($part)) {
                    continue;
                }

                $e = explode(' ', $part);

                if (end($e) == 'INNER' || end($e) == 'LEFT') {
                    $last = array_pop($e);
                }
                $part = implode(' ', $e);

                foreach (explode(',', $part) as $reference) {
                    $reference = trim($reference);
                    $e = explode(' ', $reference);
                    $e2 = explode('.', $e[0]);

                    if ($operator) {
                        $e[0] = array_shift($e2) . $operator . implode('.', $e2);
                    }

                    $tables[] = $this->_doctrineQuery->load(implode(' ', $e));
                }

                $operator = ($last == 'INNER') ? ':' : '.';
            }
        }

        return $tables;
    }

    /**
     * Enrich doctrine tables with it's aliasses.
     *
     * @param array $tables   Array of doctrine table information.
     * @param array $aliasMap Array of aliasses.
     *
     * @return array Enriched array.
     */
    private function _enrichTablesWithAlias($tables, $aliasMap)
    {
        if (count($tables) == count($aliasMap)) {
            return array_combine($aliasMap, $tables);

        } elseif (count($tables) < count($aliasMap)) {
            $aliasses = array();
            foreach ($tables as $table) {
                foreach ($aliasMap as $alias) {
                    if ($this->_doctrineQuery->getQueryComponent($alias) == $table) {
                        $aliasses[] = $alias;
                    }
                }
            }
            return array_combine($aliasses, $tables);
        }

        return false;
    }

    /**
     * Set column array from Doctrine_Table object array.
     *
     * @param array $tables Array of Doctrine_Table objects.
     *
     * @return void
     */
    private function _setColumnsFromDoctrineTables($tables)
    {
        $this->_column = array();
        $aliases = array();
        foreach ($tables as $alias => $table) {
            $fields = $table['table']->getFieldNames();

            if (strpos($alias, '.') === false) {
                // add main table aliases only
                $aliases[] = "$alias.";
            }
            foreach ($fields as $field) {
                $key = $alias . '.' . $field;
                // strip the main table alias of the field key
                $key = str_replace($aliases, '', $key);

                $this->_column[$key] = $alias . '.' . $field;
            }
        }
    }

    /**
     * Get table mode
     *
     * Get whether we're using Doctrine or old tables.
     *
     * @return constant Identifier constant.
     */
    public function getTableMode()
    {
        if (isset($this->_doctrineTable) && $this->_doctrineTable instanceof Doctrine_Table) {
            return self::TABLE_DOCTRINE_MODE;
        } else {
            return self::TABLE_TABLES_MODE;
        }
    }
}
