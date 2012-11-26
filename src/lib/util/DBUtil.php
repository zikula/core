<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * DBUtil is the database abstraction class of Zikula.
 */
class DBUtil
{
    /**
     * Cache enabled.
     *
     * @var boolean
     */
    protected static $cache_enabled;

    /**
     * Constructor of DBUtil class.
     */
    private function __construct()
    {

    }

    /**
     * Check whether the object cache should be used for a specific query/operation.
     *
     * @param string $tablename The Zikula tablename.
     *
     * @return true/false
     */
    public static function hasObjectCache($tablename)
    {
        if (!self::$cache_enabled) {
            self::$cache_enabled = ServiceUtil::getManager()->getArgument('dbcache.enable');
        }

        return ($tablename != 'session_info' && !System::isInstalling() && self::$cache_enabled);
    }

    /**
     * Get the cache.
     *
     * @param string $table Table name.
     * @param string $key   Key choise.
     *
     * @return mixed Return the cache.
     */
    public static function getCache($table, $key)
    {
        if (self::hasObjectCache($table)) {
            $key = md5($key);
            $databases = ServiceUtil::getManager()->getArgument('databases');
            $connName = Doctrine_Manager::getInstance()->getCurrentConnection()->getName();
            $prefix = md5(serialize($databases[$connName]));
            $cacheDriver = ServiceUtil::getManager()->getService('doctrine.cachedriver');

            return $cacheDriver->fetch($prefix . $table . $key);
        }

        return false;
    }

    /**
     * Set the cache.
     *
     * @param string $table  Table name.
     * @param string $key    Key choise.
     * @param string $fields Fields to cache.
     *
     * @return void
     */
    public static function setCache($table, $key, $fields)
    {
        if (self::hasObjectCache($table)) {
            $key = md5($key);
            $databases = ServiceUtil::getManager()->getArgument('databases');
            $connName = Doctrine_Manager::getInstance()->getCurrentConnection()->getName();
            $prefix = md5(serialize($databases[$connName]));
            $cacheDriver = ServiceUtil::getManager()->getService('doctrine.cachedriver');
            $cacheDriver->save($prefix . $table . $key, $fields);
        }
    }

    /**
     * Flush the cache.
     *
     * @param string $table Table name.
     *
     * @return void
     */
    public static function flushCache($table)
    {
        if (self::hasObjectCache($table)) {
            $databases = ServiceUtil::getManager()->getArgument('databases');
            $connName = Doctrine_Manager::getInstance()->getCurrentConnection()->getName();
            $prefix = md5(serialize($databases[$connName]));
            $cacheDriver = ServiceUtil::getManager()->getService('doctrine.cachedriver');
            $cacheDriver->deleteByPrefix($prefix . $table);
        }
    }

    /**
     * Return server information.
     *
     * @return array Array of server info.
     */
    public static function serverInfo()
    {
        $connection = Doctrine_Manager::getInstance()->getCurrentConnection();

        // we will form an array to keep formally compatible to the old ado-db style for now
        return array('description' => $connection->getAttribute(PDO::ATTR_SERVER_INFO),
                'version' => $connection->getAttribute(PDO::ATTR_CLIENT_VERSION));
    }

    /**
     * Create database.
     *
     * @param string  $dbname       The database name.
     * @param boolean $optionsarray The options array.
     *
     * @return boolean
     * @throws Exception If the dbname is empty.
     */
    public static function createDatabase($dbname, $optionsarray = false)
    {
        if (empty($dbname)) {
            throw new Exception(__f('The parameter %s must not be empty', 'dbname'));
        }

        $connection = Doctrine_Manager::getInstance()->getCurrentConnection();

        try {
            // create the new database
            // TODO C [use $optionsarray in DBUtil::createDatabase() for backwards compatability] (Guite)
            $connection->export->createDatabase($dbname);

            return true;
        } catch (Exception $e) {
            echo 'Database error: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * Get a list of databases available on the server.
     *
     * @return array Array of databases.
     */
    public static function metaDatabases()
    {
        return Doctrine_Manager::getInstance()->getCurrentConnection()->import->listDatabases();
    }

    /**
     * Get a list of tables in the currently connected database.
     *
     * @param boolean $ttype      Type of 'tables' to get.
     * @param boolean $showSchema Add the schema name to the table.
     * @param boolean $mask       Mask to apply to return result set.
     *
     * @return array Array of tables.
     */
    public static function metaTables($ttype = false, $showSchema = false, $mask = false)
    {
        return Doctrine_Manager::getInstance()->getCurrentConnection()->import->listTables();
    }

    /**
     * Get a list of database tables.
     *
     * @return array array of database tables
     */
    public static function getTables()
    {
        return ServiceUtil::getManager()->getArgument('dbtables');
    }

    /**
     * Get a list of default dbms specific table options.
     *
     * This allows the default table options to be set in a modules's tables.php without
     * causing a circular dependency.
     *
     * @return array Return the default table options.
     */
    public static function getDefaultTableOptions()
    {
        $tableoptions = array();
        $serviceManager = ServiceUtil::getManager();

        $databases = $serviceManager['databases'];
        $connName = Doctrine_Manager::getInstance()->getCurrentConnection()->getName();
        $dbDriverName = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());
        if ($dbDriverName == 'mysql') {
            $tableoptions['type'] = Doctrine_Manager::getInstance()->getCurrentConnection()->getAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_TYPE);
        }

        $tableoptions['charset'] = $databases[$connName]['charset'];
        $tableoptions['collate'] = $databases[$connName]['collate'];

        return $tableoptions;
    }

    /**
     * Get a list of dbms specific table options.
     *
     * For use by ADODB's data dictionary
     * Additional database specific settings can be defined here
     * See ADODB's data dictionary docs for full details.
     *
     * @param string $table Optional, string with table name.
     * If $table param is set and there is a set of options configured
     * for this table via tables.php then we return these options,
     * the default options are returned otherwise.
     *
     * @return array Return the table options.
     */
    public static function getTableOptions($table = '')
    {
        if ($table != '') {
            $tables = self::getTables();
            if (isset($tables[$table . '_def'])) {
                return $tables[$table . '_def'];
            }
        }

        return self::getDefaultTableOptions();
    }

    /**
     * Execute SQL, check for errors and return result. Uses Doctrine's DBAL to generate DB-portable paging code.
     *
     * @param string  $sql          The SQL statement to execute.
     * @param integer $limitOffset  The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows The upper limit bound (optional) (default=-1).
     * @param boolean $exitOnError  Whether to exit on error (default=true) (optional).
     * @param boolean $verbose      Whether to be verbose (default=true) (optional).
     *
     * @return mixed     The result set of the successfully executed query or false on error.
     * @throws Exception No SQL statment.
     */
    public static function executeSQL($sql, $limitOffset = -1, $limitNumRows = -1, $exitOnError = true, $verbose = true)
    {
        if (!$sql) {
            throw new Exception(__('No SQL statement to execute'));
        }

        $connection = Doctrine_Manager::getInstance()->getCurrentConnection();

        if (!$connection && System::isInstalling()) {
            return false;
        }

        try {
            if ($limitNumRows > 0) {
                $tStr = strtoupper(substr(trim($sql), 0, 6));
                if ($tStr !== 'SELECT') {
                    // TODO D [use normal Select instead of showing an error message if paging is desired for something different than SELECTs] (Guite)
                    throw new Exception(__('Paging parameters can only be used for SELECT statements'));
                }

                if ($limitOffset > 0) {
                    $sql = $connection->modifyLimitQuery($sql, $limitNumRows, $limitOffset);
                } else {
                    $sql = $connection->modifyLimitQuery($sql, $limitNumRows);
                }
            }

            $stmt = $connection->prepare($sql);
            //$stmt->setHydrationMode(Doctrine::HYDRATE_RECORD);
            if ($stmt->execute()) {
                $result = $stmt;
            }

            if ($result) {
                if (System::isLegacyMode()) {
                    return new Zikula_Adapter_AdodbStatement($result);
                } else {
                    return $result;
                }
            }
        } catch (Exception $e) {
            echo 'Error in DBUtil::executeSQL: ' . $sql . '<br />' . $e->getMessage() . '<br />';
            if ((System::isDevelopmentMode() && SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN))) {
                echo nl2br($e->getTraceAsString());
            }
            System::shutDown();
        }

        return false;
    }

    /**
     * Same as Api function but without AS aliasing.
     *
     * @param string $table       The treated table reference.
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null).
     *
     * @return string    The generated sql string.
     * @throws Exception If invalid table key retreived or empty query generated.
     */
    public static function _getAllColumns($table, $columnArray = null)
    {
        $tables = self::getTables();
        $columns = $tables["{$table}_column"];
        if (!$columns) {
            throw new Exception(__f('Invalid table-key [%s] retrieved', $table));
        }

        $queriesResult = array();
        foreach ($columns as $key => $val) {
            if (!$columnArray || in_array($key, $columnArray)) {
                $queriesResult[] = $val . ' AS "' . $key . '"';
            }
        }

        if (!$queriesResult && $columnArray) {
            throw new Exception(__f('Empty query generated for [%s] filtered by columnArray', $table));
        }

        return implode(',', $queriesResult);
    }

    /**
     * Same as Api function but returns fully qualified fieldnames.
     *
     * @param string $table       The treated table reference.
     * @param string $tablealias  The SQL table alias to use in the SQL statement.
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null).
     *
     * @return The       generated sql string
     * @throws Exception If invalid table key retreived or empty query generated.
     */
    public static function _getAllColumnsQualified($table, $tablealias, $columnArray = null)
    {
        $search = array('+', '-', '*', '/', '%');
        $replace = array('');

        $tables = self::getTables();
        $columns = $tables["{$table}_column"];
        if (!$columns) {
            throw new Exception(__f('Invalid table-key [%s] retrieved', $table));
        }

        foreach ($columns as $key => $val) {
            if (!$columnArray || in_array($key, $columnArray)) {
                $hasMath = (bool)(strcmp($val, str_replace($search, $replace, $val)));
                if (!$hasMath) {
                    $queriesResult[] = $tablealias . '.' . $val . ' AS "' . $key . '"';
                } else {
                    $queriesResult[] = $val . ' AS "' . $key . '"';
                }
            }
        }

        if (!$queriesResult && $columnArray) {
            throw new Exception(__f('Empty query generated for [%s] filtered by columnArray', $table));
        }

        return implode(',', $queriesResult);
    }

    /**
     * Return the column array for the given table.
     *
     * @param string $table       The treated table reference.
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null).
     *
     * @return The       column array for the given table.
     * @throws Exception If empty query generated.
     */
    public static function getColumnsArray($table, $columnArray = null)
    {
        $columnArrayResult = array();

        $tables = self::getTables();
        $tkey = $table . '_column';
        if (!isset($tables[$tkey])) {
            return $columnArrayResult;
        }

        $cols = $tables[$tkey];

        foreach ($cols as $key => $val) {
            // since the key is plain name, we take it rather
            // than the value to construct object fields from
            if (!$columnArray || in_array($key, $columnArray)) {
                $columnArrayResult[] = $key;
            }
        }

        if (!$columnArrayResult && $columnArray) {
            throw new Exception(__f('Empty query generated for [%s] filtered by columnArray', $table));
        }

        return $columnArrayResult;
    }

    /**
     * Expand column array with JOIN-Fields.
     *
     * This adds all joined fields to the column array by their alias defined in $joinInfo.
     * Also it adds the field's table alias to avoid ambiguous queries.
     *
     * @param array $columns  Column array.
     * @param array $joinInfo JoinInfo array.
     *
     * @return array Expanded column array.
     * @deprecated
     * @see    Doctrine_Record
     * @throws Exception If invalid join information retrieved (an alias already exists).
     */
    public static function expandColumnsWithJoinInfo($columns, $joinInfo)
    {
        if (count($joinInfo) <= 0) {
            return $columns;
        }

        // now add alias "tbl" to all fields
        foreach ($columns as &$a) {
            $a = 'tbl.' . $a;
        }

        $tables = self::getTables();
        // add fields of all joins
        $alias = 'a';
        foreach ($joinInfo as &$join) {
            $jc = & $tables[$join['join_table'] . '_column'];
            foreach ($join['join_field'] as $k => $f) {
                $a = $join['object_field_name'][$k];
                if (isset($columns[$a])) {
                    //Oh, that won't work! Two fields with the same alias!
                    throw new Exception(__f('Invalid join information retrieved (alias %s already exists)', $a));
                }
                //so, let's add the field to the column array
                $columns[$a] = $alias . '.' . $jc[$f];
            }
            //now increase the alias ('a'++ = 'b')
            $alias++;
        }

        return $columns;
    }

    /**
     * Rename column(s) in a table.
     *
     * @param string $table      The treated table reference.
     * @param string $oldcolumn  The existing name of the column (full database name of column).
     * @param string $newcolumn  The new name of the column from the pntables array.
     * @param string $definition Field specific options (optional) (default=null).
     *
     * @return boolean
     * @throws Exception If parameters are empty.
     */
    public static function renameColumn($table, $oldcolumn, $newcolumn, $definition = null)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }
        if (empty($oldcolumn)) {
            throw new Exception(__f('The parameter %s must not be empty', 'oldcolumn'));
        }
        if (empty($newcolumn)) {
            throw new Exception(__f('The parameter %s must not be empty', 'newcolumn'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        $oldcolumn = isset($tables["{$table}_column"][$oldcolumn]) ? $tables["{$table}_column"][$oldcolumn] : $oldcolumn;
        $newcolumn = $tables["{$table}_column"][$newcolumn];

        if (empty($definition) || !is_array($definition)) {
            $definition = self::getTableDefinition($table);
            $definition = $definition[$newcolumn];
            if (!$definition) {
                throw new Exception(__f('Neither the sql parameter nor the table array contain the dictionary representation of table [%s]', array($table)));
            }
        }

        $renameColumnArray = array($oldcolumn => array(
                        'name' => $newcolumn,
                        'definition' => $definition));
        try {
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->alterTable($tableName, array('rename' => $renameColumnArray));
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Column rename failed.') . ' ' . $e->getMessage());
        }
        self::flushCache($table);

        return true;
    }

    /**
     * Add column(s) to a table.
     *
     * @param string $table  The treated table reference.
     * @param array  $fields Fields to add from the table.
     *
     * @return boolean
     * @throws Exception                If parameters are empty.
     * @throws InvalidArgumentException If field does not exist in table definition.
     */
    public static function addColumn($table, array $fields)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        if (!$fields) {
            throw new Exception(__f('The parameter %s must not be empty', 'fields'));
        }

        if (!is_array($fields[0])) {
            throw new Exception(__f('The parameter %s must be an array of field arrays', 'fields'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        try {
            $connection = Doctrine_Manager::getInstance()->getCurrentConnection();
            foreach ($fields as $field) {
                $options = self::getTableOptions($table);
                $definition = self::getTableDefinition($table);
                if (!isset($definition[$field[0]])) {
                    throw new InvalidArgumentException(__f('%1$s does not exist in table definition for %2$s.', array($field[0], $table)));
                }
                $def = $definition[$field[0]];

                $connection->export->alterTable($tableName, array('add' => array($field[0] => $def)));
            }
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Column creation failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);

        return true;
    }

    /**
     * Drop column(s) from a table.
     *
     * @param string $table  The treated table reference.
     * @param array  $fields Fields to drop from the table.
     *
     * @return boolean
     * @throws Exception If parameters are empty.
     */
    public static function dropColumn($table, $fields)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        if (!$fields) {
            throw new Exception(__f('The parameter %s must not be empty', 'fields'));
        }

        if (!is_string($fields) && !is_array($fields)) {
            throw new Exception(__f('The parameter %s must be an array.', 'fields'));
        }

        $fields = (array)$fields;
        $arrayFields = array();
        foreach ($fields as $field) {
            $arrayFields[$field] = array();
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        try {
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->alterTable($tableName, array('remove' => $arrayFields));
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Column deletion failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);

        return true;
    }

    /**
     * Format value for use in SQL statement.
     *
     * Special handling for integers and booleans (the last is required for MySQL 5 strict mode).
     *
     * @param intiger|boolean $value The value to format.
     *
     * @return string string ready to add to SQL statement.
     */
    public static function _formatForStore($value)
    {
        if (is_int($value)) {
            // No need to DataUtil::formatForStore when casted to int
            return (int)$value;
            // Avoid SQL strict problems where false would be stored as ''
        } elseif ($value === false) {
            return 0;
        } elseif ($value === true) {
            return 1;
        }

        return '\'' . DataUtil::formatForStore((string)$value) . '\'';
    }

    /**
     * Generate and execute an insert SQL statement for the given object.
     *
     * @param array   &$object  The object we wish to insert.
     * @param string  $table    The treated table reference.
     * @param string  $idfield  The column which stores the primary key (optional) (default='id').
     * @param boolean $preserve Whether or not to preserve existing/set standard fields (optional) (default=false).
     * @param boolean $force    Whether or not to insert empty values as NULL (optional) (default=false).
     *
     * @return The result set from the update operation. The object is updated with the newly generated ID.
     * @deprecated
     * @see    Doctrine_Record::save()
     * @deprecated
     * @see    Doctrine_Table
     * @throws Exception If column or column_def is not an array or cant find anything to insert into object.
     */
    public static function insertObject(array &$object, $table, $idfield = 'id', $preserve = false, $force = false)
    {
        $tables = self::getTables();
        $tableName = $tables[$table];

        $sql = "INSERT INTO $tableName ";

        // set standard architecture fields
        ObjectUtil::setStandardFieldsOnObjectCreate($object, $preserve, $idfield);

        // build the column list
        $columnList = $tables["{$table}_column"];
        if (!is_array($columnList)) {
            throw new Exception(__f('%s_column is not an array', $table));
        }

        // build the column definition list
        $columnDefList = $tables["{$table}_column_def"];
        if (!is_array($columnDefList)) {
            throw new Exception(__f('%s_column_def is not an array', $table));
        }

        // grab each key and value and append to the sql string
        $search = array('+', '-', '*', '/', '%');
        $replace = array('');
        $cArray = array();
        $vArray = array();

        $dbDriverName = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());
        foreach ($columnList as $key => $val) {
            $hasMath = (bool)(strcmp($val, str_replace($search, $replace, $val)));
            if ($hasMath) {
                continue;
            }

            if (isset($object[$key])) {
                $skip = false;
                $save = false;
                $columnDefinition = $columnDefList[$key];
                $colType = substr($columnDefinition, 0, 1);
                // ensure that international float numbers are stored with '.' rather than ',' for decimal separator
                if ($colType == 'F' || $colType == 'N') {
                    if (is_float($object[$key]) || is_double($object[$key])) {
                        $object[$key] = number_format($object[$key], 8, '.', '');
                    }
                }

                // generate the actual insert values
                if (!$skip) {
                    $cArray[] = $columnList[$key];
                    $vArray[] = self::_formatForStore($object[$key]);
                }
            } else {
                if ($key == $idfield) {
                    if ($dbDriverName == 'pgsql') {
                        $cArray[] = $columnList[$key];
                        $vArray[] = 'DEFAULT';
                    }
                } elseif ($force) {
                    $cArray[] = $columnList[$key];
                    $vArray[] = 'NULL';
                }
            }
        }

        if (!($cArray && $vArray)) {
            throw new Exception(__('Unable to find anything to insert in supplied object ...'));
        }

        $sql .= ' (' . implode(',', $cArray) . ')';
        $sql .= ' VALUES (' . implode(',', $vArray) . ')';

        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }

        self::flushCache($table);

        if ((!$preserve || !isset($object[$idfield])) && isset($columnList[$idfield])) {
            $obj_id = self::getInsertID($table, $idfield);
            $object[$idfield] = $obj_id;
        }

        if ($cArray && $vArray) {
            $object = self::_savePostProcess($object, $table, $idfield);
        }

        return $object;
    }

    /**
     * Generate and execute an update SQL statement for the given object.
     *
     * @param array   &$object  The object we wish to update.
     * @param string  $table    The treated table reference.
     * @param string  $where    The where clause (optional) (default='').
     * @param string  $idfield  The column which stores the primary key (optional) (default='id').
     * @param boolean $force    Whether or not to insert empty values as NULL (optional) (default=false).
     * @param boolean $updateid Allow primary key to be updated (default=false).
     *
     * @return integer The result set from the update operation
     * @deprecated
     * @see    Doctrine_Record::save()
     * @throws Exception If parameters not set or column or column_def not in array.
     */
    public static function updateObject(array &$object, $table, $where = '', $idfield = 'id', $force = false, $updateid = false)
    {
        if (!isset($object[$idfield]) && !$where) {
            throw new Exception(__('Neither object ID nor where parameters are provided'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        $sql = "UPDATE $tableName SET ";

        // set standard architecture fields
        ObjectUtil::setStandardFieldsOnObjectUpdate($object, $force);

        // build the column list
        $columnList = $tables["{$table}_column"];
        if (!is_array($columnList)) {
            throw new Exception(__f('%s_column is not an array', $table));
        }

        // build the column definition list
        $columnDefList = $tables["{$table}_column_def"];
        if (!is_array($columnDefList)) {
            throw new Exception(__f('%s_column_def is not an array', $table));
        }

        // grab each key and value and append to the sql string
        $tArray = array();
        $search = array('+', '-', '*', '/', '%');
        $replace = array('');

        foreach ($columnList as $key => $val) {
            $hasMath = (bool)(strcmp($val, str_replace($search, $replace, $val)));
            if ($hasMath) {
                continue;
            }

            if ($key != $idfield || ($key == $idfield && $updateid == true)) {
                if ($force || array_key_exists($key, $object)) {
                    $skip = false;
                    $columnDefinition = $columnDefList[$key];
                    $colType = substr($columnDefinition, 0, 1);
                    // ensure that international float numbers are stored with '.' rather than ',' for decimal separator
                    if ($colType == 'F' || $colType == 'N') {
                        if (is_float($object[$key]) || is_double($object[$key])) {
                            $object[$key] = number_format($object[$key], 8, '.', '');
                        }
                    }

                    // generate the actual update values
                    if (!$skip) {
                        $tArray[] = "$val=" . (isset($object[$key]) ? self::_formatForStore($object[$key]) : 'NULL');
                    }
                }
            }
        }

        if ($tArray) {
            if (!$where) {
                $_where = " WHERE $columnList[$idfield] = '" . DataUtil::formatForStore($object[$idfield]) . "'";
            } else {
                $_where = self::_checkWhereClause($where);
            }

            $sql .= implode(',', $tArray) . ' ' . $_where;

            $res = self::executeSQL($sql);
            if ($res === false) {
                return $res;
            }
        }

        self::flushCache($table);

        $object = self::_savePostProcess($object, $table, $idfield, true);

        return $object;
    }

    /**
     * Loop through the array and feed it to self::insertObject().
     *
     * @param array   &$objects The objectArray we wish to insert.
     * @param string  $table    The treated table reference.
     * @param string  $idfield  The column which stores the primary key (optional) (default='id').
     * @param boolean $preserve Whether or not to preserve existing/set standard fields (optional) (default=false).
     * @param boolean $force    Whether or not to insert empty values as NULL (optional) (default=false).
     *
     * @return integer The result set from the last insert operation. The objects are updated with the newly generated ID.
     *
     * @deprecated
     * @see    Doctrine_Table
     */
    public static function insertObjectArray(array &$objects, $table, $idfield = 'id', $preserve = false, $force = false)
    {
        $res = false;
        foreach (array_keys($objects) as $k) {
            $res = self::insertObject($objects[$k], $table, $idfield, $preserve, $force);
            if (!$res) {
                break;
            }
        }

        return $res;
    }

    /**
     * Loop through the array and feed it to self::updateObject().
     *
     * @param array   &$objects The objectArray we wish to insert.
     * @param string  $table   The treated table reference.
     * @param string  $idfield The column which stores the primary key.
     * @param boolean $force   Whether or not to insert empty values as NULL.
     *
     * @return integer The result set from the last update operation.
     */
    public static function updateObjectArray(array &$objects, $table, $idfield = 'id', $force = false)
    {
        $res = true;

        foreach (array_keys($objects) as $k) {
            $res = self::updateObject($objects[$k], $table, '', $idfield, $force);
            if (!$res) {
                break;
            }
        }

        return $res;
    }

    /**
     * Post-processing after this object has beens saved.
     *
     * This routine is responsible for writing the 'extra' data (attributes, categories,
     * and meta data) to the database and the optionally creating an
     * entry in the object-log table.
     *
     * @param mixed   $object  The object wehave just saved.
     * @param string  $table   The treated table reference.
     * @param integer $idfield The id column for the object/table combination.
     * @param boolean $update  Whether or not this was an update (default=false, signifies operation was an insert).
     *
     * @return mixed The object.
     * @deprecated
     * @see    CategorisableListener, AttributableListener, MetaDataListener, LoggableListener
     * @throws Exception If invalid idfield received.
     */
    private static function _savePostProcess($object, $table, $idfield, $update = false)
    {
        $tables = self::getTables();
        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (!$idfield) {
            throw new Exception(__f('Invalid idfield received', $table));
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_categorization"]) && $tables["{$table}_db_extra_enable_categorization"]) ) &&
                System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') &&
                strcmp($table, 'categories_') !== 0 &&
                strcmp($table, 'objectdata_attributes') !== 0 &&
                strcmp($table, 'objectdata_log') !== 0 &&
                ModUtil::available('Categories')) {
            ObjectUtil::storeObjectCategories($object, $table, $idfield, $update);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_attribution"]) && $tables["{$table}_db_extra_enable_attribution"] ) ||
                System::getVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) &&
                strcmp($table, 'objectdata_attributes') !== 0 &&
                strcmp($table, 'objectdata_log') !== 0) {
            ObjectUtil::storeObjectAttributes($object, $table, $idfield, $update);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_meta"]) && $tables["{$table}_db_extra_enable_meta"] ) ||
                System::getVar('Z_CONFIG_USE_OBJECT_META')) &&
                $table != 'objectdata_attributes' &&
                $table != 'objectdata_meta' &&
                $table != 'objectdata_log') {
            ObjectUtil::updateObjectMetaData($object, $table, $idfield);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_logging"]) && $tables["{$table}_db_extra_enable_logging"]) ) &&
                System::getVar('Z_CONFIG_USE_OBJECT_LOGGING') &&
                strcmp($table, 'objectdata_log') !== 0) {
            $oldObj = self::selectObjectByID($table, $object[$idfield], $idfield);

            $log = new ObjectData_Log();
            $log['object_type'] = $table;
            $log['object_id'] = $object[$idfield];
            $log['op'] = ($update ? 'U' : 'I');

            if ($update) {
                $log['diff'] = serialize(ObjectUtil::diffExtended($oldObj, $object, $idfield));
            } else {
                $log['diff'] = serialize($object);
            }

            $log->save();
        }

        return $object;
    }

    /**
     * Increment a field by the given increment.
     *
     * @param string  $table    The treated table reference.
     * @param string  $incfield The column which stores the field to increment.
     * @param integer $id       The ID value of the object holding the field we wish to increment.
     * @param string  $idfield  The idfield to use (optional) (default='id').
     * @param integer $inccount The amount by which to increment the field (optional) (default=1).
     *
     * @return The result from the increment operation.
     */
    public static function incrementObjectFieldByID($table, $incfield, $id, $idfield = 'id', $inccount = 1)
    {
        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $idFieldName = $columns[$idfield];
        $incFieldName = $columns[$incfield];
        $column = $tables["{$table}_column"];

        $sql = 'UPDATE ' . $tableName . " SET $incFieldName = $column[$incfield] + $inccount";
        $sql .= " WHERE $idFieldName = '" . DataUtil::formatForStore($id) . "'";

        $res = self::executeSQL($sql);
        if ($res === false) {
            return false;
        }

        self::flushCache($table);

        return $res;
    }

    /**
     * Decrement a field by the given decrement.
     *
     * @param string  $table    The treated table reference.
     * @param string  $decfield The column which stores the field to decrement.
     * @param integer $id       The ID value of the object holding the field we wish to increment.
     * @param string  $idfield  The idfield to use (optional) (default='id').
     * @param integer $deccount The amount by which to decrement the field (optional) (default=1).
     *
     * @return integer The result from the decrement operation.
     */
    public static function decrementObjectFieldByID($table, $decfield, $id, $idfield = 'id', $deccount = 1)
    {
        return self::incrementObjectFieldByID($table, $decfield, $id, $idfield, 0 - $deccount);
    }

    /**
     * Generate and execute a delete SQL statement for the given object.
     *
     * @param array  $object  The object we wish to delete.
     * @param string $table   The treated table reference.
     * @param string $where   The where clause to use (optional) (default='').
     * @param string $idfield The column which contains the ID field (optional) (default='id').
     *
     * @return The result from the delete operation.
     * @deprecated
     * @see    CategorisableListener, AttributableListener, MetaDataListener, LoggableListener
     * @throws Exception Cant specify both object and whereclause or either are missing.
     */
    public static function deleteObject(array $object, $table, $where = '', $idfield = 'id')
    {
        if ($object && $where) {
            throw new Exception(__("Can't specify both object and where-clause"));
        }

        if (!$object && !$where) {
            throw new Exception(__('Missing either object or where-clause'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$idfield];
        $sql = "DELETE FROM $tableName ";

        if (!$where) {
            if (!$object[$idfield]) {
                throw new Exception(__('Object does not have an ID'));
            }
            $sql .= "WHERE $fieldName = '" . DataUtil::formatForStore($object[$idfield]) . "'";
        } else {
            $sql .= self::_checkWhereClause($where);
            $object['__fake_field__'] = 'Fake entry to mark deleteWhere() return as valid object';
        }

        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }

        self::flushCache($table);

        // Attribution and logging only make sense if we do object-based deletion.
        // If we come from deleteWhere, we simply don't do any of this as in that
        // case we don't know the object ID to map attributes to.
        // TODO D [there should be a dangling attribute cleanup function somewhere]
        self::_deletePostProcess($object, $table, $idfield);

        return $res;
    }

    /**
     * Generate and execute a delete SQL statement.
     *
     * @param array  $keyarray The KeyArray todelete.
     * @param mixed  $table    The treated table reference.
     * @param string $field    The field to use.
     *
     * @return mixed
     */
    public static function deleteObjectsFromKeyArray(array $keyarray, $table, $field = 'id')
    {
        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        $sql = 'DELETE FROM ' . $tableName . ' WHERE ' . $fieldName . ' IN (';

        $sqlArray = array();
        foreach ($keyarray as $key => $val) {
            $sqlArray[] = $key;
        }
        $sql .= implode(',', $sqlArray) . ')';

        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }

        self::flushCache($tableName);

        return $res;
    }

    /**
     * Delete an object by its ID.
     *
     * @param string  $table       The treated table reference.
     * @param integer $id          The ID of the object to delete.
     * @param string  $idFieldName The column which contains the ID field (optional) (default='id').
     *
     * @return integer The result from the delete operation
     */
    public static function deleteObjectByID($table, $id, $idFieldName = 'id')
    {
        $object = array();
        $object[$idFieldName] = $id;

        return self::deleteObject($object, $table, '', $idFieldName);
    }

    /**
     * Delete (an) object(s) via a where clause.
     *
     * @param string $table The treated table reference.
     * @param string $where The where-clause to use.
     *
     * @return mixed The result from the delete operation.
     */
    public static function deleteWhere($table, $where)
    {
        $tables = self::getTables();
        $tableName = $tables[$table];
        $where = self::_checkWhereClause($where);
        $sql = 'DELETE FROM ' . $tableName . ' ' . $where;

        return self::executeSQL($sql);
    }

    /**
     * Post-processing after this object has beens deleted.
     *
     * This routine is responsible for deleting the 'extra' data (attributes, categories,
     * and meta data) from the database and the optionally creating an
     * entry in the object-log table.
     *
     * @param mixed   $object  The object wehave just saved.
     * @param string  $table   The treated table reference.
     * @param integer $idfield The id column for the object/table combination.
     *
     * @deprecated
     * @see    CategorisableListener, AttributableListener, MetaDataListener, LoggableListener
     * @return void
     */
    private static function _deletePostProcess($object, $table, $idfield)
    {
        $tables = self::getTables();
        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_categorization"]) && $tables["{$table}_db_extra_enable_categorization"]) ) &&
                System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') &&
                $table != 'categories_' &&
                $table != 'objectdata_attributes' &&
                $table != 'objectdata_log' &&
                ModUtil::available('Categories')) {
            ObjectUtil::deleteObjectCategories($object, $table, $idfield);
        }

        if (((isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]) ||
                (isset($tables["{$table}_db_extra_enable_attribution"]) && $tables["{$table}_db_extra_enable_attribution"] ) ||
                System::getVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) &&
                $table != 'objectdata_attributes' &&
                $table != 'objectdata_log') {
            ObjectUtil::deleteObjectAttributes($object, $table, $idfield);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_meta"]) && $tables["{$table}_db_extra_enable_meta"] ) ||
                System::getVar('Z_CONFIG_USE_OBJECT_META')) &&
                $table != 'objectdata_attributes' &&
                $table != 'objectdata_meta' &&
                $table != 'objectdata_log') {
            ObjectUtil::deleteObjectMetaData($object, $table, $idfield);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_logging"]) && $tables["{$table}_db_extra_enable_logging"]) ) &&
                System::getVar('Z_CONFIG_USE_OBJECT_LOGGING') &&
                strcmp($table, 'objectdata_log') !== 0) {
            $log = new ObjectData_Log();
            $log['object_type'] = $table;
            $log['object_id'] = $object[$idfield];
            $log['op'] = 'D';
            $log['diff'] = serialize($object);
            $log->save();
        }
    }

    /**
     * Convenience function to ensure that the where-clause starts with "WHERE".
     *
     * @param string $where The original where clause.
     *
     * @return string The value held by the global counter.
     */
    public static function _checkWhereClause($where)
    {
        if (!strlen(trim($where))) {
            return $where;
        }

        $where = trim($where);
        $upwhere = strtoupper($where);
        if (strstr($upwhere, 'WHERE') === false || strpos($upwhere, 'WHERE') > 1) {
            $where = 'WHERE ' . $where;
        }

        return $where;
    }

    /**
     * Convenience function to ensure that the order-by-clause starts with "ORDER BY".
     *
     * @param string $orderby The original order-by clause.
     * @param string $table   The table reference, only used for oracle quote determination (optional) (default=null).
     *
     * @return string The (potentially) altered order-by-clause.
     */
    public static function _checkOrderByClause($orderby, $table = null)
    {
        if (!strlen(trim($orderby))) {
            return $orderby;
        }

        $tables = self::getTables();
        $dbDriverName = Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName();

        // given that we use quotes in our generated SQL, oracle requires the same quotes in the order-by
        if ($dbDriverName == 'oracle') {
            $t = str_replace('ORDER BY ', '', $orderby); // remove "ORDER BY" for easier parsing
            $t = str_replace('order by ', '', $t); // remove "order by" for easier parsing


            $columns = $tables["{$table}_column"];

            // anything which doesn't look like a basic ORDER BY clause (with possibly an ASC/DESC modifier)
            // we don't touch. To use such stuff with Oracle, you'll have to apply the quotes yourself.


            $tokens = explode(',', $t); // split on comma
            foreach ($tokens as $k => $v) {
                $v = trim($v);
                if (strpos($v, ' ') === false) {
                    // 1 word
                    if (strpos($v, '(') === false) {
                        // not a function call
                        if (strpos($v, '"') === false) {
                            // not surrounded by quotes already
                            if (isset($columns[$v])) {
                                // ensure that token is an alias
                                $tokens[$k] = '"' . $v . '"'; // surround it by quotes
                            }
                        }
                    }
                } else {
                    // multiple words, perform a few basic hecks
                    $ttok = explode(' ', $v); // split on space
                    if (count($ttok) == 2) {
                        // see if we have 2 tokens
                        $t1 = strtolower(trim($ttok[0]));
                        $t2 = strtolower(trim($ttok[1]));
                        $haveQuotes = strpos($t1, '"') === false;
                        $isAscDesc = (strpos($t2, 'asc') === 0 || strpos($t2, 'desc') === 0);
                        $isColumn = isset($columns[$ttok[0]]);
                        if ($haveQuotes && $isAscDesc && $isColumn) {
                            $ttok[0] = '"' . $ttok[0] . '"'; // surround it by quotes
                        }
                    }
                    $tokens[$k] = implode(' ', $ttok);
                }
            }

            $orderby = implode(', ', $tokens);
        }

        if (stristr($orderby, 'ORDER BY') === false) {
            $orderby = 'ORDER BY ' . $orderby;
        }

        return $orderby;
    }

    /**
     * Convenience function.
     *
     * Ensures that the field to be used as ORDER BY
     * is not a CLOB/BLOB when using Oracle
     *
     * @param string $table The treated table reference.
     * @param string $field The field name to be used for order by.
     *
     * @return string The order-by-clause to be used, may be ''.
     */
    public static function _checkOrderByField($table = '', $field = '')
    {
        $orderby = '';

        if (empty($field) || empty($table)) {
            return $orderby;
        }

        $dbDriverName = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());
        $tables = self::getTables();
        $columns = $tables["{$table}_column"];
        $columnsdef = $tables["{$table}_column_def"];
        $fieldName = $columns[$field];
        $fieldDef = $columnsdef[$field];

        if ($dbDriverName == 'oracle') {
            // we are using oracle - split up the field definition and check if it is defined as a LOB
            // oracle does not like LOBs in an ORDERBY
            $definition = explode(' ', $fieldDef);
            // [0] contains the dangerous information, either XL or B
            if (strtoupper($definition[0]) != 'XL' && strtoupper($definition[0]) != 'B') {
                // no BLOB, no problem
                $orderby = 'ORDER BY ' . $fieldName;
            }
        } else {
            $orderby = 'ORDER BY ' . $fieldName;
        }

        return $orderby;
    }

    /**
     * Build a basic select clause for the specified table with the specified where and orderBy clause.
     *
     * @param string $table       The treated table reference.
     * @param string $where       The original where clause (optional) (default='').
     * @param string $orderBy     The original order-by clause (optional) (default='').
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null).
     * @param string $distinct    Set if a "SELECT DISTINCT" should be performed.
     *
     * @return string The select clause built.
     */
    public static function _getSelectAllColumnsFrom($table, $where = '', $orderBy = '', $columnArray = null, $distinct = false)
    {
        $tables = self::getTables();
        $tableName = $tables[$table];

        $query = 'SELECT ' . ($distinct ? 'DISTINCT ' : '') . self::_getAllColumns($table, $columnArray) . " FROM $tableName AS tbl ";

        if (trim($where)) {
            $query .= self::_checkWhereClause($where) . ' ';
        }

        if (trim($orderBy)) {
            $query .= self::_checkOrderByClause($orderBy, $table) . ' ';
        }

        return $query;
    }

    /**
     * Set the gobal object fetch counter to the specified value.
     *
     * This function is workaround for PHP4 limitations when passing default arguments by reference.
     * Returns nothing, the global variable is assigned counter.
     *
     * @param integer $count The value to set the object marhsall counter to.
     *
     * @return void
     */
    public static function _setFetchedObjectCount($count = 0)
    {
        // TODO D [remove PHP4 stuff in DBUtil] (Guite)
        $GLOBALS['DBUtilFetchObjectCount'] = $count;

        return;
    }

    /**
     * Get the gobal object fetch counter.
     *
     * This function is workaround for PHP4 limitations when passing default arguments by reference.
     *
     * @return integer The value held by the global.
     * @deprecated
     */
    public static function _getFetchedObjectCount()
    {
        // TODO D [remove PHP4 stuff in DBUtil] (Guite)
        if (isset($GLOBALS['DBUtilFetchObjectCount'])) {
            return (int)$GLOBALS['DBUtilFetchObjectCount'];
        }

        return false;
    }

    /**
     * Transform a result set into an array of field values.
     *
     * @param mixed   $result         The result set we wish to marshall.
     * @param boolean $closeResultSet Whether or not to close the supplied result set (optional) (default=true).
     * @param string  $assocKey       The key field to use to build the associative index (optional) (default='').
     * @param boolean $clean          Whether or not to clean up the marshalled data (optional) (default=true).
     *
     * @return The       resulting field array.
     * @throws Exception If empty result parameter.
     */
    public static function marshallFieldArray($result, $closeResultSet = true, $assocKey = '', $clean = true)
    {
        if (!$result) {
            throw new Exception(__f('The parameter %s must not be empty', 'result'));
        }

        $resultRows = $result->fetchAll(Doctrine::FETCH_NUM);
        $fieldArray = array();
        if ($assocKey) {
            foreach ($resultRows as $resultRow) {
                $f1 = $resultRow[1];
                $fieldArray[$f1] = $resultRow[0];
            }
        } else {
            foreach ($resultRows as $resultRow) {
                $fieldArray[] = $resultRow[0];
            }
        }

        return $fieldArray;
    }

    /**
     * Transform a SQL query result set into an object/array, optionally applying an permission filter.
     *
     * @param mixed   $result           The result set we wish to marshall.
     * @param array   $objectColumns    The column array to map onto the result set, default null = don't use.
     * @param boolean $closeResultSet   Whether or not to close the supplied result set (optional) (default=true).
     * @param string  $assocKey         The key field to use to build the associative index (optional) (default='').
     * @param boolean $clean            Whether or not to clean up the marshalled data (optional) (default=true).
     * @param string  $permissionFilter The permission structure to use for permission checking (optional) (default=null).
     * @param string  $tablename        The tablename.
     *
     * @return array     The marshalled array of objects.
     * @throws Exception If empty parameters. or if permissionfilter is not an array.
     */
    public static function marshallObjects($result, $objectColumns = null, $closeResultSet = true, $assocKey = '', $clean = true, $permissionFilter = null, $tablename = null)
    {
        if (!$result) {
            throw new Exception(__f('The parameter %s must not be empty', 'result'));
        }

        if (!is_null($objectColumns) && !$objectColumns) {
            throw new Exception(__f('The parameter %s must not be empty', 'objectColumns'));
        }

        // since the single-object selects don't need to init
        // the paging logic, we ensure values are set here
        // in order to avoid E_ALL issues
        if (!isset($GLOBALS['DBUtilFetchObjectCount'])) {
            self::_setFetchedObjectCount(0);
        }

        $object = array();
        $objectArray = array();
        $fetchedObjectCount = 0;
        $resultRows = $result->fetchAll(Doctrine::FETCH_ASSOC);
        if ($resultRows && $objectColumns && count($resultRows[0]) != count($objectColumns)) {
            throw new Exception('$objectColumn field count must match the resultset');
        }

        if ($assocKey && $resultRows &&
            (!array_key_exists($assocKey, $resultRows[0]) && !in_array($assocKey, $objectColumns))) {
            throw new Exception(__f('Unable to find assocKey [%1$s] in objectColumns for resultset.', array($assocKey)));
        }

        foreach ($resultRows as $resultRow) {
            $fetchedObjectCount++;
            if ($objectColumns) {
                $object = array_combine($objectColumns, $resultRow);
            } else {
                $object = $resultRow;
            }

            $havePerm = true;
            if ($permissionFilter) {
                if (!is_array($permissionFilter)) {
                    throw new Exception(__f('The parameter %s must be an array', 'permissionFilter'));
                }

                // we need an array of arrays, but this will fix a single array
                if (!is_array($permissionFilter[0])) {
                    $permissionFilter = array(
                            $permissionFilter);
                }

                foreach (array_keys($permissionFilter) as $k) {
                    $pf = $permissionFilter[$k];
                    if (!is_array($pf)) {
                        throw new Exception(__('Permission filter iterator did not return an array (must be an array of arrays)'));
                    }

                    $cl = (isset($pf['component_left']) ? $pf['component_left'] : '');
                    $cm = (isset($pf['component_middle']) ? $pf['component_middle'] : '');
                    $cr = (isset($pf['component_right']) ? $pf['component_right'] : '');
                    $il = (isset($pf['instance_left']) ? $pf['instance_left'] : '');
                    $im = (isset($pf['instance_middle']) ? $pf['instance_middle'] : '');
                    $ir = (isset($pf['instance_right']) ? $pf['instance_right'] : '');
                    $oil = ($il && isset($object[$il]) ? $object[$il] : '__PERM_NO_SUCH_ITEM__');
                    $oim = ($im && isset($object[$im]) ? $object[$im] : '__PERM_NO_SUCH_ITEM__');
                    $oir = ($ir && isset($object[$ir]) ? $object[$ir] : '__PERM_NO_SUCH_ITEM__');
                    $level = (isset($pf['level']) && $pf['level'] ? $pf['level'] : false);

                    if (!$cl && !$cm && !$cr) {
                        throw new Exception('Permission filter component is empty: [' . $cl . '], [' . $cm . '], [' . $cr . ']');
                    }

                    if (!$il && !$im && !$ir) {
                        throw new Exception('Permission filter instance is empty: [' . $il . '], [' . $im . '], [' . $ir . ']');
                    }

                    if ($oil == '__PERM_NO_SUCH_ITEM__' && $oim == '__PERM_NO_SUCH_ITEM__' && $oir == '__PERM_NO_SUCH_ITEM__') {
                        throw new Exception('Permission filter instance is invalid: [' . $oil . '], [' . $oim . '], [' . $oir . ']');
                    }

                    if (!$level) {
                        throw new Exception(__f('Permission filter level is invalid: [%s]', $level));
                    }

                    $component = $cl . ':' . $cm . ':' . $cr;
                    $instance = $oil . ':' . $oim . ':' . $oir;
                    if (!SecurityUtil::checkPermission($component, $instance, $level)) {
                        $havePerm = false;
                        break;
                    }
                }
            }

            if ($havePerm) {
                if ($assocKey) {
                    $key = $object[$assocKey];
                    $objectArray[$key] = $object;
                } else {
                    $objectArray[] = $object;
                }
            }
        }

        self::_setFetchedObjectCount($fetchedObjectCount);

        return $objectArray;
    }

    /**
     * Execute SQL select statement and return the value of the first column in the first row.
     *
     * Mostly useful for places where you want to do a "select count(*)" or similar scalar selection.
     *
     * @param string  $sql         Sql string.
     * @param boolean $exitOnError Exit on error.
     *
     * @return mixed     selected value.
     * @throws Exception If rowcount or results count is empty.
     */
    public static function selectScalar($sql, $exitOnError = true)
    {
        $res = self::executeSQL($sql);
        if ($res === false) {
            return false;
        }

        $value = null;
        if (!$res->rowCount()) {
            if ($exitOnError) {
                throw new Exception(__('Got no rows to select from'));
            }
        }
        $results = $res->fetchAll();
        $results = $results[0];

        if (count($results) < 1) {
            if ($exitOnError) {
                throw new Exception(__('Got no columns to select from'));
            }
        } else {
            $value = $results[0];
        }

        return $value;
    }

    /**
     * Select & return a field.
     *
     * @param string $table The treated table reference.
     * @param string $field The name of the field we wish to marshall.
     * @param string $where The where clause (optional) (default='').
     *
     * @return The resulting field array.
     */
    public static function selectField($table, $field, $where = '')
    {
        $fieldArray = self::selectFieldArray($table, $field, $where);

        if (count($fieldArray) > 0) {
            return $fieldArray[0];
        }

        return false;
    }

    /**
     * Select & return a field by an ID-field value.
     *
     * @param string  $tableName The treated table reference.
     * @param string  $field     The field we wish to select.
     * @param integer $id        The ID value we wish to select with.
     * @param string  $idfield   The idfield to use (optional) (default='id').
     *
     * @return mixed The resulting field value.
     */
    public static function selectFieldByID($tableName, $field, $id, $idfield = 'id')
    {
        $tables = self::getTables();
        $cols = $tables["{$tableName}_column"];
        $idFieldName = $cols[$idfield];

        $where = $idFieldName . " = '" . DataUtil::formatForStore($id) . "'";

        return self::selectField($tableName, $field, $where);
    }

    /**
     * Select & return a field array.
     *
     * @param string  $table    The treated table reference.
     * @param string  $field    The name of the field we wish to marshall.
     * @param string  $where    The where clause (optional) (default='').
     * @param string  $orderby  The orderby clause (optional) (default='').
     * @param boolean $distinct Whether or not to add a 'DISTINCT' clause (optional) (default=false).
     * @param string  $assocKey The key field to use to build the associative index (optional) (default='').
     *
     * @return array The resulting field array.
     */
    public static function selectFieldArray($table, $field, $where = '', $orderby = '', $distinct = false, $assocKey = '')
    {
        $key = $field . $where . $orderby . $distinct . $assocKey;
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        $tables = self::getTables();
        if (!isset($tables["{$table}_column"])) {
            return false;
        }

        $columns = $tables["{$table}_column"];
        $tableName = $tables[$table];
        $dSql = ($distinct ? "DISTINCT($columns[$field])" : "$columns[$field]");
        if ($assocKey) {
            $assocColumn = $columns[$assocKey];
        }

        $assoc = ($assocKey ? ", $columns[$assocKey]" : '');
        $where = self::_checkWhereClause($where);

        if ($orderby) {
            $orderby = self::_checkOrderByClause($orderby, $table);
        } else {
            $orderby = self::_checkOrderByField($table, $field); // "ORDER BY $columns[$field]";
        }

        $sql = "SELECT $dSql $assoc FROM $tableName AS tbl $where $orderby";

        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }

        $fields = self::marshallFieldArray($res, true, $assocKey);
        self::setCache($table, $key, $fields);

        return $fields;
    }

    /**
     * Select & return an array of field by an ID-field value.
     *
     * @param string $tableName The treated table reference.
     * @param string $field     The field we wish to select.
     * @param string $id        The ID value we wish to select with.
     * @param string $idfield   The idfield to use (optional) (default='id').
     *
     * @return mixed The resulting field value.
     */
    public static function selectFieldArrayByID($tableName, $field, $id, $idfield = 'id')
    {
        $tables = self::getTables();
        $cols = $tables["{$tableName}_column"];
        $idFieldName = $cols[$idfield];

        $where = $idFieldName . " = '" . DataUtil::formatForStore($id) . "'";

        return self::selectFieldArray($tableName, $field, $where);
    }

    /**
     * Select & return the max/min value of a field.
     *
     * @param string $table  The treated table reference.
     * @param string $field  The name of the field we wish to marshall.
     * @param string $option MIN, MAX, SUM or COUNT (optional) (default='MAX').
     * @param string $where  The where clause (optional) (default='').
     *
     * @return mixed The resulting min/max value.
     */
    public static function selectFieldMax($table, $field, $option = 'MAX', $where = '')
    {
        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        $field = isset($fieldName) ? $fieldName : $field;

        $sql = "SELECT $option($field) FROM $tableName AS tbl";
        $where = self::_checkWhereClause($where);

        $sql .= ' ' . $where;

        $res = self::executeSQL($sql);
        if ($res === false) {
            return false;
        }

        $max = false;
        if ($data = $res->fetchColumn(0)) {
            $max = $data;
        }

        return $max;
    }

    /**
     * Select & return the max/min array of a field grouped by the associated key.
     *
     * @param string $table    The treated table reference.
     * @param string $field    The name of the field we wish to marshall.
     * @param string $option   MIN, MAX, SUM or COUNT (optional) (default='MAX').
     * @param string $where    The where clause (optional) (default='').
     * @param string $assocKey The key field to use to build the associative index (optional) (default='' which defaults to the primary key).
     *
     * @return array The resulting min/max value.
     */
    public static function selectFieldMaxArray($table, $field, $option = 'MAX', $where = '', $assocKey = '')
    {
        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        if (!$assocKey) {
            $assocKey = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';
        }

        $sql = "SELECT $assocKey AS $assocKey, $option($fieldName) AS $option FROM $tableName AS tbl";
        $where = self::_checkWhereClause($where);

        $sql .= ' ' . $where;
        $sql .= ' ' . "GROUP BY $assocKey";

        $res = self::executeSQL($sql);
        if ($res === false) {
            return false;
        }

        $objArray = array();
        foreach ($res as $row) {
            $objArray[$row[0]] = $row[1];
        }

        return $objArray;
    }

    /**
     * Build a list of objects which are mapped to the specified categories.
     *
     * @param string  $tablename      Treated table reference.
     * @param string  $categoryFilter The category list to use for filtering.
     * @param boolean $returnArray    Whether or not to return an array (optional) (default=false).
     *
     * @return mixed The resulting string or array.
     */
    private static function _generateCategoryFilter($tablename, $categoryFilter, $returnArray = false)
    {
        if (!$categoryFilter) {
            return '';
        }

        if (!ModUtil::dbInfoLoad('Categories')) {
            return '';
        }

        // check the meta data
        if (isset($categoryFilter['__META__']['module'])) {
            $modname = $categoryFilter['__META__']['module'];
        } else {
            $modname = ModUtil::getName();
        }

        // check operator to use
        // when it's AND, the where contains subqueries
        if (isset($categoryFilter['__META__']['operator']) && in_array(strtolower($categoryFilter['__META__']['operator']), array('and', 'or'))) {
            $op = strtoupper($categoryFilter['__META__']['operator']);
        } else {
            $op = 'OR';
        }

        unset($categoryFilter['__META__']);

        // get the properties IDs in the category register
        $propids = CategoryRegistryUtil::getRegisteredModuleCategoriesIds($modname, $tablename);

        // build the where clause
        $n = 1; // subquery counter
        $catmapobjtbl = 'categories_mapobj';

        $where = array();
        foreach ($categoryFilter as $property => $category) {
            $prefix = '';
            if ($op == 'AND') {
                $prefix = "table$n.";
            }

            // this allows to have an array of categories IDs
            if (is_array($category)) {
                $wherecat = array();
                foreach ($category as $cat) {
                    $wherecat[] = "{$prefix}category_id='" . DataUtil::formatForStore($cat) . "'";
                }
                $wherecat = '(' . implode(' OR ', $wherecat) . ')';

            // if there's only one category ID
            } else {
                $wherecat = "{$prefix}category_id='" . DataUtil::formatForStore($category) . "'";
            }

            // process the where depending of the operator
            if ($op == 'AND') {
                $where[] = "obj_id IN (SELECT {$prefix}obj_id FROM $catmapobjtbl table$n WHERE {$prefix}reg_id = '".DataUtil::formatForStore($propids[$property])."' AND $wherecat)";
            } else {
                $where[] = "(reg_id='" . DataUtil::formatForStore($propids[$property]) . "' AND $wherecat)";
            }
            $n++;
        }
        $where = "tablename='" . DataUtil::formatForStore($tablename) . "' AND (" . implode(" $op ", $where) . ')';

        // perform the query
        $objIds = DBUtil::selectFieldArray('categories_mapobj', 'obj_id', $where);

        // this ensures that we return an empty set if no objects are mapped to the requested categories
        if (!$objIds) {
            $objIds[] = -1;
        }

        if ($returnArray) {
            return $objIds;
        }

        return implode(',', $objIds);
    }

    /**
     * Append the approriate category filter where-clause to the given where clause.
     *
     * @param string  $table          The treated table reference.
     * @param string  $where          The where clause (optional) (default='').
     * @param string  $categoryFilter The category list to use for filtering.
     * @param boolean $returnArray    Whether or not to return an array (optional) (default=false).
     * @param boolean $usesJoin       Whether a join is used (if yes, then a prefix is prepended to the column name) (optional) (default=false).
     *
     * @return mixed The resulting string or array.
     */
    public static function generateCategoryFilterWhere($table, $where, $categoryFilter, $returnArray = false, $usesJoin = false)
    {
        $tables = self::getTables();
        $idlist = self::_generateCategoryFilter($table, $categoryFilter);
        if ($idlist) {
            $cols = $tables["{$table}_column"];
            $idcol = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';
            $idcol = $cols[$idcol];

            $and = ($where ? ' AND ' : '');
            $tblName = ($usesJoin ? 'tbl.' : '') . $idcol;
            $where .= "$and $tblName IN ($idlist)";
        }

        return $where;
    }

    /**
     * Select & return a specific object using the given sql statement.
     *
     * @param string $sql              The sql statement to execute for the selection.
     * @param string $table            The treated table reference.
     * @param array  $columnArray      The columns to marshall into the resulting object (optional) (default=null).
     * @param string $permissionFilter The permission filter to use for permission checking (optional) (default=null).
     *
     * @return array The resulting object.
     */
    public static function selectObjectSQL($sql, $table, $columnArray = null, $permissionFilter = null)
    {
        $permissionFilterKey = '';
        if (is_array($permissionFilter)) {
            foreach ($permissionFilter as $permissionRule) {
                $permissionFilterKey .= implode('_', $permissionRule);
            }
        }

        $res = self::executeSQL($sql, 0, 1);
        if ($res === false) {
            return $res;
        }

        $ca = null; //Not required since Zikula 1.3.0 because of 'PDO::fetchAll()' #2227 //self::getColumnsArray($table, $columnArray);
        $objArr = self::marshallObjects($res, $ca, true, '', true, $permissionFilter);

        if (count($objArr) > 0) {
            return $objArr[0];
        }
    }

    /**
     * Select & return a specific object based on a table definition.
     *
     * @param string $table            The treated table reference.
     * @param string $where            The where clause (optional) (default='').
     * @param array  $columnArray      The columns to marshall into the resulting object (optional) (default=null).
     * @param string $permissionFilter The permission filter to use for permission checking (optional) (default=null).
     * @param string $categoryFilter   The category list to use for filtering (optional) (default=null).
     *
     * @return mixed The resulting object.
     */
    public static function selectObject($table, $where = '', $columnArray = null, $permissionFilter = null, $categoryFilter = null)
    {
        $key = $where . serialize($columnArray) . serialize($permissionFilter) . serialize($categoryFilter);
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        $tables = self::getTables();
        $sql = self::_getSelectAllColumnsFrom($table, $where, '', $columnArray);
        $object = self::selectObjectSQL($sql, $table, $columnArray, $permissionFilter);

        // since we're dealing with a single object, we
        // just check it's presence in the category mapping array
        $idarr = self::_generateCategoryFilter($table, $categoryFilter, true);
        $idcol = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';
        if ($idarr && $idcol && !in_array($object[$idcol], $idarr)) {
            return array();
        }

        $object = self::_selectPostProcess($object, $table, $idcol);

        self::setCache($table, $key, $object);

        return $object;
    }

    /**
     * Select & return a specific object by using the ID field.
     *
     * @param string  $table            The treated table reference.
     * @param integer $id               The object ID to query.
     * @param string  $field            The field key which holds the ID value (optional) (default='id').
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null).
     * @param string  $permissionFilter The permission structure to use for permission checking (optional) (default=null).
     * @param string  $categoryFilter   The category list to use for filtering (optional) (default=null).
     * @param boolean $cacheObject      If true returns a cached object if available (optional) (default=true).
     * @param boolean $transformFunc    Transformation function to apply to $id (optional) (default=null).
     *
     * @return mixed The resulting object.
     * @deprecated
     * @see    Doctrine_Table::find*
     * @throws Exception If id parameter is empty or non-numeric.
     */
    public static function selectObjectByID($table, $id, $field = 'id', $columnArray = null, $permissionFilter = null, $categoryFilter = null, $cacheObject = true, $transformFunc = null)
    {
        $tables = self::getTables();
        if (!$id) {
            throw new Exception(__f('The parameter %s must not be empty', 'id'));
        }

        if ($field == 'id' && !is_numeric($id)) {
            throw new Exception(__f('The parameter %s must be numeric', 'id'));
        }

        $cols = $tables["{$table}_column"];
        $fieldName = $cols[$field];

        $where = (($transformFunc) ? "$transformFunc($fieldName)" : $fieldName) . ' = \'' . DataUtil::formatForStore($id) . '\'';

        $obj = self::selectObject($table, $where, $columnArray, $permissionFilter, $categoryFilter, $cacheObject);
        // _selectPostProcess is already called in selectObject()

        return $obj;
    }

    /**
     * Select & return an object array based on a table definition.
     *
     * @param string  $table            The treated table reference.
     * @param string  $where            The where clause (optional) (default='').
     * @param string  $orderby          The order by clause (optional) (default='').
     * @param integer $limitOffset      The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows     The upper limit bound (optional) (default=-1).
     * @param string  $assocKey         The key field to use to build the associative index (optional) (default='').
     * @param string  $permissionFilter The permission filter to use for permission checking (optional) (default=null).
     * @param string  $categoryFilter   The category list to use for filtering (optional) (default=null).
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null).
     * @param string  $distinct         Set if a "SELECT DISTINCT" should be performed.
     *
     * @return array The resulting object array.
     */
    public static function selectObjectArray($table, $where = '', $orderby = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '', $permissionFilter = null, $categoryFilter = null, $columnArray = null, $distinct = '')
    {
        $key = $where . $orderby . $limitOffset . $limitNumRows . $assocKey . serialize($permissionFilter) . serialize($categoryFilter) . serialize($columnArray) . ($distinct ? '1' : '0');
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        self::_setFetchedObjectCount(0);

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $objects = array();
        $ca = null; // Not required since Zikula 1.3.0 because of 'PDO::fetchAll()' #2227// self::getColumnsArray($table, $columnArray);
        $sql = self::_getSelectAllColumnsFrom($table, $where, $orderby, $columnArray, $distinct);

        do {
            $fetchedObjectCount = self::_getFetchedObjectCount();
            $stmt = $sql;
            $limitOffset += $fetchedObjectCount;

            $res = self::executeSQL($stmt, $limitOffset, $limitNumRows);
            if ($res === false) {
                return $res;
            }

            $objArr = self::marshallObjects($res, $ca, true, $assocKey, true, $permissionFilter);
            $fc = self::_getFetchedObjectCount();
            if ($objArr) {
                $objects = $objects + (array)$objArr; // append new array
            }
        } while ($permissionFilter && ($limitNumRows != -1 && $limitNumRows > 0) && $fetchedObjectCount > 0 && count($objects) < $limitNumRows);

        if ($limitNumRows != -1 && count($objects) > $limitNumRows) {
            $objects = array_slice($objects, 0, $limitNumRows);
        }

        $tables = self::getTables();
        $idFieldName = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';

        $objects = self::_selectPostProcess($objects, $table, $idFieldName);

        self::setCache($table, $key, $objects);

        return $objects;
    }

    /**
     * Select and return an object array based on a table definition.
     *
     * The result is filtered by a callback object passed into the function. This object must
     * have implemented a method called "checkResult" which is passed the resulting data rows
     * one by one. The "checkResult" function returns true if the datarow is ok, otherwise
     * it returns false.
     *
     * Example:
     * <code>
     * class myFilter
     * {
     *   var $userId;
     *
     *   function checkResult($datarow)
     *   {
     *     return $datarow['ownerUserId'] == $this->userId;
     *   }
     * }
     * </code>
     *
     * @param string   $table          The treated table reference.
     * @param string   $where          The where clause (optional) (default='').
     * @param string   $orderby        The order by clause (optional) (default='').
     * @param integer  $limitOffset    The lower limit bound (optional) (default=-1).
     * @param integer  $limitNumRows   The upper limit bound (optional) (default=-1).
     * @param string   $assocKey       The key field to use to build the associative index (optional) (default='').
     * @param callback $filterCallback The filter callback object.
     * @param array    $categoryFilter The category list to use for filtering.
     * @param array    $columnArray    The columns to marshall into the resulting object (optional) (default=null).
     *
     * @return The resulting object array
     */
    public static function selectObjectArrayFilter($table, $where = '', $orderby = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '', $filterCallback, $categoryFilter = null, $columnArray = null)
    {
        self::_setFetchedObjectCount(0);

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $objects = array();
        $fetchedObjectCount = 0;
        $ca = null; //Note required since Zikula 1.3.0 because of PDO::fetchAll() see #2227 //self::getColumnsArray($table, $columnArray);
        $sql = self::_getSelectAllColumnsFrom($table, $where, $orderby, $columnArray);

        do {
            $stmt = $sql;
            $limitOffset += $fetchedObjectCount;

            $res = self::executeSQL($stmt, $limitOffset, $limitNumRows);
            if ($res === false) {
                return $res;
            }

            $objArr = self::marshallObjects($res, $ca, true, $assocKey, true, null, $table);
            $fetchedObjectCount = self::_getFetchedObjectCount();

            for ($i = 0, $cou = count($objArr); $i < $cou; ++$i) {
                $obj = & $objArr[$i];
                if ($filterCallback->checkResult($obj)) {
                    $objects[] = $obj;
                }
            }
        } while ($limitNumRows != -1 && $limitNumRows > 0 && $fetchedObjectCount > 0 && count($objects) < $limitNumRows);

        $tables = self::getTables();
        $idFieldName = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';
        $objects = self::_selectPostProcess($objects, $table, $idFieldName);

        return $objects;
    }

    /**
     * Return the sum of a column.
     *
     * @param string $table          The treated table reference.
     * @param string $column         The column to place in the sum phrase.
     * @param string $where          The where clause (optional) (default='').
     * @param string $categoryFilter The category list to use for filtering (optional) (default=null).
     *
     * @return integer The resulting column sum.
     */
    public static function selectObjectSum($table, $column, $where = '', $categoryFilter = null)
    {
        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$column];

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);

        $sql = "SELECT SUM($fieldName) FROM $tableName $where";

        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }

        $sum = false;
        if ($data = $res->fetchColumn(0)) {
            $sum = $data;
        }

        return $sum;
    }

    /**
     * Return the number of rows affected.
     *
     * @param string  $table          The treated table reference.
     * @param string  $where          The where clause (optional) (default='').
     * @param string  $column         The column to place in the count phrase (optional) (default='*').
     * @param boolean $distinct       Whether or not to count distinct entries (optional) (default='false').
     * @param string  $categoryFilter The category list to use for filtering (optional) (default=null).
     *
     * @return integer The resulting object count.
     */
    public static function selectObjectCount($table, $where = '', $column = '1', $distinct = false, $categoryFilter = null, $subquery = null)
    {
        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];

        $dst = ($distinct && $column != '1' ? 'DISTINCT' : '');
        $col = ($column === '1' ? '1' : $columns[$column]);

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);

        if ($subquery) {
            $sql = "SELECT COUNT($dst $col) FROM $subquery";
        } else {
            $sql = "SELECT COUNT($dst $col) FROM $tableName AS tbl $where";
        }

        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }

        $res = $res->fetchAll(Doctrine::FETCH_COLUMN);

        if ($res) {
            if (isset($res[0])) {
                $count = $res[0];
            } else {
                $count = $res["COUNT($dst $col)"];
            }
        }

        return $count;
    }

    /**
     * Select an object count by ID.
     *
     * @param string  $table         The treated table reference.
     * @param integer $id            The id value to match.
     * @param string  $field         The field to match the ID against (optional) (default='id').
     * @param string  $transformFunc Transformation function to apply to $id (optional) (default=null).
     *
     * @return The       resulting object count.
     * @throws Exception If id paramerter is empty or non-numeric.
     */
    public static function selectObjectCountByID($table, $id, $field = 'id', $transformFunc = '')
    {
        if (!$id) {
            throw new Exception(__f('The parameter %s must not be empty', 'id'));
        }

        if ($field == 'id' && !is_numeric($id)) {
            throw new Exception(__f('The parameter %s must be numeric', 'id'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        if ($transformFunc) {
            $where = "$transformFunc($fieldName) = '" . DataUtil::formatForStore($id) . "'";
        } else {
            $where = $fieldName . " = '" . DataUtil::formatForStore($id) . "'";
        }

        return self::selectObjectCount($table, $where, $field);
    }

    /**
     * Select & return an expanded field array.
     *
     * @param string  $table            The treated table reference.
     * @param array   $joinInfo         The array containing the extended join information.
     * @param string  $field            The name of the field we wish to marshall.
     * @param string  $where            The where clause (optional) (default='').
     * @param string  $orderby          The orderby clause (optional) (default='').
     * @param boolean $distinct         Whether or not to add a 'DISTINCT' clause (optional) (default=false).
     * @param string  $assocKey         The key field to use to build the associative index (optional) (default='').
     * @param string  $permissionFilter The permission filter to use for permission checking (optional) (default=null).
     * @param integer $limitOffset      The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows     The upper limit bound (optional) (default=-1).
     *
     * @return The resulting field array.
     */
    public static function selectExpandedFieldArray($table, $joinInfo, $field, $where = '', $orderby = '', $distinct = false, $assocKey = '', $permissionFilter = null, $limitOffset = -1, $limitNumRows = -1)
    {
        $key = $field . $where . $orderby . $distinct . $assocKey . serialize($joinInfo) . serialize($permissionFilter);
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        self::_setFetchedObjectCount(0);

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = isset($columns[$field]) ? 'tbl.'.$columns[$field] : $field;

        $sqlJoinArray = self::_processJoinArray($table, $joinInfo);
        $sqlJoin = $sqlJoinArray[0];
        $sqlJoinFieldList = $sqlJoinArray[1];

        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $dSql = ($distinct ? "DISTINCT($fieldName)" : "$fieldName");
        $sqlStart = "SELECT $dSql ";
        $sqlFrom = "FROM $tableName AS tbl ";

        $sql = "$sqlStart $sqlJoinFieldList $sqlFrom $sqlJoin $where $orderby";

        $res = self::executeSQL($sql, $limitOffset, $limitNumRows);

        if ($res === false) {
            return $res;
        }

        $fields = self::marshallFieldArray($res, true, $assocKey);
        self::setCache($table, $key, $fields);

        return $fields;
    }

    /**
     * Select & return a object with it's left join fields filled in.
     *
     * @param string $table            The treated table reference.
     * @param array  $joinInfo         The array containing the extended join information.
     * @param string $where            The where clause (optional).
     * @param array  $columnArray      The columns to marshall into the resulting object (optional) (default=null).
     * @param string $permissionFilter The permission structure to use for permission checking (optional) (default=null).
     * @param string $categoryFilter   The category list to use for filtering (optional) (default=null).
     *
     * @return array The resulting object.
     */
    public static function selectExpandedObject($table, $joinInfo, $where = '', $columnArray = null, $permissionFilter = null, $categoryFilter = null)
    {
        $objects = self::selectExpandedObjectArray($table, $joinInfo, $where, '', 0, 1, '', $permissionFilter, $categoryFilter, $columnArray);

        if (count($objects)) {
            return $objects[0];
        }

        return $objects;
    }

    /**
     * Select & return an object by it's ID  with it's left join fields filled in.
     *
     * @param string  $table            The treated table reference.
     * @param array   $joinInfo         The array containing the extended join information.
     * @param integer $id               The ID value to use for object retrieval.
     * @param string  $field            The field key which holds the ID value (optional) (default='id').
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null).
     * @param string  $permissionFilter The permission structure to use for permission checking (optional) (default=null).
     * @param string  $categoryFilter   The category list to use for filtering (optional) (default=null).
     * @param string  $transformFunc    Transformation function to apply to $id (optional) (default=null).
     *
     * @return array The resulting object.
     */
    public static function selectExpandedObjectByID($table, $joinInfo, $id, $field = 'id', $columnArray = null, $permissionFilter = null, $categoryFilter = null, $transformFunc = null)
    {
        $tables = self::getTables();
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        if ($transformFunc) {
            $where = "tbl.$transformFunc($fieldName) = '" . DataUtil::formatForStore($id) . "'";
        } else {
            $where = "tbl.$fieldName = '" . DataUtil::formatForStore($id) . "'";
        }

        $object = self::selectExpandedObject($table, $joinInfo, $where, $columnArray, $permissionFilter, $categoryFilter);

        return $object;
    }

    /**
     * Select & return an array of objects with it's left join fields filled in.
     *
     * @param string  $table            The treated table reference.
     * @param array   $joinInfo         The array containing the extended join information.
     * @param string  $where            The where clause (optional) (default='').
     * @param string  $orderby          The order by clause (optional) (default='').
     * @param integer $limitOffset      The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows     The upper limit bound (optional) (default=-1).
     * @param string  $assocKey         The key field to use to build the associative index (optional) (default='').
     * @param string  $permissionFilter The permission filter to use for permission checking (optional) (default=null).
     * @param string  $categoryFilter   The category filter (optional) (default=null).
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null).
     * @param string  $distinct         Set if a "SELECT DISTINCT" should be performed.  default false.
     *
     * @return array The resulting object.
     */
    public static function selectExpandedObjectArray($table, $joinInfo, $where = '', $orderby = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '', $permissionFilter = null, $categoryFilter = null, $columnArray = null, $distinct = false)
    {
        $key = serialize($joinInfo) . $where . $orderby . $limitOffset . $limitNumRows . serialize($assocKey) . serialize($permissionFilter) . serialize($categoryFilter) . serialize($columnArray) . ($distinct ? '1' : '0');
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        self::_setFetchedObjectCount(0);

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];

        $sqlStart = "SELECT " . ($distinct ? 'DISTINCT ' : '') . self::_getAllColumnsQualified($table, 'tbl', $columnArray);
        $sqlFrom = "FROM $tableName AS tbl ";

        $sqlJoinArray = self::_processJoinArray($table, $joinInfo, $columnArray);
        $sqlJoin = $sqlJoinArray[0];
        $sqlJoinFieldList = $sqlJoinArray[1];
        $ca = null; //$sqlJoinArray[2]; -- edited by Drak, this causes errors if set.

        $usesJoin = (count($joinInfo) > 0) ? true : false;

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter, false, $usesJoin);

        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $objects = array();
        $sql = "$sqlStart $sqlJoinFieldList $sqlFrom $sqlJoin $where $orderby";

        do {
            $fetchedObjectCount = self::_getFetchedObjectCount();
            $stmt = $sql;
            $limitOffset += $fetchedObjectCount;

            $res = self::executeSQL($stmt, $limitOffset, $limitNumRows);
            if ($res === false) {
                return $res;
            }

            $objArr = self::marshallObjects($res, $ca, true, $assocKey, true, $permissionFilter);
            $fc = self::_getFetchedObjectCount();
            if ($objArr) {
                $objects = $objects + (array)$objArr; // append new array
            }
        } while ($permissionFilter && ($limitNumRows != -1 && $limitNumRows > 0) && $fetchedObjectCount > 0 && count($objects) < $limitNumRows);

        if (count($objects) > $limitNumRows && $limitNumRows > 0) {
            $objects = array_slice($objects, 0, $limitNumRows);
        }

        $idFieldName = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';

        $objects = self::_selectPostProcess($objects, $table, $idFieldName);

        self::setCache($table, $key, $objects);

        return $objects;
    }

    /**
     * Return the number of rows affected.
     *
     * @param string  $table          The treated table reference.
     * @param array   $joinInfo       The array containing the extended join information.
     * @param string  $where          The where clause (optional) (default='').
     * @param boolean $distinct       Whether or not to count distinct entries (optional) (default='false') turned off as fix for http://code.zikula.org/core/ticket/49, not supported in SQL).
     * @param string  $categoryFilter The category list to use for filtering (optional) (default=null).
     *
     * @return integer The resulting object count.
     */
    public static function selectExpandedObjectCount($table, $joinInfo, $where = '', $distinct = false, $categoryFilter = null)
    {
        self::_setFetchedObjectCount(0);

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];

        $sqlJoinArray = self::_processJoinArray($table, $joinInfo);
        $sqlJoin = $sqlJoinArray[0];
        $sqlJoinFieldList = $sqlJoinArray[1];

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter, false, true);
        $where = self::_checkWhereClause($where);
        //$dst = ($distinct ? 'DISTINCT' : '');
        $sqlStart = "SELECT COUNT(*) ";
        $sqlFrom = "FROM $tableName AS tbl ";

        $sql = "$sqlStart $sqlJoinFieldList $sqlFrom $sqlJoin $where";
        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }
    
        $count = false;
        $res   = $res->fetchAll(Doctrine::FETCH_COLUMN);
        if ($res && isset($res[0])) {
            $count = $res[0];
        }

        return $count;
    }

    /**
     * Joining string creation.
     *
     * This method creates the necessary sql information for retrieving
     * fields from joined tables defined by a joinInfo array described
     * at the top of this class.
     *
     * @param string $table       The treated table reference.
     * @param array  $joinInfo    The array containing the extended join information.
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null).
     *
     * @return array $sqlJoin, $sqlJoinFieldList, $ca.
     * @deprecated
     * @see    Doctrine_Record
     */
    private static function _processJoinArray($table, $joinInfo, $columnArray = null)
    {
        $tables = self::getTables();
        $columns = $tables["{$table}_column"];

        $allowedJoinMethods = array('LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN');

        $ca = self::getColumnsArray($table, $columnArray);
        $alias = 'a';
        $sqlJoin = '';
        $sqlJoinFieldList = '';
        foreach (array_keys($joinInfo) as $k) {
            $jt = $joinInfo[$k]['join_table'];
            $jf = $joinInfo[$k]['join_field'];
            $ofn = $joinInfo[$k]['object_field_name'];
            $cft = isset($joinInfo[$k]['compare_field_table']) ? $joinInfo[$k]['compare_field_table'] : null;
            $cfj = isset($joinInfo[$k]['compare_field_join'])  ? $joinInfo[$k]['compare_field_join']  : null;
            $jw  = isset($joinInfo[$k]['join_where'])          ? $joinInfo[$k]['join_where']          : null;

            $joinMethod = 'LEFT JOIN';
            if (isset($joinInfo[$k]['join_method']) && in_array(strtoupper($joinInfo[$k]['join_method']), $allowedJoinMethods)) {
                $joinMethod = $joinInfo[$k]['join_method'];
            }

            $jtab = $tables[$jt];
            $jcol = $tables["{$jt}_column"];

            if (!is_array($jf)) {
                $jf = array($jf);
            }

            if (!is_array($ofn)) {
                $ofn = array($ofn);
            }

            // loop over all fields to select from the joined table
            foreach ($jf as $k => $v) {
                $currentColumn = $jcol[$v];
                // attempt to remove encoded table name in column list used by some tables
                $t = strstr($currentColumn, '.');
                if ($t !== false) {
                    $currentColumn = substr($t, 1);
                }

                $line = ", $alias.$currentColumn AS \"$ofn[$k]\" ";
                $sqlJoinFieldList .= $line;

                $ca[] = $ofn[$k];
            }

            if ($jw) {
                $line = ' ' . $joinMethod . " $jtab $alias ON $jw ";
            } else {
                $compareColumn = $jcol[$cfj];
                // attempt to remove encoded table name in column list used by some tables
                $t = strstr($compareColumn, '.');
                if ($t !== false) {
                    $compareColumn = substr($t, 1);
                }

                $t = isset($columns[$cft]) ? "tbl.$columns[$cft]" : $cft; // if not a column reference assume litereal column name
                $line = ' ' . $joinMethod . " $jtab $alias ON $alias.$compareColumn = $t ";
            }

            $sqlJoin .= $line;
            ++$alias;
        }

        return array($sqlJoin, $sqlJoinFieldList, $ca);
    }

    /**
     * Post-processing for selected objects.
     *
     * This routine is responsible for reading the 'extra' data
     * (attributes, categories, and meta data) from the database and inserting the relevant sub-objects into the object.
     *
     * @param array   $objects     The object-array or the object we just selected.
     * @param string  $table       The treated table reference.
     * @param integer $idFieldName The id column for the object/table combination.
     *
     * @return array the object with it's relevant sub-objects set.
     *
     * @deprecated
     * @see    CategorisableListener, AttributableListener, MetaDataListener
     */
    public static function _selectPostProcess($objects, $table, $idFieldName)
    {
        // nothing to do if objects is empty
        if (is_array($objects) && count($objects) == 0) {
            return $objects;
        }

        $tables = self::getTables();
        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_categorization"]) && $tables["{$table}_db_extra_enable_categorization"])) && System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') && strcmp($table, 'categories_') !== 0 && strcmp($table, 'objectdata_attributes') !== 0 && strcmp($table, 'objectdata_log') !== 0 && ModUtil::available('Categories')) {
            if (is_array($objects)) {
                $ak = array_keys($objects);
                if ($ak && is_array($objects[$ak[0]])) {
                    ObjectUtil::expandObjectArrayWithCategories($objects, $table, $idFieldName);
                } else {
                    ObjectUtil::expandObjectWithCategories($objects, $table, $idFieldName);
                }
            }
        }

        // temporary hack to prevent recursive loop because available() calls selectObjectArray again (Guite)
        if ($table == 'modules') {
            return $objects;
        }

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_attribution"]) && $tables["{$table}_db_extra_enable_attribution"]) || System::getVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) && strcmp($table, 'objectdata_attributes') !== 0 && strcmp($table, 'objectdata_log') !== 0) {
            if (is_array($objects)) {
                $ak = array_keys($objects);
                if ($ak && is_array($objects[$ak[0]])) {
                    foreach ($ak as $k) {
                        ObjectUtil::expandObjectWithAttributes($objects[$k], $table, $idFieldName);
                    }
                } else {
                    ObjectUtil::expandObjectWithAttributes($objects, $table, $idFieldName);
                }
            }
        }

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_meta"]) && $tables["{$table}_db_extra_enable_meta"]) || System::getVar('Z_CONFIG_USE_OBJECT_META')) && strcmp($table, 'objectdata_attributes') !== 0 && strcmp($table, 'objectdata_meta') !== 0 && strcmp($table, 'objectdata_log') !== 0) {
            if (is_array($objects)) {
                $ak = array_keys($objects);
                if ($ak && is_array($objects[$ak[0]])) {
                    foreach ($ak as $k) {
                        ObjectUtil::expandObjectWithMeta($objects[$k], $table, $idFieldName);
                    }
                } else {
                    ObjectUtil::expandObjectWithMeta($objects, $table, $idFieldName);
                }
            }
        }

        return $objects;
    }

    /**
     * Select & return an object array based on a table definition using the given SQL statement.
     *
     * @param string  $sql              The sql statement to execute for the selection.
     * @param string  $table            The treated table reference.
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null).
     * @param string  $permissionFilter The permission filter to use for permission checking (optional) (default=null).
     * @param integer $limitOffSet      The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows     The upper limit bound (optional) (default=-1).
     *
     * @return array The resulting object array.
     */
    public static function selectObjectArraySQL($sql, $table, $columnArray = null, $permissionFilter = null, $limitOffSet = -1, $limitNumRows = -1)
    {
        $key = $sql . serialize($columnArray) . serialize($permissionFilter) . $limitOffSet . $limitNumRows;
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        $res = self::executeSQL($sql, $limitOffSet, $limitNumRows);
        if ($res === false) {
            return $res;
        }

        $ca = null; //Note required since Zikula 1.3.0 because of PDO::fetchAll() see #2227 //self::getColumnsArray($table, $columnArray);
        $objArr = self::marshallObjects($res, $ca, true, '', true, $permissionFilter);
        self::setCache($table, $key, $objArr);

        return $objArr;
    }

    /**
     * Returns the last inserted ID.
     *
     * @param string  $table       The treated table reference.
     * @param string  $field       The field to use.
     * @param boolean $exitOnError Exit on error.
     * @param boolean $verbose     Verbose mode.
     *
     * @return intiger   The result ID.
     * @throws Exception IF table does not point to valid table definition, or field does not point to valif field def.
     */
    public static function getInsertID($table, $field = 'id', $exitOnError = true, $verbose = true)
    {
        $tables = self::getTables();
        $tableName = $tables[$table];
        $column = $tables["{$table}_column"];
        $fieldName = $column[$field];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        if (empty($fieldName)) {
            throw new Exception(__f('%s does not point to a valid field definition', $field));
        }

        $resultID = 0;
        try {
            if (!$resultID = Doctrine_Manager::getInstance()->getCurrentConnection()->lastInsertId($tableName, $fieldName)) {
                if ($exitOnError) {
                    throw new Exception(__('Exiting after SQL-error'));
                }
            }
        } catch (Exception $e) {
            return LogUtil::registerError('Database error: ' . $e->getMessage());
        }

        return $resultID;
    }

    /**
     * Get table definitions.
     *
     * Get the table definition for a database table. Convert the representation
     * from ADODB Datadict to Doctrine
     *
     * @param string $table Table to get adodb sql string for.
     *
     * @return array                    The table definition.
     * @throws Exception                If table parameter is empty.
     * @throws InvalidArgumentException If error in table definition.
     */
    public static function getTableDefinition($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $flag = false;
        $sql = '';

        // try to read table definitions from $table array if present
        $ddict = array();
        $tables = self::getTables();
        $tablecol = $table . '_column';
        $tabledef = $table . '_column_def';

        if (array_key_exists($tabledef, $tables) && is_array($tables[$tabledef])) {
            // we have a {$tablename}_column_def array as defined in tables.php. This is a real array, not a string.
            // The format is like "C(24) NOTNULL DEFAULT ''" which means we have to prepend the field name now
            $typemap = array(
                    'B' => 'blob',    // NOTE: not supported in Doctrine 2
                    'C' => 'string',
                    'C2' => 'blob',   // NOTE: not supported in Doctrine 2
                    'D' => 'date',
                    'F' => 'float',
                    'I' => 'integer',
                    'I1' => 'integer',
                    'I2' => 'integer',
                    'I4' => 'integer',
                    'I8' => 'integer',
                    'N' => 'number',
                    'L' => 'boolean',
                    'T' => 'timestamp',
                    'TS' => 'timestamp',
                    'X' => 'clob',
                    'X2' => 'blob', // NOTE: not supported in Doctrine 2
                    'XL' => 'clob');
            $iLengthMap = array(
                    'I' => 4, // maps to I4
                    'I1' => 1,
                    'I2' => 2,
                    'I4' => 4,
                    'I8' => 8);
            $search = array(
                    '+',
                    '-',
                    '*',
                    '/',
                    '%');
            $replace = array(
                    '');

            foreach ($tables[$tablecol] as $id => $val) {
                $hasMath = (bool)(strcmp($val, str_replace($search, $replace, $val)));
                if (!$hasMath && !isset($tables[$tabledef][$id])) {
                    throw new Exception(__f('Invalid field pattern detected in table [%s] ...', $table));
                }
                if ($hasMath) {
                    continue;
                }
                $fAuto = false;
                $fDef = null;
                $fLen = null;
                $fNull = null;
                $fPrim = false;
                $fType = null;
                $fUSign = false;
                $fScale = null;

                $clean = preg_replace('/\s\s+/', ' ', $tables[$tabledef][$id]);
                $fields = explode(' ', $clean);

                if (preg_match('#B|C2|X2#', $fields[0])) {
                    LogUtil::log(__('Warning! Table defintion type longblob [B, C2 and X2] is deprecated from Zikula 1.4.0.'), E_USER_DEPRECATED);
                }

                // parse type and length
                preg_match('#(B|D|C2|I1|I2|I4|I8|F|L|TS|T|X2|XL|X|(C|I)(?:\()(\d+)(?:\))|(N)(?:\()(\d+|\d+\.\d+)(?:\))|I)#', $fields[0], $matches);
                if (!$matches) {
                    throw new InvalidArgumentException(__f('Error in table definition for %1$s, column %2$s', array($table, $id)));
                }

                switch (count($matches)) {
                    case 2:
                        $type = $matches[1];
                        break;
                    case 4:
                        $type = $matches[2];
                        $fLen = $matches[3];
                        break;
                    case 6:
                        $type = $matches[4];
                        $p = explode('.', $matches[5]);
                        if (count($p) == 2) {
                            $fLen = $p[0];
                            $fScale = $p[1];
                        } else {
                            $fLen = $matches[5];
                        }
                        break;
                }

                // get field type
                if (isset($fScale)) {
                    $fType = 'decimal';
                } else {
                    $fType = $typemap[$type];
                }

                unset($fields[0]);

                // transform to Doctrine datadict representation
                for ($i = 1; $i <= count($fields); $i++) {
                    $fields[$i] = strtoupper($fields[$i]);
                    if ($fields[$i] == 'AUTO' || $fields[$i] == 'AUTOINCREMENT') {
                        $fAuto = true;
                    } elseif ($fields[$i] == 'PRIMARY') {
                        $fPrim = true;
                    } elseif ($fields[$i] == 'NOTNULL' || $fields[$i] == 'NULL') {
                        $fNull = $fields[$i];
                        if ($fAuto) {
                            $fNull = null;
                        }
                    } elseif ($fields[$i] == 'UNSIGNED') {
                        $fUSign = true;
                    } elseif ($fields[$i] == 'DEFAULT') {
                        if (!isset($fields[$i + 1])) {
                            throw new Exception(__f('Missing default value in field datadict specification for %1$s.%2$s', $table, $id));
                        }
                        for ($j = $i + 1; $j <= count($fields); $j++) {
                            if ($j > $i + 1) {
                                $fDef .= ' ';
                            }
                            $fDef .= str_replace(array(
                                            '"',
                                            "'"), array(
                                            '',
                                            ''), $fields[$j]);
                            if ($fDef == 'NULL') {
                                $fDef = '';
                            }
                        }
                    }
                }

                $fieldDef = array();
                $fieldDef['type'] = $fType;
                $fieldDef['length'] = (!$fLen && isset($iLengthMap[$type]) ? $iLengthMap[$type] : $fLen);

                if ($fType == 'decimal') {
                    $fieldDef['scale'] = $fScale;
                }

                $fieldDef['autoincrement'] = $fAuto;
                $fieldDef['primary'] = $fPrim;
                $fieldDef['unsigned'] = $fUSign;
                $fieldDef['notnull'] = ($fNull !== null && $fType != 'boolean' ? ($fNull == 'NOTNULL' ? true : false) : null);
                if ($fDef != null) {
                    $fieldDef['default'] = $fDef;
                }

                $ddict[$val] = $fieldDef;
            }

            return $ddict;
        } else {
            throw new Exception(__f('Neither the sql parameter nor the table structure contain the data dictionary representation of table [%s] ...', $table));
        }
    }

    /**
     * Get the table definition for a database table.
     *
     * @param string $table Table to get adodb sql string for.
     *
     * @return string
     * @throws Exception If the table parameter is empty.
     */
    public static function _getTableDefinition($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $flag = false;
        $sql = '';

        // try to read table definitions from $table array if present
        $tables = self::getTables();
        $tablecol = $table . '_column';
        $tabledef = $table . '_column_def';
        if (array_key_exists($tabledef, $tables) && is_array($tables[$tabledef])) {
            // we have a {$tablename}_column_def array as defined in tables.php. This is a real array, not a string.
            // The format is like "C(24) NOTNULL DEFAULT ''" which means we have to prepend the field name now
            foreach ($tables[$tablecol] as $id => $val) {
                if (!array_key_exists($id, $tables[$tabledef])) {
                    throw new Exception(__f('Invalid field pattern detected in table [%s] ...', $table));
                }
                if ($flag == true) {
                    $sql .= ', ';
                }
                $sql .= $val . ' ' . trim($tables[$tabledef][$id]);
                $flag = true;
            }

            return $sql;
        } else {
            throw new Exception(__f('Neither the sql parameter nor the table structure contain the ADODB dictionary representation of table [%s] ...', $table));
        }
    }

    /**
     * Get the constraints for a given table.
     *
     * @param string $table Treated table.
     *
     * @return string    Return string to get table constraints.
     * @throws Exception If the table parameter is empty or does not point to a valid table definition.
     */
    public static function getTableConstraints($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        //try {
        //    return Doctrine_Manager::getInstance()->getCurrentConnection()->import->listTableConstraints($tableName);
        //} catch (Exception $e) {
        //    return LogUtil::registerError(__('Error! Table constraints determination failed.') . ' ' . $e->getMessage());
        //}

        $tablecol = $table . '_column';
        $tableopt = $table . '_constraints';
        $tables = self::getTables();
        if (array_key_exists($tableopt, $tables) && is_array($tables[$tableopt])) {
            foreach ($tables[$tableopt] as $fk_column => $fk_reference) {
                $reference_table = $tables[$fk_reference['table']];
                $reference_column = $tables[$fk_reference['table'] . '_column'][$fk_reference['column']];
                $original_column = $tables[$tablecol][$fk_column];
                $constraints .= ", CONSTRAINT FOREIGN KEY($original_column) REFERENCES $reference_table ($reference_column) $fk_reference[accion]";
            }

            return $constraints;
        }
    }

    /**
     * Get table prefix.
     *
     * Gets the database prefix for the current site.
     * In a non multisite scenario this will be the 'prefix' config var
     * from config/config.php. For a multisite configuration the multistes
     * module will manage the prefixes for a given table.
     *
     * The table name parameter is the table name to get the prefix for
     * minus the prefix and seperating _
     * e.g. getTablePrefix returns z for tables z_modules with getTablePrefix('modules');
     *
     * @param string $table Table name.
     *
     * @return string Database prefix.
     */
    public static function getTablePrefix($table)
    {
        if (!isset($table)) {
            return false;
        }

        return System::getVar('prefix');
    }

    /**
     * Verify that column and column_def definition match.
     *
     * @param string $table The treated table reference.
     *
     * @return boolean
     * @throws Exception If the table parameter is empty or cannot retrieve table/column def for $table.
     */
    public static function verifyTableDefinitionConsistency($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = self::getTables();

        $tableName = isset($tables[$table]) ? $tables[$table] : null;
        $columns = isset($tables["{$table}_column"]) ? $tables["{$table}_column"] : null;
        $columnDefs = isset($tables["{$table}_column_def"]) ? $tables["{$table}_column_def"] : null;

        if (!$tableName) {
            throw new Exception(__f('Unable to retrieve table definition for [%s]', $table));
            //$success = LogUtil::registerError(__f('Unable to retrieve table definition for [%s]', $table));
        }

        if (!$columns) {
            throw new Exception(__f('Unable to retrieve table columns definition for [%s]', $table));
            //$success = LogUtil::registerError(__f('Unable to retrieve table columns definition for [%s]', $table));
        }

        if (!$columnDefs) {
            throw new Exception(__f('Unable to retrieve table columns_def definition for [%s]', $table));
            //$success = LogUtil::registerError(__f('Unable to retrieve table columns_def definition for [%s]', $table));
        }

        // verify that column and column_def definitions are consistent
        $search = array(
                '+',
                '-',
                '*',
                '/',
                '%');
        $replace = array(
                '');
        $success = true;
        foreach ($columns as $k => $v) {
            $hasMath = (bool)(strcmp($v, str_replace($search, $replace, $v)));
            if (!$hasMath) {
                if (!isset($columnDefs[$k])) {
                    throw new Exception(__f('Inconsistent table definition detected for table [%1$s]: column [%2$s] has no counterpart in column_def structure', array(
                                    $table,
                                    $k)));
                    //$success = LogUtil::registerError(__f('Inconsistent table definition detected for table [%1$s]: column [%2$s] has no counterpart in column_def structure', array($table, $k)));
                }
            }
        }
        foreach ($columnDefs as $k => $v) {
            if (!isset($columns[$k])) {
                throw new Exception(__f('Inconsistent table definition detected for table [%1$s]: column_def [%2$s] has no counterpart in column structure', array(
                                $table,
                                $k)));
                //$success = LogUtil::registerError(__f('Inconsistent table definition detected for table [%1$s]: column_def [%2$s] has no counterpart in column structure', array($table, $k)));
            }
        }

        return true;
    }

    /**
     * Create a database table.
     *
     * @param string $table      Tablename key for the tables structure.
     * @param array  $definition Doctrine table definition array.
     * @param array  $tabopt     Table options specific to this table (optional).
     *
     * @throws Exception On error.
     *
     * @return boolean True on success, false of failure.
     */
    public static function createTable($table, $definition = null, $tabopt = null)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $success = self::verifyTableDefinitionConsistency($table);
        if (!$success) {
            throw new Exception(__f('Table consistency check failed for %s', $table));

            return false;
        }

        $connection = Doctrine_Manager::getInstance()->getCurrentConnection();

        if (empty($definition) || !is_array($definition)) {
            $definition = self::getTableDefinition($table);
            if (!$definition) {
                throw new Exception(__f('Neither the sql parameter nor the table array contain the dictionary representation of table [%s]', array($table)));
            }
        }

        if (!isset($tabopt) || empty($tabopt)) {
            $tabopt = self::getTableOptions($table);
        }
        $tabopt['constraints'] = self::getTableConstraints($table);

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        try {
            $connection->export->createTable($tableName, $definition, $tabopt);
        } catch (Exception $e) {
            return LogUtil::registerError(__f('Error! Table creation failed for %s', $tableName) . ' ' . $e->getMessage());
        }

        // create additional indexes
        $tableIndex = $table . '_column_idx';
        if (array_key_exists($tableIndex, $tables) && is_array($tables[$tableIndex])) {
            foreach ($tables[$tableIndex] as $indexName => $indexDefinition) {
                if (is_array($indexDefinition) && isset($indexDefinition['columns'])) {
                    if (isset($indexDefinition['options'])) {
                        self::createIndex($indexName, $table, $indexDefinition['columns'], $indexDefinition['options']);
                    } else {
                        self::createIndex($indexName, $table, $indexDefinition['columns']);
                    }
                } else {
                    self::createIndex($indexName, $table, $indexDefinition);
                }
            }
        }

        return true;
    }

    /**
     * Change database table using Doctrine dictionary method.
     *
     * Please note this method does not handle column renaming.  Renames should
     * be handled by first calling this method with $dropColums = false so that data
     * can then be copied to the new columns, before calling the method again with
     * $dropColumns = true to cleanup the old columns.
     *
     * @param string  $table       Table key in pntables.
     * @param array   $definition  Table definition (default = null).
     * @param array   $tabopt      Table options.
     * @param booleam $dropColumns Drop columns if they don't exist in new schema (default = false).
     *
     * @return boolean
     * @throws Exception If the $table parameter is empty or failed consistency check.
     */
    public static function changeTable($table, $definition = null, $tabopt = null, $dropColumns = false)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $success = self::verifyTableDefinitionConsistency($table);
        if (!$success) {
            throw new Exception(__f('Table consistency check failed for %s', $table));

            return false;
        }

        $connection = Doctrine_Manager::getInstance()->getCurrentConnection();

        if (empty($definition) || !is_array($definition)) {
            $definition = self::getTableDefinition($table);
            if (!$definition) {
                throw new Exception(__f('Neither the sql parameter nor the table array contain the dictionary representation of table [%s]', array($table)));
            }
        }

        if (!isset($tabopt) || empty($tabopt)) {
            $tabopt = self::getTableOptions();
        }
        $tabopt['constraints'] = self::getTableConstraints($table);

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        $metaColumns = self::metaColumnNames($table);

        // first round - create any missing columns
        foreach ($definition as $key => $columnDefinition) {
            if (isset($metaColumns[$key])) {
                continue;
            }
            $alterTableDefinition = array('add' => array($key => $columnDefinition));
            try {
                $connection->export->alterTable($tableName, $alterTableDefinition);
            } catch (Exception $e) {
                return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
            }
        }

        // second round, alter table structures to match new tables definition.
        foreach ($definition as $key => $columnDefinition) {
            $alterTableDefinition = array('change' => array($key => array('definition' => $columnDefinition)));
            try {
                $connection->export->alterTable($tableName, $alterTableDefinition);
            } catch (Exception $e) {
                return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
            }
        }

        // third round - removes non existing columns in the model.
        if ($dropColumns) {
            foreach (array_keys($metaColumns) as $key) {
                if (array_key_exists($key, $metaColumns)) {
                    continue;
                }
                $alterTableDefinition = array('remove' => array($key => array()));
                try {
                    $connection->export->alterTable($tableName, $alterTableDefinition);
                } catch (Exception $e) {
                    return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
                }
            }
        }

        // drop all indexes
        $indexes = self::metaIndexes($table);
        foreach ($indexes as $index) {
            try {
                $connection->export->dropIndex($tableName, $index);
            } catch (Exception $e) {
                return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
            }
        }

        // create additional indexes
        $tableIndex = $table . '_column_idx';
        if (array_key_exists($tableIndex, $tables) && is_array($tables[$tableIndex])) {
            $indexes = self::metaIndexes($table);
            foreach ($tables[$tableIndex] as $indexName => $indexDefinition) {
                if (!isset($indexes[$indexName])) {
                    if (is_array($indexDefinition) && isset($indexDefinition['columns'])) {
                        if (isset($indexDefinition['options'])) {
                            self::createIndex($indexName, $table, $indexDefinition['columns'], $indexDefinition['options']);
                        } else {
                            self::createIndex($indexName, $table, $indexDefinition['columns']);
                        }
                    } else {
                        self::createIndex($indexName, $table, $indexDefinition);
                    }
                }
            }
        }

        self::flushCache($table);

        return true;
    }

    /**
     * Truncate database table.
     *
     * @param string $table Table a tablename key for the tables structure.
     *
     * @return boolean
     * @throws Exception If the $table param is empty or does not point to a valid table definition.
     */
    public static function truncateTable($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        $sql = 'DELETE FROM ' . $tableName;
        $res = self::executeSQL($sql);

        if ($res === false) {
            return $res;
        }

        self::flushCache($table);

        return $res;
    }

    /**
     * Rename a database table.
     *
     * @param string $table    Table a tablename key for the tables structure.
     * @param string $newTable NewTable a tablename key for the tables structure.
     *
     * @return boolean
     * @throws Exception If the $table or $newTable parameter is empty, or do not point to valid definitons.
     */
    public static function renameTable($table, $newTable)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }
        if (empty($newTable)) {
            throw new Exception(__f('The parameter %s must not be empty', 'newTable'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];
        $newTableName = $tables[$newTable];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        if (empty($newTableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $newTable));
        }

        try {
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->alterTable($tableName, array('name' => $newTableName));
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Table rename failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);

        return true;
    }

    /**
     * Delete a database table.
     *
     * @param string $table Table a tablename key for the tables structure.
     *
     * @return boolean
     * @throws Exception If the $table parameter is empty or does not point to valid table definition.
     */
    public static function dropTable($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        try {
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->dropTable($tableName);
            ObjectUtil::deleteAllObjectTypeAttributes($table);
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Table drop failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);

        return true;
    }

    /**
     * Create index on table.
     *
     * @param string       $idxname     Name of index.
     * @param string       $table       The treated table reference.
     * @param array|string $flds        String field name, or non-associative array of field names.
     * @param array        $idxoptarray Array of UNIQUE=true.
     *
     * @return boolean
     * @throws Exception If $idxname, $table, or $flds paramters are empty.
     */
    public static function createIndex($idxname, $table, $flds, $idxoptarray = false)
    {
        if (empty($idxname)) {
            throw new Exception(__f('The parameter %s must not be empty', 'idxname'));
        }

        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        if (empty($flds)) {
            throw new Exception(__f('The parameter %s must not be empty', 'flds'));
        }

        if (!empty($idxoptarray) && !is_array($idxoptarray)) {
            throw new Exception(__f('The parameter %s must be an array', 'idxoptarray'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];
        $column = $tables["{$table}_column"];

        if (empty($column)) {
            throw new Exception(__f('%s does not point to a valid column definition', $column));
        }

        $indexFields = array();
        if (!is_array($flds)) {
            $indexFields[$column[$flds]] = array();
        } else {
            foreach ($flds as $fld) {
                if (is_array($fld)) {
                    // this adds support to specifying index lengths in your pntables. So you can say
                    // $flds[] = array('path', 100);
                    // $flds[] = array('name', 10);
                    // $idxoptarray['UNIQUE'] = true;
                    // self::createIndex($idxname, $table, $flds, $idxoptarray);
                    $indexFields[$column[$fld]] = array();
                    // TODO - implement what is described in the above comment!
                } else {
                    $indexFields[$column[$fld]] = array();
                }
            }
        }

        $indexDefinition = array(
                'fields' => $indexFields,
        );

        if (!empty($idxoptarray) && is_array($idxoptarray)) {
            if (isset($idxoptarray['UNIQUE']) && $idxoptarray['UNIQUE']) {
                $indexDefinition['type'] = 'unique';
            }
        }

        try {
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->createIndex($tableName, $idxname, $indexDefinition);

            return true;
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Index creation failed.') . ' ' . $e->getMessage());
        }
    }

    /**
     * Drop index on table.
     *
     * @param string $idxname Index name.
     * @param string $table   The treated table reference.
     *
     * @return boolean
     * @throws Exception If any parameter is empty, table does not point to a valid definition.
     */
    public static function dropIndex($idxname, $table)
    {
        if (empty($idxname)) {
            throw new Exception(__f('The parameter %s must not be empty', 'idxname'));
        }

        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $tableName));
        }

        try {
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->dropIndex($tableName, $idxname);

            return true;
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Index deletion failed.') . ' ' . $e->getMessage());
        }
    }

    /**
     * Get a list of columns in a table.
     *
     * @param string  $table            The treated table reference.
     * @param boolean $assoc            Associative meta column names?.
     * @param boolean $notcasesensitive Normalize case of table name.
     *
     * @return array of column objects.
     */
    public static function metaColumns($table, $assoc = false, $notcasesensitive = true)
    {
        $rows = self::metaColumnNames($table, $assoc);
        $array = array();
        if ($notcasesensitive) {
            foreach ($rows as $key => $row) {
                $array[strtolower($key)] = $row;
            }

            return $array;
        }

        return $rows;
    }

    /**
     * Get a list of column names in a table.
     *
     * @param string  $table        Table The treated table reference.
     * @param boolean $numericIndex Use numeric keys.
     *
     * @return array     Array of column names.
     * @throws Exception If the table param is empty or does not point to a valid table definition.
     */
    public static function metaColumnNames($table, $numericIndex = false)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        $rows = Doctrine_Manager::getInstance()->getCurrentConnection()->import->listTableColumns($tableName);
        $array = array();
        if ($numericIndex) {
            foreach ($rows as $row) {
                $array[] = $row;
            }

            return $array;
        }

        return $rows;
    }

    /**
     * Get a list of indexes for a table.
     *
     * @param string  $table   The treated table reference.
     * @param boolean $primary Show only primary keys.
     *
     * @return array     Array of column names.
     * @throws Exception If the table parameter is empty or does not point to a valid table definition.
     */
    public static function metaIndexes($table, $primary = false)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        try {
            // Using array_unique here because Doctrine is sometimes returning a duplicate of the last index key - drak refs #2676
            return array_unique(Doctrine_Manager::getInstance()->getCurrentConnection()->import->listTableIndexes($tableName));
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Fetching table index list failed.') . ' ' . $e->getMessage());
        }
    }

    /**
     * Limit the table name if necessary and prepend the prefix.
     *
     * When using Oracle the object name may not be longer than 30 chars. Now ADODB uses TRIGGERS and SEQUENCEs to emulate the AUTOINCREMENT
     * which eats up to 9 chars (TRIG_SEQ_<prefix>_<tablename>) so we have to limit the length of the table name to
     * 30 - 9 - length(prefix) - separator.
     * We use this function as a central point to shorten table name (there might be restrictions in ' other RDBMS too). If the resulting tablename is
     * empty we will show an error. In this case the prefix is too long.
     *
     * @param string $table        The treated table reference.
     * @param string $dbDriverName The driver used for this DB (optional).
     *
     * @deprecated
     * @see    Doctrines DBAL layer.
     *
     * @return boolean
     */
    public static function getLimitedTablename($table, $dbDriverName = '')
    {
        if (!$dbDriverName) {
            $dbDriverName = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());
        }

        $prefix = self::getTablePrefix($table);

        switch ($dbDriverName) {
            case 'oracle': // Oracle
                $maxlen = 30; // max length for a tablename
                $_tablename = $table; // save for later if we need to show an error
                $lenTable = strlen($table);
                $lenPrefix = strlen($prefix);
                // 10 for length of TRIG_SEQ_ + _
                if ($lenTable + $lenPrefix + 10 > $maxlen) {
                    $table = substr($table, 0, $maxlen - 10 - $lenPrefix); // same as 20-strlen(), but easier to understand :-)
                }
                if (empty($table)) {
                    return z_exit(__f('%1$s: unable to limit tablename [%2$s] because database prefix is too long for Oracle, please shorten it (recommended length is 4 chars)', array(
                                    __CLASS__ . '::' . __FUNCTION__,
                                    DataUtil::formatForDisplay($_tablename))));
                }
                break;

            default: // no action necessary, use tablename as is
                break;
        }

        // finally build the tablename
        $tablename = $prefix ? $prefix . '_' . $table : $table;

        return $tablename;
    }

    /**
     * Build a Doctrine Model class dynamically to allow pntable based modules to use DQL
     *
     * @param string $table     Table to use.
     * @param string $className Name of the class to load (default=null which generates {$table}_DBUtilRecord).
     *
     * @return string The model class.
     */
    public static function buildDoctrineModuleClass($table, $className=null)
    {
        $className = (is_null($className) ? "{$table}_DBUtilRecord" : $className);

        $def = self::getTableDefinition($table);
        $opt = self::getTableOptions($table);

        $tables = self::getTables();
        $columns = $tables["{$table}_column"];
        $columns = array_flip($columns);

        $hasColumns = '';
        foreach ($def as $columnName => $array) {
            $columnAlias = $columns[$columnName];
            $type = $array['type'];
            $length = (is_null($array['length']) ? 'null' : $array['length']);
            unset($array['type']);
            unset($array['length']);
            $array = array_filter($array);
            $array = !empty($array) ? ', ' . var_export($array, true) : null;
            $length = (!empty($array) || $length != 'null') ? ", $length" : '';
            $hasColumns .= "\$this->hasColumn('$columnName as $columnAlias', '$type'{$length}{$array});\n";
        }

        $options = '';
        foreach ($opt as $k => $v) {
            if (in_array($k, array('type', 'charset', 'collate'))) {
                continue;
            }
            $options .= "\$this->option('$k', '$v');\n";
        }
        // generate the model class:
        $class = "
class {$className} extends Doctrine_Record
{
    public function setTableDefinition()
    {
        \$this->setTableName('$table');
        $hasColumns
        $options
    }
}

class {$className}Table extends Doctrine_Table {}
";

        return $class;
    }

    /**
     * Include dynamically created Doctrine Model class into runtime environment
     *
     * @param string $table     The table.
     * @param string $className Name of the class to load (default=null which generates {$table}_DBUtilRecord).
     *
     * @return void
     */
    public static function loadDBUtilDoctrineModel($table, $className=null)
    {
        // don't double load
        $className = (is_null($className) ? "{$table}_DBUtilRecord" : $className);
        if (class_exists($className, false)) {
            return;
        }
        $code = self::buildDoctrineModuleClass($table, $className);
        eval($code);
    }

}
