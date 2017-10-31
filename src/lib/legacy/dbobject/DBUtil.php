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
 * DBUtil is the database abstraction class of Zikula.
 *
 * @deprecated
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
     * @param string $tablename The Zikula tablename
     *
     * @return true/false
     */
    public static function hasObjectCache($tablename)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!self::$cache_enabled) {
            self::$cache_enabled = ServiceUtil::getManager()->getParameter('dbcache.enable');
        }

        return 'session_info' != $tablename && ServiceUtil::getManager()->getParameter('installed') && self::$cache_enabled;
    }

    /**
     * Get the cache.
     *
     * @param string $table Table name
     * @param string $key   Key choise
     *
     * @return mixed Return the cache
     */
    public static function getCache($table, $key)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (self::hasObjectCache($table)) {
            $key = md5($key);
            $databases = ServiceUtil::getManager()->getParameter('databases');
            $connName = Doctrine_Manager::getInstance()->getCurrentConnection()->getName();
            $prefix = md5(serialize($databases[$connName]));
            $cacheDriver = ServiceUtil::getManager()->get('doctrine.cachedriver');

            return $cacheDriver->fetch($prefix . $table . $key);
        }

        return false;
    }

    /**
     * Set the cache.
     *
     * @param string $table  Table name
     * @param string $key    Key choise
     * @param string $fields Fields to cache
     *
     * @return void
     */
    public static function setCache($table, $key, $fields)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (self::hasObjectCache($table)) {
            $key = md5($key);
            $databases = ServiceUtil::getManager()->getParameter('databases');
            $connName = Doctrine_Manager::getInstance()->getCurrentConnection()->getName();
            $prefix = md5(serialize($databases[$connName]));
            $cacheDriver = ServiceUtil::getManager()->get('doctrine.cachedriver');
            $cacheDriver->save($prefix . $table . $key, $fields);
        }
    }

    /**
     * Flush the cache.
     *
     * @param string $table Table name
     *
     * @return void
     */
    public static function flushCache($table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (self::hasObjectCache($table)) {
            $databases = ServiceUtil::getManager()->getParameter('databases');
            $connName = Doctrine_Manager::getInstance()->getCurrentConnection()->getName();
            $prefix = md5(serialize($databases[$connName]));
            $cacheDriver = ServiceUtil::getManager()->get('doctrine.cachedriver');
            $cacheDriver->deleteByPrefix($prefix . $table);
        }
    }

    /**
     * Return server information.
     *
     * @return array Array of server info
     */
    public static function serverInfo()
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $connection = Doctrine_Manager::getInstance()->getCurrentConnection();

        // we will form an array to keep formally compatible to the old ado-db style for now
        return [
            'description' => $connection->getAttribute(PDO::ATTR_SERVER_INFO),
            'version' => $connection->getAttribute(PDO::ATTR_CLIENT_VERSION)
        ];
    }

    /**
     * Create database.
     *
     * @param string  $dbname       The database name
     * @param boolean $optionsarray The options array
     *
     * @return boolean
     * @throws Exception If the dbname is empty
     */
    public static function createDatabase($dbname, $optionsarray = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
     * @return array Array of databases
     */
    public static function metaDatabases()
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        return Doctrine_Manager::getInstance()->getCurrentConnection()->import->listDatabases();
    }

    /**
     * Get a list of tables in the currently connected database.
     *
     * @param boolean $ttype      Type of 'tables' to get
     * @param boolean $showSchema Add the schema name to the table
     * @param boolean $mask       Mask to apply to return result set
     *
     * @return array Array of tables
     */
    public static function metaTables($ttype = false, $showSchema = false, $mask = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        return Doctrine_Manager::getInstance()->getCurrentConnection()->import->listTables();
    }

    /**
     * Get a list of database tables.
     *
     * @return array array of database tables
     */
    public static function getTables()
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        return ServiceUtil::getManager()->getParameter('dbtables');
    }

    /**
     * Get a list of default dbms specific table options.
     *
     * This allows the default table options to be set in a modules's tables.php without
     * causing a circular dependency.
     *
     * @return array Return the default table options
     */
    public static function getDefaultTableOptions()
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableoptions = [];
        $serviceManager = ServiceUtil::getManager();

        $databases = $serviceManager['databases'];
        $connName = Doctrine_Manager::getInstance()->getCurrentConnection()->getName();
        $dbDriverName = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());
        if ('mysql' == $dbDriverName) {
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
     * the default options are returned otherwise
     *
     * @return array Return the table options
     */
    public static function getTableOptions($table = '')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if ('' != $table) {
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
     * @param string  $sql          The SQL statement to execute
     * @param integer $limitOffset  The lower limit bound (optional) (default=-1)
     * @param integer $limitNumRows The upper limit bound (optional) (default=-1)
     * @param boolean $exitOnError  Whether to exit on error (default=true) (optional)
     * @param boolean $verbose      Whether to be verbose (default=true) (optional)
     *
     * @return mixed     The result set of the successfully executed query or false on error
     * @throws Exception No SQL statment
     */
    public static function executeSQL($sql, $limitOffset = -1, $limitNumRows = -1, $exitOnError = true, $verbose = true)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!$sql) {
            throw new Exception(__('No SQL statement to execute'));
        }

        $connection = Doctrine_Manager::getInstance()->getCurrentConnection();

        if (!$connection && !\ServiceUtil::getManager()->getParameter('installed')) {
            return false;
        }

        try {
            if ($limitNumRows > 0) {
                $tStr = strtoupper(substr(trim($sql), 0, 7)); // Grab first 7 chars to allow syntax like "(SELECT" which may happen with UNION statements
                if (false === strpos($tStr, 'SELECT')) {
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
            //$stmt->setHydrationMode(Doctrine_Core::HYDRATE_RECORD);
            if ($stmt->execute()) {
                $result = $stmt;
            }

            if ($result) {
                // catch manual SQL which requires cache flushes
                $tab = null;
                $sql = strtolower(trim(preg_replace("/\s+/", " ", $sql)));
                if (0 === strpos($sql, 'update')) {
                    list(, $tab) = explode(' ', $sql);
                }
                if (0 === strpos($sql, 'delete')) {
                    list(, , $tab) = explode(' ', $sql);
                }
                if ($tab && false === strpos($tab, 'session_info')) {
                    self::flushCache($tab);
                }

                return $result;
            }
        } catch (Exception $e) {
            echo 'Error in DBUtil::executeSQL: ' . $sql . '<br />' . $e->getMessage() . '<br />';
            if ((System::isDevelopmentMode() && SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN))) {
                echo nl2br($e->getTraceAsString());
            }
            if ($exitOnError) {
                System::shutDown();
            }
        }

        return false;
    }

    /**
     * Transform a value for DB-storage-safe formatting, taking into account the columnt type.
     * Numeric values are not enclosed in single-quotes, anything else is.
     *
     * @param string $table   The treated table reference
     * @param string $field   The table field the value needs to be stored in
     * @param array  $value   The value which needs to be stored
     *
     * @return string    The generated sql string
     * @throws Exception If invalid table key retreived or empty query generated
     */
    public static function _typesafeQuotedValue($table, $field, $value)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables     = self::getTables();
        $columns    = $tables["{$table}_column"];
        $columnsDef = $tables["{$table}_column_def"];
        $fieldType  = $columnsDef[$field];
        $fieldTypes = explode(' ', $fieldType);
        $fieldType  = $fieldTypes[0];

        static $numericFields = null;
        if (!$numericFields) {
            $numericFields = ['I' => 'I', 'I1' => 'I1', 'I2' => 'I2', 'I4' => 'I4', 'I8' => 'I8', 'F' => 'F', 'L' => 'L', 'N' => 'N'];
        }

        if (isset($numericFields[$fieldType])) {
            if ('I' == $fieldType[0] || 'L' == $fieldType) {
                $value = (int)$value;
            } else {
                $value = (float)$value;
            }

            return DataUtil::formatForStore($value);
        }

        return "'" . DataUtil::formatForStore($value) . "'";
    }

    /**
     * Same as Api function but without AS aliasing.
     *
     * @param string $table       The treated table reference
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null)
     *
     * @return string    The generated sql string
     * @throws Exception If invalid table key retreived or empty query generated
     */
    public static function _getAllColumns($table, $columnArray = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $columns = $tables["{$table}_column"];
        if (!$columns) {
            throw new Exception(__f('Invalid table-key [%s] retrieved', $table));
        }

        $queriesResult = [];
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
     * @param string $table       The treated table reference
     * @param string $tablealias  The SQL table alias to use in the SQL statement
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null)
     *
     * @return The       generated sql string
     * @throws Exception If invalid table key retreived or empty query generated
     */
    public static function _getAllColumnsQualified($table, $tablealias, $columnArray = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $search = ['+', '-', '*', '/', '%'];
        $replace = [''];

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
     * @param string $table       The treated table reference
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null)
     *
     * @return The       column array for the given table
     * @throws Exception If empty query generated
     */
    public static function getColumnsArray($table, $columnArray = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $columnArrayResult = [];

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
     * @param array $columns  Column array
     * @param array $joinInfo JoinInfo array
     *
     * @return array Expanded column array
     * @deprecated
     * @see    Doctrine_Record
     * @throws Exception If invalid join information retrieved (an alias already exists)
     */
    public static function expandColumnsWithJoinInfo($columns, $joinInfo)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
            $jc = &$tables[$join['join_table'] . '_column'];
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
     * @param string $table      The treated table reference
     * @param string $oldcolumn  The existing name of the column (full database name of column)
     * @param string $newcolumn  The new name of the column from the pntables array
     * @param string $definition Field specific options (optional) (default=null)
     *
     * @return boolean
     * @throws Exception If parameters are empty
     */
    public static function renameColumn($table, $oldcolumn, $newcolumn, $definition = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
                throw new Exception(__f('Neither the sql parameter nor the table array contain the dictionary representation of table [%s]', [$table]));
            }
        }

        $renameColumnArray = [
            $oldcolumn => [
                'name' => $newcolumn,
                'definition' => $definition
            ]
        ];
        try {
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->alterTable($tableName, ['rename' => $renameColumnArray]);
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Column rename failed.') . ' ' . $e->getMessage());
        }
        self::flushCache($table);

        return true;
    }

    /**
     * Add column(s) to a table.
     *
     * @param string $table  The treated table reference
     * @param array  $fields Fields to add from the table
     *
     * @return boolean
     * @throws Exception                If parameters are empty
     * @throws InvalidArgumentException If field does not exist in table definition
     */
    public static function addColumn($table, array $fields)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
                    throw new InvalidArgumentException(__f('%1$s does not exist in table definition for %2$s.', [$field[0], $table]));
                }
                $def = $definition[$field[0]];

                $connection->export->alterTable($tableName, ['add' => [$field[0] => $def]]);
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
     * @param string $table  The treated table reference
     * @param array  $fields Fields to drop from the table
     *
     * @return boolean
     * @throws Exception If parameters are empty
     */
    public static function dropColumn($table, $fields)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
        $arrayFields = [];
        foreach ($fields as $field) {
            $arrayFields[$field] = [];
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        try {
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->alterTable($tableName, ['remove' => $arrayFields]);
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
     * @param integer|boolean $value The value to format
     *
     * @return string string ready to add to SQL statement
     */
    public static function _formatForStore($value)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (is_int($value)) {
            // No need to DataUtil::formatForStore when casted to int
            return (int)$value;
            // Avoid SQL strict problems where false would be stored as ''
        } elseif (false === $value) {
            return 0;
        } elseif (true === $value) {
            return 1;
        }

        return '\'' . DataUtil::formatForStore((string)$value) . '\'';
    }

    /**
     * Generate and execute an insert SQL statement for the given object.
     *
     * @param array   &$object  The object we wish to insert
     * @param string  $table    The treated table reference
     * @param string  $idfield  The column which stores the primary key (optional) (default='id')
     * @param boolean $preserve Whether or not to preserve existing/set standard fields (optional) (default=false)
     * @param boolean $force    Whether or not to insert empty values as NULL (optional) (default=false)
     *
     * @return The result set from the update operation. The object is updated with the newly generated ID
     * @deprecated
     * @see    Doctrine_Record::save()
     * @deprecated
     * @see    Doctrine_Table
     * @throws Exception If column or column_def is not an array or cant find anything to insert into object
     */
    public static function insertObject(array &$object, $table, $idfield = 'id', $preserve = false, $force = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
        $search = ['+', '-', '*', '/', '%'];
        $replace = [''];
        $cArray = [];
        $vArray = [];

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
                $columnDefFields  = explode(' ', $columnDefinition);
                $colType = substr($columnDefinition, 0, 1);
                // ensure that international float numbers are stored with '.' rather than ',' for decimal separator
                if ('F' == $colType || 'N' == $colType) {
                    if (is_float($object[$key]) || is_float($object[$key])) {
                        $object[$key] = number_format($object[$key], 8, '.', '');
                    }
                }

                // generate the actual insert values
                if (!$skip) {
                    $cArray[] = $columnList[$key];
                    $value    = is_bool($object[$key]) ? (int)$object[$key] : $object[$key];
                    if (('derby' == $dbDriverName || 'splice' == $dbDriverName || 'jdbcbridge' == $dbDriverName) &&
                        ('XL' != strtoupper($columnDefFields[0]) || 'B' != strtoupper($columnDefFields[0])) && strlen($object[$key]) > 32000) {
                        $chunks = str_split($object[$key], 32000);
                        $str    = '';
                        foreach ($chunks as $chunk) {
                            if ($str) {
                                $str .= ' || ';
                            }
                            $str = "CAST (" . self::_formatForStore($chunk) . " AS CLOB)";
                        }
                        $vArray[] = self::_formatForStore($str);
                    } else {
                        $vArray[] =  self::_typesafeQuotedValue($table, $key, $object[$key]);
                    }
                }
            } else {
                if ($key == $idfield) {
                    if ('pgsql' == $dbDriverName) {
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
        if (false === $res) {
            return $res;
        }

        self::flushCache($table);

        if (!isset($object[$idfield]) || !$object[$idfield] || (!$preserve || !isset($object[$idfield])) && isset($columnList[$idfield])) {
            if (isset($columnDefList[$idfield])) {
                $columnDefinition = $columnDefList[$idfield];
                $columnDefFields  = explode(' ', $columnDefinition);
                $colType = substr($columnDefinition, 0, 1);
                $colAuto = in_array('AUTO', $columnDefFields);
                if ('I' == $colType && $colAuto) {
                    $obj_id = self::getInsertID($table, $idfield);
                    $object[$idfield] = $obj_id;
                }
            }
        }

        if ($cArray && $vArray) {
            $object = self::_savePostProcess($object, $table, $idfield);
        }

        return $object;
    }

    /**
     * Generate and execute an update SQL statement for the given object.
     *
     * @param array   &$object  The object we wish to update
     * @param string  $table    The treated table reference
     * @param string  $where    The where clause (optional) (default='')
     * @param string  $idfield  The column which stores the primary key (optional) (default='id')
     * @param boolean $force    Whether or not to insert empty values as NULL (optional) (default=false)
     * @param boolean $updateid Allow primary key to be updated (default=false)
     *
     * @return integer The result set from the update operation
     * @deprecated
     * @see    Doctrine_Record::save()
     * @throws Exception If parameters not set or column or column_def not in array
     */
    public static function updateObject(array &$object, $table, $where = '', $idfield = 'id', $force = false, $updateid = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
        $tArray = [];
        $search = ['+', '-', '*', '/', '%'];
        $replace = [''];

        foreach ($columnList as $key => $val) {
            $hasMath = (bool)(strcmp($val, str_replace($search, $replace, $val)));
            if ($hasMath) {
                continue;
            }

            if ($key != $idfield || ($key == $idfield && true == $updateid)) {
                if ($force || array_key_exists($key, $object)) {
                    $skip = false;
                    $columnDefinition = $columnDefList[$key];
                    $columnDefFields  = explode(' ', $columnDefinition);
                    $colType = substr($columnDefinition, 0, 1);
                    // ensure that international float numbers are stored with '.' rather than ',' for decimal separator
                    if ('F' == $colType || 'N' == $colType) {
                        if (is_float($object[$key]) || is_float($object[$key])) {
                            $object[$key] = number_format($object[$key], 8, '.', '');
                        }
                    }

                    // generate the actual update values
                    if (!$skip) {
                        $dbDriverName = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());
                        if (isset($object[$key]) &&
                            ('derby' == $dbDriverName || 'splice' == $dbDriverName || 'jdbcbridge' == $dbDriverName) &&
                            ('XL' != strtoupper($columnDefFields[0]) || 'B' != strtoupper($columnDefFields[0])) && strlen($object[$key]) > 32000) {
                            $chunks = str_split($object[$key], 32000);
                            $str    = '';
                            foreach ($chunks as $chunk) {
                                if ($str) {
                                    $str .= ' || ';
                                }
                                $str .= "CAST (" . self::_formatForStore($chunk) . " AS CLOB)";
                            }
                            $tArray[] = "$val=$str";
                        } else {
                            $tArray[] = "$val=" . (isset($object[$key]) ? self::_typesafeQuotedValue($table, $key, $object[$key]) : 'NULL');
                        }
                    }
                }
            }
        }

        if ($tArray) {
            if (!$where) {
                $_where = " WHERE $columnList[$idfield] = " . self::_typesafeQuotedValue($table, $idfield, $object[$idfield]);
            } else {
                $_where = self::_checkWhereClause($where);
            }

            $sql .= implode(',', $tArray) . ' ' . $_where;

            $res = self::executeSQL($sql);
            if (false === $res) {
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
     * @param array   &$objects The objectArray we wish to insert
     * @param string  $table    The treated table reference
     * @param string  $idfield  The column which stores the primary key (optional) (default='id')
     * @param boolean $preserve Whether or not to preserve existing/set standard fields (optional) (default=false)
     * @param boolean $force    Whether or not to insert empty values as NULL (optional) (default=false)
     *
     * @return integer The result set from the last insert operation. The objects are updated with the newly generated ID
     *
     * @deprecated
     * @see    Doctrine_Table
     */
    public static function insertObjectArray(array &$objects, $table, $idfield = 'id', $preserve = false, $force = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
     * @param array   &$objects The objectArray we wish to insert
     * @param string  $table   The treated table reference
     * @param string  $idfield The column which stores the primary key
     * @param boolean $force   Whether or not to insert empty values as NULL
     *
     * @return integer The result set from the last update operation
     */
    public static function updateObjectArray(array &$objects, $table, $idfield = 'id', $force = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
     * @param mixed   $object  The object wehave just saved
     * @param string  $table   The treated table reference
     * @param integer $idfield The id column for the object/table combination
     * @param boolean $update  Whether or not this was an update (default=false, signifies operation was an insert)
     *
     * @return mixed The object
     * @deprecated
     * @see    CategorisableListener, AttributableListener, MetaDataListener, LoggableListener
     * @throws Exception If invalid idfield received
     */
    private static function _savePostProcess($object, $table, $idfield, $update = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (!$idfield) {
            throw new Exception(__f('Invalid idfield received', $table));
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_categorization"]) && $tables["{$table}_db_extra_enable_categorization"])) &&
                System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') &&
                0 !== strcmp($table, 'categories_') &&
                0 !== strcmp($table, 'objectdata_attributes') &&
                0 !== strcmp($table, 'objectdata_log') &&
                ModUtil::available('ZikulaCategoriesModule')) {
            ObjectUtil::storeObjectCategories($object, $table, $idfield, $update);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_attribution"]) && $tables["{$table}_db_extra_enable_attribution"]) ||
                System::getVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) &&
                0 !== strcmp($table, 'objectdata_attributes') &&
                0 !== strcmp($table, 'objectdata_log')) {
            ObjectUtil::storeObjectAttributes($object, $table, $idfield, $update);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_meta"]) && $tables["{$table}_db_extra_enable_meta"]) ||
                System::getVar('Z_CONFIG_USE_OBJECT_META')) &&
                'objectdata_attributes' != $table &&
                'objectdata_meta' != $table &&
                'objectdata_log' != $table) {
            ObjectUtil::updateObjectMetaData($object, $table, $idfield);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_logging"]) && $tables["{$table}_db_extra_enable_logging"])) &&
                System::getVar('Z_CONFIG_USE_OBJECT_LOGGING') &&
                0 !== strcmp($table, 'objectdata_log')) {
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
     * @param string  $table    The treated table reference
     * @param string  $incfield The column which stores the field to increment
     * @param integer $id       The ID value of the object holding the field we wish to increment
     * @param string  $idfield  The idfield to use (optional) (default='id')
     * @param integer $inccount The amount by which to increment the field (optional) (default=1)
     *
     * @return The result from the increment operation
     */
    public static function incrementObjectFieldByID($table, $incfield, $id, $idfield = 'id', $inccount = 1)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $idFieldName = $columns[$idfield];
        $incFieldName = $columns[$incfield];
        $column = $tables["{$table}_column"];

        $sql  = 'UPDATE ' . $tableName . " SET $incFieldName = $column[$incfield] + $inccount";
        $sql .= " WHERE $idFieldName = " . self::_typesafeQuotedValue($table, $idfield, $id);

        $res = self::executeSQL($sql);
        if (false === $res) {
            return false;
        }

        self::flushCache($table);

        return $res;
    }

    /**
     * Decrement a field by the given decrement.
     *
     * @param string  $table    The treated table reference
     * @param string  $decfield The column which stores the field to decrement
     * @param integer $id       The ID value of the object holding the field we wish to increment
     * @param string  $idfield  The idfield to use (optional) (default='id')
     * @param integer $deccount The amount by which to decrement the field (optional) (default=1)
     *
     * @return integer The result from the decrement operation
     */
    public static function decrementObjectFieldByID($table, $decfield, $id, $idfield = 'id', $deccount = 1)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        return self::incrementObjectFieldByID($table, $decfield, $id, $idfield, 0 - $deccount);
    }

    /**
     * Generate and execute a delete SQL statement for the given object.
     *
     * @param array  $object  The object we wish to delete
     * @param string $table   The treated table reference
     * @param string $where   The where clause to use (optional) (default='')
     * @param string $idfield The column which contains the ID field (optional) (default='id')
     *
     * @return The result from the delete operation
     * @deprecated
     * @see    CategorisableListener, AttributableListener, MetaDataListener, LoggableListener
     * @throws Exception Cant specify both object and whereclause or either are missing
     */
    public static function deleteObject(array $object, $table, $where = '', $idfield = 'id')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
            $sql .= "WHERE $fieldName = " . self::_typesafeQuotedValue($table, $idfield, $object[$idfield]);
        } else {
            $sql .= self::_checkWhereClause($where);
            $object['__fake_field__'] = 'Fake entry to mark deleteWhere() return as valid object';
        }

        $res = self::executeSQL($sql);
        if (false === $res) {
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
     * @param array  $keyarray The KeyArray todelete
     * @param mixed  $table    The treated table reference
     * @param string $field    The field to use
     *
     * @return mixed
     */
    public static function deleteObjectsFromKeyArray(array $keyarray, $table, $field = 'id')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        $sql = 'DELETE FROM ' . $tableName . ' WHERE ' . $fieldName . ' IN (';

        $sqlArray = [];
        foreach ($keyarray as $key => $val) {
            $sqlArray[] = $key;
        }
        $sql .= implode(',', $sqlArray) . ')';

        $res = self::executeSQL($sql);
        if (false === $res) {
            return $res;
        }

        self::flushCache($tableName);

        return $res;
    }

    /**
     * Delete an object by its ID.
     *
     * @param string  $table       The treated table reference
     * @param integer $id          The ID of the object to delete
     * @param string  $idFieldName The column which contains the ID field (optional) (default='id')
     *
     * @return integer The result from the delete operation
     */
    public static function deleteObjectByID($table, $id, $idFieldName = 'id')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $object = [];
        $object[$idFieldName] = $id;

        return self::deleteObject($object, $table, '', $idFieldName);
    }

    /**
     * Delete (an) object(s) via a where clause.
     *
     * @param string $table The treated table reference
     * @param string $where The where-clause to use
     *
     * @return mixed The result from the delete operation
     */
    public static function deleteWhere($table, $where)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if ('categories_mapobj' == $table) {
            // table no longer exists >= 1.4.0
            return true;
        }
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
     * @param mixed   $object  The object wehave just saved
     * @param string  $table   The treated table reference
     * @param integer $idfield The id column for the object/table combination
     *
     * @deprecated
     * @see    CategorisableListener, AttributableListener, MetaDataListener, LoggableListener
     * @return void
     */
    private static function _deletePostProcess($object, $table, $idfield)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_categorization"]) && $tables["{$table}_db_extra_enable_categorization"])) &&
                System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') &&
                'categories_' != $table &&
                'objectdata_attributes' != $table &&
                'objectdata_log' != $table &&
                ModUtil::available('ZikulaCategoriesModule')) {
            ObjectUtil::deleteObjectCategories($object, $table, $idfield);
        }

        if (((isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]) ||
                (isset($tables["{$table}_db_extra_enable_attribution"]) && $tables["{$table}_db_extra_enable_attribution"]) ||
                System::getVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) &&
                'objectdata_attributes' != $table &&
                'objectdata_log' != $table) {
            ObjectUtil::deleteObjectAttributes($object, $table, $idfield);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_meta"]) && $tables["{$table}_db_extra_enable_meta"]) ||
                System::getVar('Z_CONFIG_USE_OBJECT_META')) &&
                'objectdata_attributes' != $table &&
                'objectdata_meta' != $table &&
                'objectdata_log' != $table) {
            ObjectUtil::deleteObjectMetaData($object, $table, $idfield);
        }

        if (($enableAllServices ||
                (isset($tables["{$table}_db_extra_enable_logging"]) && $tables["{$table}_db_extra_enable_logging"])) &&
                System::getVar('Z_CONFIG_USE_OBJECT_LOGGING') &&
                0 !== strcmp($table, 'objectdata_log')) {
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
     * @param string $where The original where clause
     *
     * @return string The value held by the global counter
     */
    public static function _checkWhereClause($where)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!strlen(trim($where))) {
            return $where;
        }

        $where = trim($where);
        $upwhere = strtoupper($where);
        if (false === strstr($upwhere, 'WHERE') || strpos($upwhere, 'WHERE') > 1) {
            $where = 'WHERE ' . $where;
        }

        return $where;
    }

    /**
     * Convenience function to ensure that the order-by-clause starts with "ORDER BY".
     *
     * @param string $orderby The original order-by clause
     * @param string $table   The table reference
     *
     * @return string The (potentially) altered order-by-clause
     */
    public static function _checkOrderByClause($orderby, $table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $orderby = trim($orderby);
        if (!strlen($orderby)) {
            return $orderby;
        }

        if (0 === strpos($orderby, 'GROUP BY')) {
            return $orderby;
        }

        if (!$table) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $orderby      = str_ireplace('ORDER BY ', '', $orderby); // remove "ORDER BY" for easier parsing
        $orderby      = trim(str_replace(["\t", "\n", '  ', ' +0', '+ 0'], [' ', ' ', ' ', '+0', '+0'], $orderby));
        $tables       = self::getTables();
        $columns      = $tables["{$table}_column"];
        $dbDriverName = Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName();
        $tokens       = explode(',', $orderby); // split on comma

        if (!$columns) {
            throw new Exception(__f('The parameter %s does not seem to point towards a valid table definition', 'table'));
        }

        // given that we use quotes in our generated SQL, oracle requires the same quotes in the order-by
        if ('oracle' == $dbDriverName) {
            // anything which doesn't look like a basic ORDER BY clause (with possibly an ASC/DESC modifier)
            // we don't touch. To use such stuff with Oracle, you'll have to apply the quotes yourself.

            foreach ($tokens as $k => $v) {
                $v = trim($v);
                if (false === strpos($v, ' ')) {
                    // 1 word
                    if (false === strpos($v, '(')) {
                        // not a function call
                        if (false === strpos($v, '"')) {
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
                    if (2 == count($ttok)) {
                        // see if we have 2 tokens
                        $t1 = strtolower(trim($ttok[0]));
                        $t2 = strtolower(trim($ttok[1]));
                        $haveQuotes = false === strpos($t1, '"');
                        $isAscDesc = (0 === strpos($t2, 'asc') || 0 === strpos($t2, 'desc'));
                        $isColumn = isset($columns[$ttok[0]]);
                        if ($haveQuotes && $isAscDesc && $isColumn) {
                            $ttok[0] = '"' . $ttok[0] . '"'; // surround it by quotes
                        }
                    }
                    $tokens[$k] = implode(' ', $ttok);
                }
            }
        } else {
            $search = ['+', '-', '*', '/', '%'];
            $replace = [''];

            foreach ($tokens as $k => $v) {
                $hasMath  = (bool)(strcmp($v, str_replace($search, $replace, $v)));
                $hasFunc  = (bool)(strpos($v, '('));
                $hasPlus0 = (bool)(strpos($v, '+0'));

                if ($hasMath) {
                    if ($hasPlus0) {
                        $hasMath = false;
                    }
                }

                if (!$hasFunc && !$hasMath) {
                    $fields = explode(' ', trim($v));
                    if ($fields) {
                        $left = $fields[0];
                        if ($hasPlus0) {
                            $left = substr($left, 0, -2);
                        }

                        $hasTablePrefix = (bool)strpos($left, '.');
                        $fullColumnName = isset($columns[$left]) ? $columns[$left] : $left;

                        // if the resolved column is a math definition, revert back to the original column spec
                        if ($fullColumnName != $left) {
                            $hasMath = (bool)(strcmp($fullColumnName, str_replace($search, $replace, $fullColumnName)));
                            if ($hasMath) {
                                $fullColumnName = "'$left'";
                            } elseif (!$hasTablePrefix) {
                                $fullColumnName = "tbl.$fullColumnName";
                            }
                        } else {
                            if (!$hasTablePrefix) {
                                $fullColumnName = "tbl.$fullColumnName";
                            }
                        }

                        if ($hasPlus0) {
                            $fullColumnName .= '+0';
                        }

                        $tokens[$k] = $fullColumnName;
                        if (count($fields) > 1) {
                            $tokens[$k] .= " $fields[1]";
                        }
                    }
                }
            }
        }

        return ' ORDER BY ' . implode(',', $tokens);
    }

    /**
     * Convenience function.
     *
     * Ensures that the field to be used as ORDER BY
     * is not a CLOB/BLOB when using Oracle
     *
     * @param string $table The treated table reference
     * @param string $field The field name to be used for order by
     *
     * @return string The order-by-clause to be used, may be ''
     */
    public static function _checkOrderByField($table = '', $field = '')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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

        if ('oracle' == $dbDriverName) {
            // we are using oracle - split up the field definition and check if it is defined as a LOB
            // oracle does not like LOBs in an ORDERBY
            $definition = explode(' ', $fieldDef);
            // [0] contains the dangerous information, either XL or B
            if ('XL' != strtoupper($definition[0]) && 'B' != strtoupper($definition[0])) {
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
     * @param string $table       The treated table reference
     * @param string $where       The original where clause (optional) (default='')
     * @param string $orderBy     The original order-by clause (optional) (default='')
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null)
     * @param string $distinct    Set if a "SELECT DISTINCT" should be performed
     *
     * @return string The select clause built
     */
    public static function _getSelectAllColumnsFrom($table, $where = '', $orderBy = '', $columnArray = null, $distinct = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
     * @param integer $count The value to set the object marhsall counter to
     *
     * @return void
     */
    public static function _setFetchedObjectCount($count = 0)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        // TODO D [remove PHP4 stuff in DBUtil] (Guite)
        $GLOBALS['DBUtilFetchObjectCount'] = $count;

        return;
    }

    /**
     * Get the gobal object fetch counter.
     *
     * This function is workaround for PHP4 limitations when passing default arguments by reference.
     *
     * @return integer The value held by the global
     * @deprecated
     */
    public static function _getFetchedObjectCount()
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        // TODO D [remove PHP4 stuff in DBUtil] (Guite)
        if (isset($GLOBALS['DBUtilFetchObjectCount'])) {
            return (int)$GLOBALS['DBUtilFetchObjectCount'];
        }

        return false;
    }

    /**
     * Transform a result set into an array of field values.
     *
     * @param mixed   $result         The result set we wish to marshall
     * @param boolean $closeResultSet Whether or not to close the supplied result set (optional) (default=true)
     * @param string  $assocKey       The key field to use to build the associative index (optional) (default='')
     * @param boolean $clean          Whether or not to clean up the marshalled data (optional) (default=true)
     *
     * @return The       resulting field array
     * @throws Exception If empty result parameter
     */
    public static function marshallFieldArray($result, $closeResultSet = true, $assocKey = '', $clean = true)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!$result) {
            throw new Exception(__f('The parameter %s must not be empty', 'result'));
        }

        $resultRows = $result->fetchAll(Doctrine_Core::FETCH_NUM);
        $fieldArray = [];
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
     * @param mixed   $result           The result set we wish to marshall
     * @param array   $objectColumns    The column array to map onto the result set, default null = don't use
     * @param boolean $closeResultSet   Whether or not to close the supplied result set (optional) (default=true)
     * @param string  $assocKey         The key field to use to build the associative index (optional) (default='')
     * @param boolean $clean            Whether or not to clean up the marshalled data (optional) (default=true)
     * @param string  $permissionFilter The permission structure to use for permission checking (optional) (default=null)
     * @param string  $tablename        The tablename
     *
     * @return array     The marshalled array of objects
     * @throws Exception If empty parameters. or if permissionfilter is not an array
     */
    public static function marshallObjects($result, $objectColumns = null, $closeResultSet = true, $assocKey = '', $clean = true, $permissionFilter = null, $tablename = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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

        $object = [];
        $objectArray = [];
        $fetchedObjectCount = 0;
        $resultRows = $result->fetchAll(Doctrine_Core::FETCH_ASSOC);
        if ($resultRows && $objectColumns && count($resultRows[0]) != count($objectColumns)) {
            throw new Exception('$objectColumn field count must match the resultset');
        }

        if ($assocKey && $resultRows &&
            (!array_key_exists($assocKey, $resultRows[0]) && !in_array($assocKey, $objectColumns))) {
            throw new Exception(__f('Unable to find assocKey [%1$s] in objectColumns for resultset.', [$assocKey]));
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
                    $permissionFilter = [$permissionFilter];
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

                    if ('__PERM_NO_SUCH_ITEM__' == $oil && '__PERM_NO_SUCH_ITEM__' == $oim && '__PERM_NO_SUCH_ITEM__' == $oir) {
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
     * @param string  $sql         Sql string
     * @param boolean $exitOnError Exit on error
     *
     * @return mixed     selected value
     * @throws Exception If rowcount or results count is empty
     */
    public static function selectScalar($sql, $exitOnError = true)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $res = self::executeSQL($sql);
        if (false === $res) {
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
     * @param string $table The treated table reference
     * @param string $field The name of the field we wish to marshall
     * @param string $where The where clause (optional) (default='')
     *
     * @return The resulting field array
     */
    public static function selectField($table, $field, $where = '')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $fieldArray = self::selectFieldArray($table, $field, $where, '', false, '', 0, 1);

        if (count($fieldArray) > 0) {
            return $fieldArray[0];
        }

        return false;
    }

    /**
     * Select & return a field by an ID-field value.
     *
     * @param string  $tableName The treated table reference
     * @param string  $field     The field we wish to select
     * @param integer $id        The ID value we wish to select with
     * @param string  $idfield   The idfield to use (optional) (default='id')
     *
     * @return mixed The resulting field value
     */
    public static function selectFieldByID($tableName, $field, $id, $idfield = 'id')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $cols = $tables["{$tableName}_column"];
        $idFieldName = $cols[$idfield];

        $where = $idFieldName . " = " . self::_typesafeQuotedValue($tableName, $idfield, $id);

        return self::selectField($tableName, $field, $where);
    }

    /**
     * Select & return a field array.
     *
     * @param string  $table        The treated table reference
     * @param string  $field        The name of the field we wish to marshall
     * @param string  $where        The where clause (optional) (default='')
     * @param string  $orderby      The orderby clause (optional) (default='')
     * @param boolean $distinct     Whether or not to add a 'DISTINCT' clause (optional) (default=false)
     * @param string  $assocKey     The key field to use to build the associative index (optional) (default='')
     * @param integer $limitOffset  The lower limit bound (optional) (default=-1)
     * @param integer $limitNumRows The upper limit bound (optional) (default=-1)
     *
     * @return array The resulting field array
     */
    public static function selectFieldArray($table, $field, $where = '', $orderby = '', $distinct = false, $assocKey = '', $limitOffset = -1, $limitNumRows = -1)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $key = $field . $where . $orderby . $distinct . $assocKey;
        $objects = self::getCache($table, $key);
        if (false !== $objects) {
            return $objects;
        }

        $exitOnError = true;
        $tables = self::getTables();
        if (!isset($tables["{$table}_column"])) {
            // For field arrays we construct a temporary literal table entry which allows us to
            // do ad-hoc queries on dynamic reference tables which do not have tables.php entry.
            $tables[$table]                    = $table;
            $tables["{$table}_column"]         = [];
            $tables["{$table}_column"][$field] = $field;
            if ($assocKey) {
                $tables["{$table}_column"][$assocKey] = $assocKey;
            }
            $exitOnError = false;
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

        $res = self::executeSQL($sql, $limitOffset, $limitNumRows, $exitOnError);
        if (false === $res) {
            return $res;
        }

        $fields = self::marshallFieldArray($res, true, $assocKey);
        self::setCache($table, $key, $fields);

        return $fields;
    }

    /**
     * Select & return an array of field by an ID-field value.
     *
     * @param string $tableName     The treated table reference
     * @param string $field         The field we wish to select
     * @param string $id            The ID value we wish to select with
     * @param string $idfield       The idfield to use (optional) (default='id')
     * @param string  $orderby      The orderby clause (optional) (default='')
     * @param boolean $distinct     Whether or not to add a 'DISTINCT' clause (optional) (default=false)
     * @param string  $assocKey     The key field to use to build the associative index (optional) (default='')
     * @param integer $limitOffset  The lower limit bound (optional) (default=-1)
     * @param integer $limitNumRows The upper limit bound (optional) (default=-1)
     *
     * @return mixed The resulting field value
     */
    public static function selectFieldArrayByID($tableName, $field, $id, $idfield = 'id', $orderby = '', $distinct = false, $assocKey = '', $limitOffset = -1, $limitNumRows = -1)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $cols = $tables["{$tableName}_column"];
        $idFieldName = $cols[$idfield];

        $where = $idFieldName . " = " . self::_typesafeQuotedValue($tableName, $idfield, $id);

        return self::selectFieldArray($tableName, $field, $where, $orderby, $distinct, $assocKey, $limitOffset, $limitNumRows);
    }

    /**
     * Select & return the max/min value of a field.
     *
     * @param string $table  The treated table reference
     * @param string $field  The name of the field we wish to marshall
     * @param string $option MIN, MAX, SUM or COUNT (optional) (default='MAX')
     * @param string $where  The where clause (optional) (default='')
     *
     * @return mixed The resulting min/max value
     */
    public static function selectFieldMax($table, $field, $option = 'MAX', $where = '')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $field = isset($columns[$field]) ? $columns[$field] : $field;

        $sql = "SELECT $option($field) FROM $tableName AS tbl";
        $where = self::_checkWhereClause($where);

        $sql .= ' ' . $where;

        $res = self::executeSQL($sql);
        if (false === $res) {
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
     * @param string $table    The treated table reference
     * @param string $field    The name of the field we wish to marshall
     * @param string $option   MIN, MAX, SUM or COUNT (optional) (default='MAX')
     * @param string $where    The where clause (optional) (default='')
     * @param string $assocKey The key field to use to build the associative index (optional) (default='' which defaults to the primary key)
     *
     * @return array The resulting min/max value
     */
    public static function selectFieldMaxArray($table, $field, $option = 'MAX', $where = '', $assocKey = '')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
        if (false === $res) {
            return false;
        }

        $objArray = [];
        foreach ($res as $row) {
            $objArray[$row[0]] = $row[1];
        }

        return $objArray;
    }

    /**
     * Build a list of objects which are mapped to the specified categories.
     *
     * @param string  $tablename      Treated table reference
     * @param string  $categoryFilter The category list to use for filtering
     * @param boolean $returnArray    Whether or not to return an array (optional) (default=false)
     *
     * @return mixed The resulting string or array
     */
    private static function _generateCategoryFilter($tablename, $categoryFilter, $returnArray = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!$categoryFilter) {
            return '';
        }

        if (!ModUtil::dbInfoLoad('ZikulaCategoriesModule')) {
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
        if (isset($categoryFilter['__META__']['operator']) && in_array(strtolower($categoryFilter['__META__']['operator']), ['and', 'or'])) {
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

        $where = [];
        foreach ($categoryFilter as $property => $category) {
            $prefix = '';
            if ('AND' == $op) {
                $prefix = "table$n.";
            }

            // this allows to have an array of categories IDs
            if (is_array($category)) {
                $wherecat = [];
                foreach ($category as $cat) {
                    $wherecat[] = "{$prefix}category_id='" . DataUtil::formatForStore($cat) . "'";
                }
                $wherecat = '(' . implode(' OR ', $wherecat) . ')';

                // if there's only one category ID
            } else {
                $wherecat = "{$prefix}category_id='" . DataUtil::formatForStore($category) . "'";
            }

            // process the where depending of the operator
            if ('AND' == $op) {
                $where[] = "obj_id IN (SELECT {$prefix}obj_id FROM $catmapobjtbl table$n WHERE {$prefix}reg_id = '".DataUtil::formatForStore($propids[$property])."' AND $wherecat)";
            } else {
                $where[] = "(reg_id='" . DataUtil::formatForStore($propids[$property]) . "' AND $wherecat)";
            }
            $n++;
        }
        $where = "tablename='" . DataUtil::formatForStore($tablename) . "' AND (" . implode(" $op ", $where) . ')';

        // perform the query
        $objIds = self::selectFieldArray('categories_mapobj', 'obj_id', $where);

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
     * @param string  $table          The treated table reference
     * @param string  $where          The where clause (optional) (default='')
     * @param string  $categoryFilter The category list to use for filtering
     * @param boolean $returnArray    Whether or not to return an array (optional) (default=false)
     * @param boolean $useJoins       Whether a join is used (if yes, then a prefix is prepended to the column name) (optional) (default=false)
     *
     * @return mixed The resulting string or array
     */
    public static function generateCategoryFilterWhere($table, $where, $categoryFilter, $returnArray = false, $useJoins = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $idlist = self::_generateCategoryFilter($table, $categoryFilter);
        if ($idlist) {
            $cols = $tables["{$table}_column"];
            $idcol = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';
            $idcol = $cols[$idcol];

            $and = ($where ? ' AND ' : '');
            $tblName = ($useJoins ? 'tbl.' : '') . $idcol;
            $where .= "$and $tblName IN ($idlist)";
        }

        return $where;
    }

    /**
     * Select & return a specific object using the given sql statement.
     *
     * @param string $sql              The sql statement to execute for the selection
     * @param string $table            The treated table reference
     * @param array  $columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param string $permissionFilter The permission filter to use for permission checking (optional) (default=null)
     *
     * @return array The resulting object
     */
    public static function selectObjectSQL($sql, $table, $columnArray = null, $permissionFilter = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $permissionFilterKey = '';
        if (is_array($permissionFilter)) {
            foreach ($permissionFilter as $permissionRule) {
                $permissionFilterKey .= implode('_', $permissionRule);
            }
        }

        $res = self::executeSQL($sql, 0, 1);
        if (false === $res) {
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
     * @param string $table            The treated table reference
     * @param string $where            The where clause (optional) (default='')
     * @param array  $columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param string $permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @param string $categoryFilter   The category list to use for filtering (optional) (default=null)
     *
     * @return mixed The resulting object
     */
    public static function selectObject($table, $where = '', $columnArray = null, $permissionFilter = null, $categoryFilter = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $key = $where . serialize($columnArray) . serialize($permissionFilter) . serialize($categoryFilter);
        $objects = self::getCache($table, $key);
        if (false !== $objects) {
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
            return [];
        }

        $object = self::_selectPostProcess($object, $table, $idcol);

        self::setCache($table, $key, $object);

        return $object;
    }

    /**
     * Select & return a specific object by using the ID field.
     *
     * @param string  $table            The treated table reference
     * @param integer $id               The object ID to query
     * @param string  $field            The field key which holds the ID value (optional) (default='id')
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param string  $permissionFilter The permission structure to use for permission checking (optional) (default=null)
     * @param string  $categoryFilter   The category list to use for filtering (optional) (default=null)
     * @param boolean $cacheObject      If true returns a cached object if available (optional) (default=true)
     * @param boolean $transformFunc    Transformation function to apply to $id (optional) (default=null)
     *
     * @return mixed The resulting object
     * @deprecated
     * @see    Doctrine_Table::find*
     * @throws Exception If id parameter is empty or non-numeric
     */
    public static function selectObjectByID($table, $id, $field = 'id', $columnArray = null, $permissionFilter = null, $categoryFilter = null, $cacheObject = true, $transformFunc = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        if (!$id) {
            throw new Exception(__f('The parameter %s must not be empty', 'id'));
        }

        if ('id' == $field && !is_numeric($id)) {
            throw new Exception(__f('The parameter %s must be numeric', 'id'));
        }

        $cols = $tables["{$table}_column"];
        $fieldName = $cols[$field];

        $where = (($transformFunc) ? "$transformFunc($fieldName)" : $fieldName) . ' = ' . self::_typesafeQuotedValue($table, $field, $id);

        $obj = self::selectObject($table, $where, $columnArray, $permissionFilter, $categoryFilter, $cacheObject);
        // _selectPostProcess is already called in selectObject()

        return $obj;
    }

    /**
     * Select & return an object array based on a table definition.
     *
     * @param string  $table            The treated table reference
     * @param string  $where            The where clause (optional) (default='')
     * @param string  $orderby          The order by clause (optional) (default='')
     * @param integer $limitOffset      The lower limit bound (optional) (default=-1)
     * @param integer $limitNumRows     The upper limit bound (optional) (default=-1)
     * @param string  $assocKey         The key field to use to build the associative index (optional) (default='')
     * @param string  $permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @param string  $categoryFilter   The category list to use for filtering (optional) (default=null)
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param string  $distinct         Set if a "SELECT DISTINCT" should be performed
     *
     * @return array The resulting object array
     */
    public static function selectObjectArray($table, $where = '', $orderby = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '', $permissionFilter = null, $categoryFilter = null, $columnArray = null, $distinct = '')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $key = $where . $orderby . $limitOffset . $limitNumRows . $assocKey . serialize($permissionFilter) . serialize($categoryFilter) . serialize($columnArray) . ($distinct ? '1' : '0');
        $objects = self::getCache($table, $key);
        if (false !== $objects) {
            return $objects;
        }

        self::_setFetchedObjectCount(0);

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $objects = [];
        $ca = null; // Not required since Zikula 1.3.0 because of 'PDO::fetchAll()' #2227// self::getColumnsArray($table, $columnArray);
        $sql = self::_getSelectAllColumnsFrom($table, $where, $orderby, $columnArray, $distinct);

        do {
            $fetchedObjectCount = self::_getFetchedObjectCount();
            $stmt = $sql;
            $limitOffset += $fetchedObjectCount;

            $res = self::executeSQL($stmt, $limitOffset, $limitNumRows);
            if (false === $res) {
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
     * @param string   $table          The treated table reference
     * @param string   $where          The where clause (optional) (default='')
     * @param string   $orderby        The order by clause (optional) (default='')
     * @param integer  $limitOffset    The lower limit bound (optional) (default=-1)
     * @param integer  $limitNumRows   The upper limit bound (optional) (default=-1)
     * @param string   $assocKey       The key field to use to build the associative index (optional) (default='')
     * @param callback $filterCallback The filter callback object
     * @param array    $categoryFilter The category list to use for filtering
     * @param array    $columnArray    The columns to marshall into the resulting object (optional) (default=null)
     *
     * @return array The resulting object array
     */
    public static function selectObjectArrayFilter($table, $where, $orderby, $limitOffset, $limitNumRows, $assocKey, $filterCallback, $categoryFilter = null, $columnArray = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        // set default values
        $where = isset($where) ? $where : '';
        $orderby = isset($orderby) ? $orderby : '';
        $limitOffset = isset($limitOffset) ? $limitOffset : -1;
        $limitNumRows = isset($limitNumRows) ? $limitNumRows : -1;
        $assocKey = isset($assocKey) ? $assocKey : '';
        self::_setFetchedObjectCount(0);

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $objects = [];
        $fetchedObjectCount = 0;
        $ca = null; //Note required since Zikula 1.3.0 because of PDO::fetchAll() see #2227 //self::getColumnsArray($table, $columnArray);
        $sql = self::_getSelectAllColumnsFrom($table, $where, $orderby, $columnArray);

        do {
            $stmt = $sql;
            $limitOffset += $fetchedObjectCount;

            $res = self::executeSQL($stmt, $limitOffset, $limitNumRows);
            if (false === $res) {
                return $res;
            }

            $objArr = self::marshallObjects($res, $ca, true, $assocKey, true, null, $table);
            $fetchedObjectCount = self::_getFetchedObjectCount();

            for ($i = 0, $cou = count($objArr); $i < $cou; ++$i) {
                $obj = &$objArr[$i];
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
     * @param string $table          The treated table reference
     * @param string $column         The column to place in the sum phrase
     * @param string $where          The where clause (optional) (default='')
     * @param string $categoryFilter The category list to use for filtering (optional) (default=null)
     * @param string $subquery       The subquery to the apply to the operatioin (optional) default=null)
     *
     * @return integer The resulting column sum
     */
    public static function selectObjectSum($table, $column, $where = '', $categoryFilter = null, $subquery = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $key = $column . $where. serialize($categoryFilter) . $subquery;
        $sum = self::getCache($table, $key);
        if (false !== $sum) {
            return $sum;
        }

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$column];

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);

        if ($subquery) {
            $sql = "SELECT SUM($fieldName) FROM $subquery";
        } else {
            $sql = "SELECT SUM($fieldName) FROM $tableName AS tbl $where";
        }

        $res = self::executeSQL($sql);
        if (false === $res) {
            return $res;
        }

        $sum = false;
        if ($data = $res->fetchColumn(0)) {
            $sum = $data;
        }

        self::setCache($table, $key, $sum);

        return $sum;
    }

    /**
     * Return the number of rows affected.
     *
     * @param string  $table          The treated table reference
     * @param string  $where          The where clause (optional) (default='')
     * @param string  $column         The column to place in the count phrase (optional) (default='*')
     * @param boolean $distinct       Whether or not to count distinct entries (optional) (default='false')
     * @param string  $categoryFilter The category list to use for filtering (optional) (default=null)
     * @param string  $subquery       The subquery to the apply to the operatioin (optional) default=null)
     *
     * @return integer The resulting object count
     */
    public static function selectObjectCount($table, $where = '', $column = '1', $distinct = false, $categoryFilter = null, $subquery = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $key = $column . $where. (int)$distinct . serialize($categoryFilter) . $subquery;
        $sum = self::getCache($table, $key);
        if (false !== $sum) {
            return $sum;
        }

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];

        $dst = ($distinct && '1' != $column ? 'DISTINCT' : '');
        $col = ('1' === $column ? '1' : $columns[$column]);

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);

        if ($subquery) {
            $sql = "SELECT COUNT($dst $col) FROM $subquery";
        } else {
            $sql = "SELECT COUNT($dst $col) FROM $tableName AS tbl $where";
        }

        $res = self::executeSQL($sql);
        if (false === $res) {
            return $res;
        }

        $res = $res->fetchAll(Doctrine_Core::FETCH_COLUMN); // RNG: Should this really be fetchAll() ??

        if ($res) {
            if (isset($res[0])) {
                $dbDriverName = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());
                if ('jdbcbridge' == $dbDriverName) {
                    $count = $res[0][0];
                } else {
                    $count = $res[0];
                }
            } else {
                $count = $res["COUNT($dst $col)"];
            }
        }

        self::setCache($table, $key, $count);

        return $count;
    }

    /**
     * Select an object count by ID.
     *
     * @param string  $table         The treated table reference
     * @param integer $id            The id value to match
     * @param string  $field         The field to match the ID against (optional) (default='id')
     * @param string  $transformFunc Transformation function to apply to $id (optional) (default=null)
     *
     * @return The       resulting object count
     * @throws Exception If id paramerter is empty or non-numeric
     */
    public static function selectObjectCountByID($table, $id, $field = 'id', $transformFunc = '')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!$id) {
            throw new Exception(__f('The parameter %s must not be empty', 'id'));
        }

        if ('id' == $field && !is_numeric($id)) {
            throw new Exception(__f('The parameter %s must be numeric', 'id'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        if ($transformFunc) {
            $where = "$transformFunc($fieldName) = " . self::_typesafeQuotedValue($table, $field, $id);
        } else {
            $where = $fieldName . " = " . self::_typesafeQuotedValue($table, $field, $id);
        }

        return self::selectObjectCount($table, $where, $field);
    }

    /**
     * Construct and execute a select statement from a nested set of expressions
     *
     * @param string  $table              The treated table reference
     * @param string  $sqlExpressionArray An array of expressions
     * @param string  $columns            The column array we use to marshall the result set
     * @param integer $id                 The id value to match (optional) (default=1)
     * @param string  $field              The field to match the ID against (optional) (default='id')
     *
     * @return integer The resulting object
     */
    public static function selectNestedExpressionsObject($table, $sqlExpressionArray, $columns, $id = 1, $field = 'id')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!is_array($sqlExpressionArray)) {
            throw new Exception(__f('The parameter %s must be an array', 'sqlExpressionArray'));
        }

        if (!$sqlExpressionArray) {
            throw new Exception(__f('The parameter %s must not be an empty array', 'sqlExpressionArray'));
        }

        if (!is_array($columns)) {
            throw new Exception(__f('The parameter %s must be an array', 'columns'));
        }

        if (!$columns) {
            throw new Exception(__f('The parameter %s must not be an empty array', 'columns'));
        }

        $tables    = self::getTables();
        $tableName = $tables[$table];
        $tableCols = $tables["{$table}_column"];
        $fieldName = $tableCols['id'];
        $where     = $fieldName . " = " . self::_typesafeQuotedValue($table, $field, $id);
        $sql       = 'SELECT ' . implode(',', $sqlExpressionArray) . " FROM $tableName WHERE $where";
        $res       = self::executeSQL($sql, 0, 1);

        if (false === $res) {
            return $res;
        }

        $res = self::marshallObjects($res, $columns);

        return $res[0];
    }

    /**
     * Select & return an expanded field array.
     *
     * @param string  $table            The treated table reference
     * @param array   $joinInfo         The array containing the extended join information
     * @param string  $field            The name of the field we wish to marshall
     * @param string  $where            The where clause (optional) (default='')
     * @param string  $orderby          The orderby clause (optional) (default='')
     * @param boolean $distinct         Whether or not to add a 'DISTINCT' clause (optional) (default=false)
     * @param string  $assocKey         The key field to use to build the associative index (optional) (default='')
     * @param string  $permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @param integer $limitOffset      The lower limit bound (optional) (default=-1)
     * @param integer $limitNumRows     The upper limit bound (optional) (default=-1)
     *
     * @return The resulting field array
     */
    public static function selectExpandedFieldArray($table, $joinInfo, $field, $where = '', $orderby = '', $distinct = false, $assocKey = '', $permissionFilter = null, $limitOffset = -1, $limitNumRows = -1)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $key = $field . $where . $orderby . $distinct . $assocKey . serialize($joinInfo) . serialize($permissionFilter);
        $objects = self::getCache($table, $key);
        if (false !== $objects) {
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

        if (false === $res) {
            return $res;
        }

        $fields = self::marshallFieldArray($res, true, $assocKey);
        self::setCache($table, $key, $fields);

        return $fields;
    }

    /**
     * Select & return a object with it's left join fields filled in.
     *
     * @param string $table            The treated table reference
     * @param array  $joinInfo         The array containing the extended join information
     * @param string $where            The where clause (optional)
     * @param array  $columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param string $permissionFilter The permission structure to use for permission checking (optional) (default=null)
     * @param string $categoryFilter   The category list to use for filtering (optional) (default=null)
     *
     * @return array The resulting object
     */
    public static function selectExpandedObject($table, $joinInfo, $where = '', $columnArray = null, $permissionFilter = null, $categoryFilter = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $objects = self::selectExpandedObjectArray($table, $joinInfo, $where, '', 0, 1, '', $permissionFilter, $categoryFilter, $columnArray);

        if (count($objects)) {
            return $objects[0];
        }

        return $objects;
    }

    /**
     * Select & return an object by it's ID  with it's left join fields filled in.
     *
     * @param string  $table            The treated table reference
     * @param array   $joinInfo         The array containing the extended join information
     * @param integer $id               The ID value to use for object retrieval
     * @param string  $field            The field key which holds the ID value (optional) (default='id')
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param string  $permissionFilter The permission structure to use for permission checking (optional) (default=null)
     * @param string  $categoryFilter   The category list to use for filtering (optional) (default=null)
     * @param string  $transformFunc    Transformation function to apply to $id (optional) (default=null)
     *
     * @return array The resulting object
     */
    public static function selectExpandedObjectByID($table, $joinInfo, $id, $field = 'id', $columnArray = null, $permissionFilter = null, $categoryFilter = null, $transformFunc = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        if ($transformFunc) {
            $where = "tbl.$transformFunc($fieldName) = " . self::_typesafeQuotedValue($table, $field, $id);
        } else {
            $where = "tbl.$fieldName = " . self::_typesafeQuotedValue($table, $field, $id);
        }

        $object = self::selectExpandedObject($table, $joinInfo, $where, $columnArray, $permissionFilter, $categoryFilter);

        return $object;
    }

    /**
     * Select & return an array of objects with it's left join fields filled in.
     *
     * @param string  $table            The treated table reference
     * @param array   $joinInfo         The array containing the extended join information
     * @param string  $where            The where clause (optional) (default='')
     * @param string  $orderby          The order by clause (optional) (default='')
     * @param integer $limitOffset      The lower limit bound (optional) (default=-1)
     * @param integer $limitNumRows     The upper limit bound (optional) (default=-1)
     * @param string  $assocKey         The key field to use to build the associative index (optional) (default='')
     * @param string  $permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @param string  $categoryFilter   The category filter (optional) (default=null)
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param string  $distinct         Set if a "SELECT DISTINCT" should be performed.  default false
     *
     * @return array The resulting object
     */
    public static function selectExpandedObjectArray($table, $joinInfo, $where = '', $orderby = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '', $permissionFilter = null, $categoryFilter = null, $columnArray = null, $distinct = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $key = serialize($joinInfo) . $where . $orderby . $limitOffset . $limitNumRows . serialize($assocKey) . serialize($permissionFilter) . serialize($categoryFilter) . serialize($columnArray) . ($distinct ? '1' : '0');
        $objects = self::getCache($table, $key);
        if (false !== $objects) {
            return $objects;
        }

        self::_setFetchedObjectCount(0);

        $tables       = self::getTables();
        $tableName    = $tables[$table];
        $columns      = $tables["{$table}_column"];
        $useJoins     = (count($joinInfo) > 0) ? true : false;
        $disableJoins = System::getVar('disableJoinss');

        $sqlStart  = "SELECT " . ($distinct ? 'DISTINCT ' : '') . self::_getAllColumnsQualified($table, 'tbl', $columnArray);
        $sqlFrom   = "FROM $tableName AS tbl ";

        if ($useJoins && !$disableJoins) {
            $sqlJoinArray     = self::_processJoinArray($table, $joinInfo, $columnArray);
            $sqlJoin          = $sqlJoinArray[0];
            $sqlJoinFieldList = $sqlJoinArray[1];
        }
        $ca = null; //$sqlJoinArray[2]; -- edited by Drak, this causes errors if set.

        $where   = self::generateCategoryFilterWhere($table, $where, $categoryFilter, false, $useJoins);
        $where   = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);
        $objects = [];

        if ($useJoins && !$disableJoins) {
            $sql = "$sqlStart $sqlJoinFieldList $sqlFrom $sqlJoin $where $orderby";
        } else {
            $sql = "$sqlStart $sqlFrom $where $orderby";
        }

        do {
            $fetchedObjectCount = self::_getFetchedObjectCount();
            $stmt = $sql;
            $limitOffset += $fetchedObjectCount;

            $res = self::executeSQL($stmt, $limitOffset, $limitNumRows);
            if (false === $res) {
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

        if ($objects && $useJoins && $disableJoins) {
            foreach ($joinInfo as $ji) {
                if (isset($ji['join_where']) && $ji['join_where']) {
                    continue;
                }

                $joinTable = $ji['join_table'];
                $tab       = $tables[$joinTable];
                $cols      = $tables["{$joinTable}_column"];
                $colDefs   = $tables["{$joinTable}_column_def"];

                $ids = [];
                $idField = $ji['compare_field_table'];
                foreach ($objects as $object) {
                    $id = isset($object[$idField]) ? $object[$idField] : null;
                    if ($id) {
                        $ids[$id] = $id;
                    }
                }

                $joinFields       = $ji['join_field'];
                $objectFields     = $ji['object_field_name'];
                $joinTableIdField = $ji['compare_field_join'];

                if (!is_array($joinFields)) {
                    $joinFields = [$joinFields];
                }
                if (!is_array($objectFields)) {
                    $objectFields = [$objectFields];
                }

                $fieldType  = $colDefs[$joinTableIdField];
                $fieldTypes = explode(' ', $fieldType);
                $fieldType  = $fieldTypes[0];

                static $numericFields = null;
                if (!$numericFields) {
                    $numericFields = ['I', 'I1', 'I2', 'I4', 'I8', 'F', 'N', 'L'];
                }

                if (!in_array($fieldType, $numericFields)) {
                    foreach ($ids as $k => $v) {
                        $ids[$k] = "'$v'";
                    }
                }

                $idList = implode(',', $ids);
                $where  = "$cols[$joinTableIdField] IN ($idList)";
                $joinObjects = $ids ? self::selectObjectArray($joinTable, $where, '', -1, -1, $joinTableIdField) : [];

                foreach ($objects as $k => $object) {
                    foreach ($joinFields as $kk => $joinField) {
                        if (isset($object[$idField])) {
                            $objectIdValue    = $object[$idField];
                            $joinFieldName    = $joinFields[$kk];
                            $objectFieldName  = $objectFields[$kk];
                            if (isset($joinObjects[$objectIdValue])) {
                                $objectFieldValue = $joinObjects[$objectIdValue][$joinFieldName];
                                $objects[$k][$objectFieldName] = $objectFieldValue;
                            }
                        }
                    }
                }
            }
        }

        self::setCache($table, $key, $objects);

        return $objects;
    }

    /**
     * Return the number of rows affected.
     *
     * @param string  $table          The treated table reference
     * @param array   $joinInfo       The array containing the extended join information
     * @param string  $where          The where clause (optional) (default='')
     * @param boolean $distinct       Whether or not to count distinct entries (optional) (default='false') turned off as fix for http://code.zikula.org/core/ticket/49, not supported in SQL)
     * @param string  $categoryFilter The category list to use for filtering (optional) (default=null)
     *
     * @return integer The resulting object count
     */
    public static function selectExpandedObjectCount($table, $joinInfo, $where = '', $distinct = false, $categoryFilter = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
        $sqlGroupBy = (empty($sqlJoinArray[3])) ? '' : 'GROUP BY '.implode(', ', $sqlJoinArray[3]);

        $sql = "$sqlStart $sqlJoinFieldList $sqlFrom $sqlJoin $where $sqlGroupBy";
        $res = self::executeSQL($sql);
        if (false === $res) {
            return $res;
        }

        $count = false;
        $res   = $res->fetchAll(Doctrine_Core::FETCH_COLUMN);
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
     * @param string $table       The treated table reference
     * @param array  $joinInfo    The array containing the extended join information
     * @param array  $columnArray The columns to marshall into the resulting object (optional) (default=null)
     * @param string $alias       The alias to use a starting point for joined tables passed as a reference!!! (optional) (default=null)
     *
     * @return array $sqlJoin, $sqlJoinFieldList, $ca, $sqlJoinFieldArray
     * @deprecated
     * @see    Doctrine_Record
     */
    private static function _processJoinArray($table, $joinInfo, $columnArray = null, &$alias = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tables = self::getTables();
        $columns = $tables["{$table}_column"];

        $allowedJoinMethods = ['LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN'];

        $ca = self::getColumnsArray($table, $columnArray);
        $alias = $alias ? $alias : 'a';
        $sqlJoin = '';
        $sqlJoinFieldList = '';
        $sqlJoinFieldArray = [];
        foreach (array_keys($joinInfo) as $k) {
            $jt = $joinInfo[$k]['join_table'];
            $jf = $joinInfo[$k]['join_field'];
            $ofn = $joinInfo[$k]['object_field_name'];
            $cft = isset($joinInfo[$k]['compare_field_table']) ? $joinInfo[$k]['compare_field_table'] : null;
            $cfj = isset($joinInfo[$k]['compare_field_join']) ? $joinInfo[$k]['compare_field_join'] : null;
            $jw  = isset($joinInfo[$k]['join_where']) ? $joinInfo[$k]['join_where'] : null;

            $joinMethod = 'LEFT JOIN';
            if (isset($joinInfo[$k]['join_method']) && in_array(strtoupper($joinInfo[$k]['join_method']), $allowedJoinMethods)) {
                $joinMethod = $joinInfo[$k]['join_method'];
            }

            $jtab = $tables[$jt];
            $jcol = $tables["{$jt}_column"];

            if (!is_array($jf)) {
                $jf = [$jf];
            }

            if (!is_array($ofn)) {
                $ofn = [$ofn];
            }

            // loop over all fields to select from the joined table
            foreach ($jf as $k => $v) {
                $currentColumn = $jcol[$v];
                // attempt to remove encoded table name in column list used by some tables
                $t = strstr($currentColumn, '.');
                if (false !== $t) {
                    $currentColumn = substr($t, 1);
                }

                $line = ", $alias.$currentColumn AS \"$ofn[$k]\" ";
                $sqlJoinFieldList .= $line;
                $sqlJoinFieldArray[] = "$alias.$currentColumn";

                $ca[] = $ofn[$k];
            }

            if ($jw) {
                $line = ' ' . $joinMethod . " $jtab $alias ON $jw ";
            } else {
                $compareColumn = $jcol[$cfj];
                // attempt to remove encoded table name in column list used by some tables
                $t = strstr($compareColumn, '.');
                if (false !== $t) {
                    $compareColumn = substr($t, 1);
                }

                $t = isset($columns[$cft]) ? "tbl.$columns[$cft]" : $cft; // if not a column reference assume litereal column name
                $line = ' ' . $joinMethod . " $jtab $alias ON $alias.$compareColumn = $t ";
            }

            $sqlJoin .= $line;
            ++$alias;
        }

        return [$sqlJoin, $sqlJoinFieldList, $ca, $sqlJoinFieldArray];
    }

    /**
     * Post-processing for selected objects.
     *
     * This routine is responsible for reading the 'extra' data
     * (attributes, categories, and meta data) from the database and inserting the relevant sub-objects into the object.
     *
     * @param array   $objects     The object-array or the object we just selected
     * @param string  $table       The treated table reference
     * @param integer $idFieldName The id column for the object/table combination
     *
     * @return array the object with it's relevant sub-objects set
     *
     * @deprecated
     * @see    CategorisableListener, AttributableListener, MetaDataListener
     */
    public static function _selectPostProcess($objects, $table, $idFieldName)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        // nothing to do if objects is empty
        if (is_array($objects) && 0 == count($objects)) {
            return $objects;
        }

        $tables = self::getTables();
        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_categorization"]) && $tables["{$table}_db_extra_enable_categorization"])) && System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') && 0 !== strcmp($table, 'categories_') && 0 !== strcmp($table, 'objectdata_attributes') && 0 !== strcmp($table, 'objectdata_log') && ModUtil::available('ZikulaCategoriesModule')) {
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
        if ('modules' == $table) {
            return $objects;
        }

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_attribution"]) && $tables["{$table}_db_extra_enable_attribution"]) || System::getVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) && 0 !== strcmp($table, 'objectdata_attributes') && 0 !== strcmp($table, 'objectdata_log')) {
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

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_meta"]) && $tables["{$table}_db_extra_enable_meta"]) || System::getVar('Z_CONFIG_USE_OBJECT_META')) && 0 !== strcmp($table, 'objectdata_attributes') && 0 !== strcmp($table, 'objectdata_meta') && 0 !== strcmp($table, 'objectdata_log')) {
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
     * @param string  $sql              The sql statement to execute for the selection
     * @param string  $table            The treated table reference
     * @param array   $columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param string  $permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @param integer $limitOffSet      The lower limit bound (optional) (default=-1)
     * @param integer $limitNumRows     The upper limit bound (optional) (default=-1)
     *
     * @return array The resulting object array
     */
    public static function selectObjectArraySQL($sql, $table, $columnArray = null, $permissionFilter = null, $limitOffSet = -1, $limitNumRows = -1)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $key = $sql . serialize($columnArray) . serialize($permissionFilter) . $limitOffSet . $limitNumRows;
        $objects = self::getCache($table, $key);
        if (false !== $objects) {
            return $objects;
        }

        $res = self::executeSQL($sql, $limitOffSet, $limitNumRows);
        if (false === $res) {
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
     * @param string  $table       The treated table reference
     * @param string  $field       The field to use
     * @param boolean $exitOnError Exit on error
     * @param boolean $verbose     Verbose mode
     *
     * @return integer   The result ID
     * @throws Exception IF table does not point to valid table definition, or field does not point to valif field def
     */
    public static function getInsertID($table, $field = 'id', $exitOnError = true, $verbose = true)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
     * @param string $table Table to get adodb sql string for
     *
     * @return array                    The table definition
     * @throws Exception                If table parameter is empty
     * @throws InvalidArgumentException If error in table definition
     */
    public static function getTableDefinition($table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $flag = false;
        $sql = '';

        // try to read table definitions from $table array if present
        $ddict = [];
        $tables = self::getTables();
        $tablecol = $table . '_column';
        $tabledef = $table . '_column_def';

        if (array_key_exists($tabledef, $tables) && is_array($tables[$tabledef])) {
            // we have a {$tablename}_column_def array as defined in tables.php. This is a real array, not a string.
            // The format is like "C(24) NOTNULL DEFAULT ''" which means we have to prepend the field name now
            $typemap = [
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
                'XL' => 'clob'
            ];
            $iLengthMap = [
                'I' => 4, // maps to I4
                'I1' => 1,
                'I2' => 2,
                'I4' => 4,
                'I8' => 8
            ];
            $search = ['+', '-', '*', '/', '%'];
            $replace = [''];

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
                    LogUtil::log(__('Warning! Table defintion type longblob [B, C2 and X2] is deprecated from Zikula 1.5.0.'), E_USER_DEPRECATED);
                }

                // parse type and length
                preg_match('#(B|D|C2|I1|I2|I4|I8|F|L|TS|T|X2|XL|X|(C|I)(?:\()(\d+)(?:\))|(N)(?:\()(\d+|\d+\.\d+)(?:\))|I)#', $fields[0], $matches);
                if (!$matches) {
                    throw new InvalidArgumentException(__f('Error in table definition for %1$s, column %2$s', [$table, $id]));
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
                        if (2 == count($p)) {
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
                    if ('AUTO' == $fields[$i] || 'AUTOINCREMENT' == $fields[$i]) {
                        $fAuto = true;
                    } elseif ('PRIMARY' == $fields[$i]) {
                        $fPrim = true;
                    } elseif ('NOTNULL' == $fields[$i] || 'NULL' == $fields[$i]) {
                        $fNull = $fields[$i];
                        if ($fAuto) {
                            $fNull = null;
                        }
                    } elseif ('UNSIGNED' == $fields[$i]) {
                        $fUSign = true;
                    } elseif ('DEFAULT' == $fields[$i]) {
                        if (!isset($fields[$i + 1])) {
                            throw new Exception(__f('Missing default value in field datadict specification for %1$s.%2$s', $table, $id));
                        }
                        for ($j = $i + 1; $j <= count($fields); $j++) {
                            if ($j > $i + 1) {
                                $fDef .= ' ';
                            }
                            $fDef .= str_replace(['"', "'"], ['', ''], $fields[$j]);
                            if ('NULL' == $fDef) {
                                $fDef = '';
                            }
                        }
                    }
                }

                $fieldDef = [];
                $fieldDef['type'] = $fType;
                $fieldDef['length'] = (!$fLen && isset($iLengthMap[$type]) ? $iLengthMap[$type] : $fLen);

                if ('decimal' == $fType) {
                    $fieldDef['scale'] = $fScale;
                }

                $fieldDef['autoincrement'] = $fAuto;
                $fieldDef['primary'] = $fPrim;
                $fieldDef['unsigned'] = $fUSign;
                $fieldDef['notnull'] = (null !== $fNull && 'boolean' != $fType ? ('NOTNULL' == $fNull ? true : false) : null);
                if (null != $fDef) {
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
     * @param string $table Table to get adodb sql string for
     *
     * @return string
     * @throws Exception If the table parameter is empty
     */
    public static function _getTableDefinition($table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
                if (true == $flag) {
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
     * @param string $table Treated table
     *
     * @return string    Return string to get table constraints
     * @throws Exception If the table parameter is empty or does not point to a valid table definition
     */
    public static function getTableConstraints($table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
        $constraints = '';
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
     * @param string $table Table name
     *
     * @return string Database prefix
     */
    public static function getTablePrefix($table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!isset($table)) {
            return false;
        }

        return System::getVar('prefix');
    }

    /**
     * Verify that column and column_def definition match.
     *
     * @param string $table The treated table reference
     *
     * @return boolean
     * @throws Exception If the table parameter is empty or cannot retrieve table/column def for $table
     */
    public static function verifyTableDefinitionConsistency($table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
        $search = ['+', '-', '*', '/', '%'];
        $replace = [''];
        $success = true;
        foreach ($columns as $k => $v) {
            $hasMath = (bool)(strcmp($v, str_replace($search, $replace, $v)));
            if (!$hasMath) {
                if (!isset($columnDefs[$k])) {
                    throw new Exception(__f('Inconsistent table definition detected for table [%1$s]: column [%2$s] has no counterpart in column_def structure', [$table, $k]));
                    //$success = LogUtil::registerError(__f('Inconsistent table definition detected for table [%1$s]: column [%2$s] has no counterpart in column_def structure', [$table, $k]));
                }
            }
        }
        foreach ($columnDefs as $k => $v) {
            if (!isset($columns[$k])) {
                throw new Exception(__f('Inconsistent table definition detected for table [%1$s]: column_def [%2$s] has no counterpart in column structure', [$table, $k]));
                //$success = LogUtil::registerError(__f('Inconsistent table definition detected for table [%1$s]: column_def [%2$s] has no counterpart in column structure', [$table, $k]));
            }
        }

        return true;
    }

    /**
     * Create a database table.
     *
     * @param string $table      Tablename key for the tables structure
     * @param array  $definition Doctrine table definition array
     * @param array  $tabopt     Table options specific to this table (optional)
     *
     * @throws Exception On error
     *
     * @return boolean True on success, false of failure
     */
    public static function createTable($table, $definition = null, $tabopt = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
                throw new Exception(__f('Neither the sql parameter nor the table array contain the dictionary representation of table [%s]', [$table]));
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
     * @param string  $table       Table key in pntables
     * @param array   $definition  Table definition (default = null)
     * @param array   $tabopt      Table options
     * @param booleam $dropColumns Drop columns if they don't exist in new schema (default = false)
     *
     * @return boolean
     * @throws Exception If the $table parameter is empty or failed consistency check
     */
    public static function changeTable($table, $definition = null, $tabopt = null, $dropColumns = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
                throw new Exception(__f('Neither the sql parameter nor the table array contain the dictionary representation of table [%s]', [$table]));
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
            $alterTableDefinition = ['add' => [$key => $columnDefinition]];
            try {
                $connection->export->alterTable($tableName, $alterTableDefinition);
            } catch (Exception $e) {
                return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
            }
        }

        // second round, alter table structures to match new tables definition.
        foreach ($definition as $key => $columnDefinition) {
            $alterTableDefinition = ['change' => [$key => ['definition' => $columnDefinition]]];
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
                $alterTableDefinition = ['remove' => [$key => []]];
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
     * @param string $table Table a tablename key for the tables structure
     *
     * @return boolean
     * @throws Exception If the $table param is empty or does not point to a valid table definition
     */
    public static function truncateTable($table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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

        if (false === $res) {
            return $res;
        }

        self::flushCache($table);

        return $res;
    }

    /**
     * Rename a database table.
     *
     * @param string $table    Table a tablename key for the tables structure
     * @param string $newTable NewTable a tablename key for the tables structure
     *
     * @return boolean
     * @throws Exception If the $table or $newTable parameter is empty, or do not point to valid definitons
     */
    public static function renameTable($table, $newTable)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
            Doctrine_Manager::getInstance()->getCurrentConnection()->export->alterTable($tableName, ['name' => $newTableName]);
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Table rename failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);

        return true;
    }

    /**
     * Delete a database table.
     *
     * @param string $table Table a tablename key for the tables structure
     *
     * @return boolean
     * @throws Exception If the $table parameter is empty or does not point to valid table definition
     */
    public static function dropTable($table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
     * @param string       $idxname     Name of index
     * @param string       $table       The treated table reference
     * @param array|string $flds        String field name, or non-associative array of field names
     * @param array        $idxoptarray Array of UNIQUE=true
     *
     * @return boolean
     * @throws Exception If $idxname, $table, or $flds paramters are empty
     */
    public static function createIndex($idxname, $table, $flds, $idxoptarray = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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

        $indexFields = [];
        if (!is_array($flds)) {
            $indexFields[$column[$flds]] = [];
        } else {
            foreach ($flds as $fld) {
                if (is_array($fld)) {
                    // this adds support to specifying index lengths in your pntables. So you can say
                    // $flds[] = ['path', 100];
                    // $flds[] = ['name', 10];
                    // $idxoptarray['UNIQUE'] = true;
                    // self::createIndex($idxname, $table, $flds, $idxoptarray);
                    $indexFields[$column[$fld]] = [];
                    // TODO - implement what is described in the above comment!
                } else {
                    $indexFields[$column[$fld]] = [];
                }
            }
        }

        $indexDefinition = [
            'fields' => $indexFields
        ];

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
     * @param string $idxname Index name
     * @param string $table   The treated table reference
     *
     * @return boolean
     * @throws Exception If any parameter is empty, table does not point to a valid definition
     */
    public static function dropIndex($idxname, $table)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
     * @param string  $table            The treated table reference
     * @param boolean $assoc            Associative meta column names?
     * @param boolean $notcasesensitive Normalize case of table name
     *
     * @return array of column objects
     */
    public static function metaColumns($table, $assoc = false, $notcasesensitive = true)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $rows = self::metaColumnNames($table, $assoc);
        $array = [];
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
     * @param string  $table        Table The treated table reference
     * @param boolean $numericIndex Use numeric keys
     *
     * @return array     Array of column names
     * @throws Exception If the table param is empty or does not point to a valid table definition
     */
    public static function metaColumnNames($table, $numericIndex = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = self::getTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        $rows = Doctrine_Manager::getInstance()->getCurrentConnection()->import->listTableColumns($tableName);
        $array = [];
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
     * @param string  $table   The treated table reference
     * @param boolean $primary Show only primary keys
     *
     * @return array     Array of column names
     * @throws Exception If the table parameter is empty or does not point to a valid table definition
     */
    public static function metaIndexes($table, $primary = false)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
     * @param string $table        The treated table reference
     * @param string $dbDriverName The driver used for this DB (optional)
     *
     * @deprecated
     * @see    Doctrines DBAL layer
     *
     * @return boolean
     */
    public static function getLimitedTablename($table, $dbDriverName = '')
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
                    throw new \Exception(__f('%1$s: unable to limit tablename [%2$s] because database prefix is too long for Oracle, please shorten it (recommended length is 4 chars)', [
                        __CLASS__ . '::' . __FUNCTION__,
                        DataUtil::formatForDisplay($_tablename)
                    ]));
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
     * @param string $table     Table to use
     * @param string $className Name of the class to load (default=null which generates {$table}_DBUtilRecord)
     *
     * @return string The model class
     */
    public static function buildDoctrineModuleClass($table, $className = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

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
            $length = (!empty($array) || 'null' != $length) ? ", $length" : '';
            $hasColumns .= "\$this->hasColumn('$columnName as $columnAlias', '$type'{$length}{$array});\n";
        }

        $options = '';
        foreach ($opt as $k => $v) {
            if (in_array($k, ['type', 'charset', 'collate'])) {
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
     * @param string $table     The table
     * @param string $className Name of the class to load (default=null which generates {$table}_DBUtilRecord)
     *
     * @return void
     */
    public static function loadDBUtilDoctrineModel($table, $className = null)
    {
        @trigger_error('DBUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        // don't double load
        $className = (is_null($className) ? "{$table}_DBUtilRecord" : $className);
        if (class_exists($className, false)) {
            return;
        }
        $code = self::buildDoctrineModuleClass($table, $className);
        eval($code);
    }
}
