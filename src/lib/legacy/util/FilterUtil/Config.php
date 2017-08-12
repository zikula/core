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
 * This is the configuration class for all FilterUtil classes.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
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
     * @param array $args Arguments as listed above
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
            $this->join = [];
        }
    }

    /**
     * Sets Module.
     *
     * @param string $module Module name
     *
     * @return bool true on success, false otherwise
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
     * @return string Module name
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * Sets table.
     *
     * @param string $table Table name
     *
     * @return bool true on success, false otherwise
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
     * @return string Table name
     */
    public function getTable()
    {
        return $this->_dbtable;
    }

    /**
     * Sets Doctrine table.
     *
     * @param Doctrine_Table $table Doctrine_Table object
     *
     * @return bool true on success, false otherwise
     */
    public function setDoctrineTable(Doctrine_Table $table)
    {
        $this->_doctrineTable = $table;
        $this->_table         = $table->getTableName();

        $this->_column = $this->getDoctrineTableColumns($table);

        return true;
    }

    /**
     * Gets the columns of a Doctrine table.
     *
     * @param Doctrine_Table $table   Doctrine_Table object
     * @param array          $dynaMap Array of dynamic aliasses
     *
     * @return array Columns of the table with keys as alias and values as columns
     */
    public function getDoctrineTableColumns(Doctrine_Table $table, $dynaMap = null)
    {
        // check if the table has a custom method to retrieve the filter columns
        if (method_exists($table, 'getFilterColumns')) {
            $columns = $table->getFilterColumns($dynaMap);
        } else {
            $fields = $table->getFieldNames();
            $columns = array_combine($fields, $fields);
        }

        return $columns;
    }

    /**
     * Gets Doctrine table.
     *
     * @return Doctrine_Table Doctrine_Table object
     */
    public function getDoctrineTable()
    {
        return $this->_doctrineTable;
    }

    /**
     * Reset columns.
     *
     * @return boolean True
     */
    public function resetColumns()
    {
        // check if we're using Doctrine
        if ($this->_doctrineTable instanceof Doctrine_Table) {
            $this->setDoctrineTable($this->_doctrineTable);
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
     * @return array Column set
     */
    public function getColumns()
    {
        return $this->_column;
    }

    /**
     * Get single column.
     *
     * @param string $alias Alias
     *
     * @return string Column name
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
     * @param string $alias Table alias
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
     * @return string Table alias
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
     * @param array &$join Join array
     *
     * @return void
     */
    public function setJoin(&$join)
    {
        $this->_join = &$join;
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
        $aliases = [];
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
                    throw new \Exception(__f('%s: Invalid join information!', 'FilterUtil'));
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
     * @param Doctrine_Query $query  Doctrine Query Object
     * @param array          $filter Filter object to be processed to enrich the Query
     *
     * @return void
     */
    public function setDoctrineQuery(Doctrine_Query $query, $filter)
    {
        $this->resetColumns();

        $this->_doctrineQuery = $query;

        $tables   = $this->_getTableInformation();
        $aliasMap = $this->_doctrineQuery->getTableAliasMap();

        $tables = $this->_enrichTablesWithAlias($tables, $aliasMap);

        // FIXME assumes any dynamic relation with first table found on FROM
        $from = $this->_doctrineQuery->getDQLPart('from');
        $main = explode(' ', $from[0]);
        $main = $main[1];

        // adds the external relation aliases
        // depending on the filter object to process
        $joins = [];
        $this->_getFilterRelations($joins, $filter);

        // stores the tables => dynaalias
        $t = [];
        $a = 'dynajoin1';
        foreach ($joins as $join) {
            $alias = substr($join, 0, strpos($join, ':'));
            $t[$alias] = $a;
            // add the dynamic left join
            $this->_doctrineQuery->leftJoin("{$main}.$alias $a");
            $a++;
        }

        $this->_setColumnsFromDoctrineTables($tables, $t);
    }

    /**
     * Detect the join fields specified in the filter obj.
     *
     * Defined to be a join with an ExternalTable:field.
     *
     * @param array &$joins Empty array to store the result in
     * @param array $obj Filter object to be processed to enrich the Query
     *
     * @return array Columns defined as join with an external table
     */
    private function _getFilterRelations(&$joins, $obj)
    {
        if (!is_array($obj) || count($obj) == 0) {
            return [];
        }

        if (isset($obj['field']) && !empty($obj['field'])) {
            if (isset($this->_column[$obj['field']])) {
                $column = $this->_column[$obj['field']];
                if (strpos($column, ':')) {
                    $joins[$obj['field']] = $column;
                }
            }

            return;
        } else {
            if (isset($obj[0]) && is_array($obj[0])) {
                $this->_getFilterRelations($joins, $obj[0]);
                unset($obj[0]);
            }
            foreach ($obj as $op => $tmp) {
                $op = strtoupper(substr($op, 0, 3)) == 'AND' ? 'AND' : 'OR';
                if (strtoupper($op) == 'AND' || strtoupper($op) == 'OR') {
                    $j = $this->_getFilterRelations($joins, $tmp);
                }
            }
        }

        return $joins;
    }

    /**
     * Gets the Doctrine Query object.
     *
     * @return Doctrine_Query Doctrine Query Object
     */
    public function getDoctrineQuery()
    {
        return $this->_doctrineQuery;
    }

    /**
     * Load the table objects from the "from" info.
     *
     * @return array Array of Doctrine_Table objects
     */
    private function _getTableInformation()
    {
        $tables = [];
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
     * @param array $tables   Array of doctrine table information
     * @param array $aliasMap Array of aliasses
     *
     * @return array Enriched array
     */
    private function _enrichTablesWithAlias($tables, $aliasMap)
    {
        if (count($tables) == count($aliasMap)) {
            return array_combine($aliasMap, $tables);
        } elseif (count($tables) < count($aliasMap)) {
            $aliasses = [];
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
     * @param array $tables  Array of Doctrine_Table objects
     * @param array $dynaMap Array of dynamic aliasses
     *
     * @return void
     */
    private function _setColumnsFromDoctrineTables($tables, $dynaMap)
    {
        $this->_column = [];

        $aliasMap  = $this->_doctrineQuery->getTableAliasMap();

        $aliases = [];
        foreach ($tables as $alias => $table) {
            $columns = $this->getDoctrineTableColumns($table['table'], $dynaMap);

            if (strpos($alias, '.') === false) {
                // add main table aliases only
                $aliases[] = "$alias.";
            }

            foreach ($columns as $a => $c) {
                $key = $alias . '.' . $a;
                // strip the main table alias of the field key
                $key = str_replace($aliases, '', $key);

                $this->_column[$key] = strpos($c, '.') ? $c : $alias . '.' . $c;
            }
        }
    }

    /**
     * Get table mode
     *
     * Get whether we're using Doctrine or old tables.
     *
     * @return constant Identifier constant
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
