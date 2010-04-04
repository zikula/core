<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * DBUtil
 */
class DBUtil
{
    private function __construct()
    {
    }

    /**
     * Check whether the object cache should be used for a specific query/operation
     *
     * @param tablename    The Zikula tablename
     *
     * @return true/false
     */
    public static function hasObjectCache($tablename)
    {
        return ($tablename != 'session_info' && !defined('_ZINSTALLVER') && DBConnectionStack::isDefaultConnection() && pnConfigGetVar('OBJECT_CACHE_ENABLE'));
    }


    public static function getCache($table, $key)
    {
        if (self::hasObjectCache($table)) {
            $key = md5($key);
            $prefix = md5(DBConnectionStack::getConnectionDSN());
            $cacheDriver = DBConnectionStack::getCacheDriver();
            return $cacheDriver->fetch($prefix.$table.$key);
        }

        return false;
    }


    public static function setCache($table, $key, $fields)
    {
        if (self::hasObjectCache($table)) {
            $key = md5($key);
            $prefix = md5(DBConnectionStack::getConnectionDSN());
            $cacheDriver = DBConnectionStack::getCacheDriver();
            $cacheDriver->save($prefix.$table.$key, $fields);
        }
    }


    public static function flushCache($table)
    {
        if (self::hasObjectCache($table)) {
            $prefix = md5(DBConnectionStack::getConnectionDSN());
            $cacheDriver = DBConnectionStack::getCacheDriver();
            $cacheDriver->deleteByPrefix($prefix.$table);
        }
    }

    /**
     * return server information
     *
     * @return array of server info
     */
    public static function serverInfo()
    {
        $connection = Doctrine_Manager::connection();

        // we will form an array to keep formally compatible to the old ado-db style for now
        return array('description' => $connection->getAttribute(PDO::ATTR_SERVER_INFO),
                     'version' => $connection->getAttribute(PDO::ATTR_CLIENT_VERSION));
    }

    /**
     * create database
     *
     * @param  dbname the database name
     * @param  optionsarray the options array
     * @return bool
     */
    public static function createDatabase($dbname, $optionsarray = false)
    {
        if (empty($dbname)) {
            throw new Exception(__f('The parameter %s must not be empty', 'dbname'));
        }

        $connection = Doctrine_Manager::connection();

        try {
            // create the new database
            // TODO C [use $optionsarray in DBUtil::createDatabase() for backwards compatability] (Guite)
            $connection->export->createDatabase($dbname);
            return true;
        }
        catch (Exception $e) {
            echo 'Database error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * get a list of databases available on the server
     *
     * @return  array of databases
     */
    public static function metaDatabases()
    {
        return Doctrine_Manager::connection()->import->listDatabases();
    }

    /**
     * get a list of tables in the currently connected database
     *
     * @param  ttype type of 'tables' to get
     * @param  showSchema add the schema name to the table
     * @param  mask mask to apply to return result set
     * @return  array of tables
     */
    public static function metaTables($ttype = false, $showSchema = false, $mask = false)
    {
        return Doctrine_Manager::connection()->import->listTables();
        //return $dbconn->MetaTables($ttype, $showSchema, $mask);
    }

    /**
     * get a list of database tables
     *
     * @deprecated
     * @return array array of database tables
     */
    public static function getTables()
    {
       return pnDBGetTables();
    }

    /**
     * get a list of dbms specific table options
     *
     * For use by ADODB's data dictionary
     * Additional database specific settings can be defined here
     * See ADODB's data dictionary docs for full details
     *
     * @param $table (optional, string with table name).
     * If $table param is set and there is a set of options configured
     * for this table via pntables.php then we return these options,
     * the default options are returned otherwise
     */
    public static function getTableOptions($table = '')
    {
        if ($table != '') {
            $tables = pnDBGetTables();
            if (isset($tables[$table . '_def'])) {
                return $tables[$table . '_def'];
            }
        }

        $dbType = DBConnectionStack::getConnectionDBType();
        if ($dbType == 'mysql' || $dbType == 'mysqli') {
            $tableoptions['type'] = DBConnectionStack::getConnectionDBTableType();
        }

        $tableoptions['charset'] = DBConnectionStack::getConnectionDBCharset();
        $tableoptions['collate'] = DBConnectionStack::getConnectionDBCollate();

        return $tableoptions;
    }


    /**
     * Execute SQL, check for errors and return result. Uses Doctrine's DBAL to generate DB-portable paging code.
     *
     * @param sql          The SQL statement to execute
     * @param limitOffset  The lower limit bound (optional) (default=-1)
     * @param limitNumRows The upper limit bound (optional) (default=-1)
     * @param sql          The SQL statement to execute
     * @param exitOnError  whether to exit on error (default=true) (optional)
     * @param verbose      whether to be verbose (default=true) (optional)
     * @return mixed The result set of the successfully executed query or false on error
     */
    public static function executeSQL($sql, $limitOffset = -1, $limitNumRows = -1, $exitOnError = true, $verbose = true)
    {
        if (!$sql) {
            throw new Exception(__('No SQL statement to execute'));
        }

        $connection = Doctrine_Manager::connection();

        if (!$connection && defined('_ZINSTALLVER')) {
            return false;
        }

        global $ZConfig;
        $suid = $ZConfig['Debug']['sql_user'];
        $uid  = SessionUtil::getVar('uid', 0);
        if (($ZConfig['Debug']['sql_time'] || $ZConfig['Debug']['sql_detail']) ||
            (!$suid || ($suid && $suid === $uid))) {
            static $timer;
            if (!$timer) {
                $timer = new Timer();
            }
            else {
                $timer->reset();
            }
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
                global $ZRuntime;
                $uid  = SessionUtil::getVar('uid', 0);
                $suid = $ZConfig['Debug']['sql_user'];

                if (!$suid || (is_array($suid) && in_array($uid)) || ($suid === $uid)) {
                    if ($ZConfig['Debug']['sql_count']) {
                        $ZRuntime['sql_count_request'] += 1;
                    }

                    if ($ZConfig['Debug']['sql_time'] || $ZConfig['Debug']['sql_detail']) {
                        $diff = $timer->snap(true);
                        $ZRuntime['sql_time_request'] += $diff['diff'];
                        if ($ZConfig['Debug']['sql_detail']) {
                            $sqlstat = array();
                            $sqlstat['stmt'] = $sql;
                        }
                        if ($limitNumRows > 0) {
                            $sqlstat['limit'] = "$limitOffset, $limitNumRows";
                        }
                        if ($ZConfig['Debug']['sql_detail']) {
                            $sqlstat['rows_affected'] = $result->_numOfRows;
                            $sqlstat['time'] = $diff['diff'];
                            $ZRuntime['sql'][] = $sqlstat;
                        }
                    }
                }

                return $result;
            }
        }
        catch (Exception $e) {
            die('Error in DBUtil::executeSQL: ' . $sql . '<br />' . $e->getMessage() . '<br />' . nl2br($e->getTraceAsString()));
        }
/*
        if ($verbose) {
            print '<br />' . $dbconn->ErrorMsg() . '<br />' . $sql . '<br />';
        }

        if ($exitOnError) {
            throw new Exception(__('Exiting after SQL-error'));
        }
*/
        return false;
    }

/**
     * Same as Api function but without AS aliasing
     *
     * @param table          The treated table reference
     * @param columnArray    The columns to marshall into the resulting object (optional) (default=null)
     * @return The generated sql string
     */
    public static function _getAllColumns($table, $columnArray = null)
    {
        $tables = pnDBGetTables();
        $columns = $tables["{$table}_column"];
        if (!$columns) {
            throw new Exception(__f('Invalid table-key [%s] retrieved', $table));
        }
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
     * Same as Api function but returns fully qualified fieldnames
     *
     * @param table          The treated table reference
     * @param tablealias     The SQL table alias to use in the SQL statement
     * @param columnArray    The columns to marshall into the resulting object (optional) (default=null)
     * @return The generated sql string
     */
    public static function _getAllColumnsQualified($table, $tablealias, $columnArray = null)
    {
        $search  = array('+', '-', '*', '/', '%');
        $replace = array('');

        $tables = pnDBGetTables();
        $columns = $tables["{$table}_column"];
        if (!$columns) {
            throw new Exception(__f('Invalid table-key [%s] retrieved', $table));
        }

        foreach ($columns as $key => $val) {
            if (!$columnArray || in_array($key, $columnArray)) {
                $hasMath = (bool)(strcmp ($val, str_replace($search, $replace, $val)));
                if (!$hasMath) {
                    $queriesResult[] = $tablealias . '.' . $val . ' AS "' . $key. '"';
                } else {
                    $queriesResult[] = $val . ' AS "' . $key. '"';
                }
            }
        }

        if (!$queriesResult && $columnArray) {
            throw new Exception(__f('Empty query generated for [%s] filtered by columnArray', $table));
        }

        return implode(',', $queriesResult);
    }

    /**
     * return the column array for the given table
     *
     * @param table          The treated table reference
     * @param columnArray    The columns to marshall into the resulting object (optional) (default=null)
     * @return The column array for the given table
     */
    public static function getColumnsArray($table, $columnArray = null)
    {
        $columnArrayResult = array();

        $tables = pnDBGetTables();
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
     * Expand column array with JOIN-Fields
     *
     * This adds all joined fields to the column array by their alias defined in $joinInfo.
     * Also it adds the field's table alias to avoid ambiguous queries.
     *
     * @param array $columns    Column array
     * @param array $joinInfo   JoinInfo array
     * @return array            Expanded column array
     * @deprecated
     * @see    Doctrine_Record
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

        $tables = pnDBGetTables();
        // add fields of all joins
        $alias = 'a';
        foreach ($joinInfo as &$join) {
            $jc =& $tables[$join['join_table'].'_column'];
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
     * rename column(s) in a table
     *
     * @param  table     The treated table reference
     * @param  oldcolumn The existing name of the column (full database name of column)
     * @param  newcolumn The new name of the column from the pntables array
     * @param  fields    field specific options (optional) (default=null)
     * @return  bool
     */
    public static function renameColumn($table, $oldcolumn, $newcolumn, $fields = null)
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

        $tables = pnDBGetTables();
        $tableName = $tables[$table];
        if (!isset($fields) || empty($fields)) {
            $fields = $tables["{$table}_column"][$newcolumn] . ' ' . $tables["{$table}_column_def"][$newcolumn];
        }
        $oldcolumn = isset($tables["{$table}_column"][$oldcolumn]) ? $tables["{$table}_column"][$oldcolumn] : $oldcolumn;
        $newcolumn = $tables["{$table}_column"][$newcolumn];

        try {
            Doctrine_Manager::connection()->export->alterTable($tableName, array('rename' => array($oldcolumn => array('name' => $newcolumn))));
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Column rename failed.') . ' ' . $e->getMessage());
        }
        self::flushCache($table);
        return true;
    }

    /**
     * add column(s) to a table
     *
     * @param  table     The treated table reference
     * @param  fields    fields to add from the table
     * @return  bool
     */
    public static function addColumn($table, array $fields)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        if (empty($fields)) {
            throw new Exception(__f('The parameter %s must not be empty', 'fields'));
        }

        if (!is_array($fields[0])) {
            throw new Exception(__f('The parameter %s must be an array of field arrays', 'fields'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        try {
            $connection = Doctrine_Manager::connection();
            foreach ($fields as $field) {
                $options = self::getTableOptions($table);
                $connection->export->alterTable($tableName, array('add' => array($field => $options)), true);
            }
        }
        catch (Exception $e) {
            return LogUtil::registerError(__('Error! Column creation failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);
        return true;
    }

    /**
     * drop column(s) from a table
     *
     * @param  table     The treated table reference
     * @param  fields    fields to drop from the table
     * @return  bool
     */
    public static function dropColumn($table, $fields)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        if (empty($fields)) {
            throw new Exception(__f('The parameter %s must not be empty', 'fields'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        try {
            $connection = Doctrine_Manager::connection();
            foreach ($fields as $field) {
                $connection->export->alterTable($tableName, array('remove' => array($field => array())), true);
            }
        }
        catch (Exception $e) {
            return LogUtil::registerError(__('Error! Column deletion failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);
        return true;
    }

/**
     * Format value for use in SQL statement
     *
     * Special handling for integers and booleans (the last is required for MySQL 5 strict mode)
     *
     * @param @value mixed the value to format
     * @return string string ready to add to SQL statement
     */
    public static function _formatForStore($value)
    {
        if (is_int($value)) {
            return (int)$value;          // No need to DataUtil::formatForStore when casted to int
        } else if ($value === false) {   // Avoid SQL strict problems where false would be stored as ''
            return 0;
        } else if ($value === true) {
            return 1;
        }

        return '\'' . DataUtil::formatForStore((string)$value) . '\'';
    }

    /**
     * Generate and execute an insert SQL statement for the given object
     *
     * @param object        The object we wish to insert
     * @param table         The treated table reference
     * @param idfield       The column which stores the primary key (optional) (default='id')
     * @param preserve      whether or not to preserve existing/set standard fields (optional) (default=false)
     * @param force         whether or not to insert empty values as NULL (optional) (default=false)
     * @return The result set from the update operation. The object is updated with the newly generated ID.
     * @deprecated
     * @see Doctrine_Record::save()
     * @deprecated
     * @see Doctrine_Table
     */
    public static function insertObject(array &$object, $table, $idfield = 'id', $preserve = false, $force = false)
    {
        $tables = pnDBGetTables();
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
        $search  = array('+', '-', '*', '/', '%');
        $replace = array('');
        $cArray  = array();
        $vArray  = array();

        $dbType = DBConnectionStack::getConnectionDBType ();
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
            }
            else {
                if ($key == $idfield) {
                    if ($dbType == 'pgsql') {
                        $cArray[] = $columnList[$key];
                        $vArray[] = 'DEFAULT';
                    }
                }
                elseif ($force) {
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

        if ((!$preserve || !isset($object[$idfield])) && isset($object[$idfield])) {
            $obj_id = self::getInsertID($table, $idfield);
            $object[$idfield] = $obj_id;
        }

        if (!DBConnectionStack::isDefaultConnection()) {
            return $object;
        }

        if ($cArray && $vArray) {
             $object = self::_savePostProcess($object, $table, $idfield);
        }

        return $object;
    }

    /**
     * Generate and execute an update SQL statement for the given object
     *
     * @param object        The object we wish to update
     * @param table         The treated table reference
     * @param where         The where clause (optional) (default='')
     * @param idfield       The column which stores the primary key (optional) (default='id')
     * @param force         whether or not to insert empty values as NULL (optional) (default=false)
     * @param updateid      Allow primary key to be updated (default=false)
     * @return The result set from the update operation
     * @deprecated
     * @see Doctrine_Record::save()
     */
    public static function updateObject(array &$object, $table, $where = '', $idfield = 'id', $force = false, $updateid = false)
    {
        if (!isset($object[$idfield]) && !$where) {
            throw new Exception(__('Neither object ID nor where parameters are provided'));
        }

        $tables = pnDBGetTables();
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
        $tArray  = array();
        $search  = array ('+', '-', '*', '/', '%');
        $replace = array ('');

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

        if (!DBConnectionStack::isDefaultConnection()) {
            return $object;
        }

        $object = self::_savePostProcess($object, $table, $idfield, true);

        return $object;
    }

    /**
     * Loop through the array and feed it to self::insertObject()
     *
     * @param objects       The objectArray we wish to insert
     * @param table         The treated table reference
     * @param idfield       The column which stores the primary key (optional) (default='id')
     * @param preserve      whether or not to preserve existing/set standard fields (optional) (default=false)
     * @param force         whether or not to insert empty values as NULL (optional) (default=false)
     * @return The result set from the last insert operation. The objects are updated with the newly generated ID.
     * @deprecated
     * @see Doctrine_Table
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
     * Loop through the array and feed it to self::updateObject()
     *
     * @param objects       The objectArray we wish to insert
     * @param table         The treated table reference
     * @param idfield       The column which stores the primary key
     * @param force         whether or not to insert empty values as NULL
     * @return The result set from the last update operation.
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
     * Post-processing after this object has beens saved. This routine
     * is responsible for writing the 'extra' data (attributes, categories,
     * and meta data) to the database and the optionally creating an
     * entry in the object-log table
     *
     * @param  object  the object wehave just saved
     * @param  table   the treated table reference
     * @param  idfield the id column for the object/table combination
     * @param  update whether or not this was an update (default=false, signifies operation was an insert).
     * @return the object
     * @deprecated
     * @see CategorisableListener, AttributableListener, MetaDataListener, LoggableListener
     */
    private static function _savePostProcess($object, $table, $idfield, $update = false)
    {
        $tables = pnDBGetTables();
        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (!$idfield) {
            throw new Exception(__f('Invalid idfield received', $table));
        }

        if (($enableAllServices ||
            (isset($tables["{$table}_db_extra_enable_categorization"]) && $tables["{$table}_db_extra_enable_categorization"])  ) &&
            pnConfigGetVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') &&
            strcmp($table, 'categories_') !== 0 &&
            strcmp($table, 'objectdata_attributes') !== 0 &&
            strcmp($table, 'objectdata_log') !== 0 &&
            pnModAvailable('Categories'))
        {
            ObjectUtil::storeObjectCategories($object, $table, $idfield, $update);
        }

        if (!pnModAvailable('ObjectData')) {
            return $object;
        }

        if (($enableAllServices ||
            (isset($tables["{$table}_db_extra_enable_attribution"]) && $tables["{$table}_db_extra_enable_attribution"] ) ||
            pnConfigGetVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) &&
            strcmp($table, 'objectdata_attributes') !== 0 &&
            strcmp($table, 'objectdata_log') !== 0)
        {
            ObjectUtil::storeObjectAttributes($object, $table, $idfield, $update);
        }

        if (($enableAllServices ||
            (isset($tables["{$table}_db_extra_enable_meta"]) && $tables["{$table}_db_extra_enable_meta"] ) ||
            pnConfigGetVar('Z_CONFIG_USE_OBJECT_META')) &&
            $table != 'objectdata_attributes' &&
            $table != 'objectdata_meta' &&
            $table != 'objectdata_log')
        {
            ObjectUtil::updateObjectMetaData($object, $table, $idfield);
        }

        if (($enableAllServices ||
            (isset($tables["{$table}_db_extra_enable_logging"]) && $tables["{$table}_db_extra_enable_logging"])  ) &&
            pnConfigGetVar('Z_CONFIG_USE_OBJECT_LOGGING') &&
            strcmp($table, 'objectdata_log') !== 0 && !$where)
        {
            $oldObj = self::selectObjectByID($table, $object[$idfield], $idfield);

            $log = new ObjectData_Log();
            $log['object_type'] = $table;
            $log['object_id']   = $object[$idfield];
            $log['op']          = ($update ? 'U' : 'I');

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
     * Increment a field by the given increment
     *
     * @param table         The treated table reference
     * @param incfield      The column which stores the field to increment
     * @param id            The ID value of the object holding the field we wish to increment
     * @param idfield       The idfield to use (optional) (default='id')
     * @param inccount      The amount by which to increment the field (optional) (default=1);
     * @return The result from the increment operation
     */
    public static function incrementObjectFieldByID($table, $incfield, $id, $idfield = 'id', $inccount = 1)
    {
        $tables       = pnDBGetTables();
        $tableName    = $tables[$table];
        $columns      = $tables["{$table}_column"];
        $idFieldName  = $columns[$idfield];
        $incFieldName = $columns[$incfield];
        $column       = $tables["{$table}_column"];

        $sql  = 'UPDATE ' . $tableName . " SET $incFieldName = $column[$incfield] + $inccount";
        $sql .= " WHERE $idFieldName = '" . DataUtil::formatForStore($id) . "'";

        $res = self::executeSQL($sql);
        if ($res === false) {
            return false;
        }

        self::flushCache($table);

        return $res;
    }

    /**
     * Decrement a field by the given decrement
     *
     * @param table         The treated table reference
     * @param decfield      The column which stores the field to increment
     * @param id            The ID value of the object holding the field we wish to increment
     * @param idfield       The idfield to use (optional) (default='id')
     * @param deccount      The amount by which to decrement the field (optional) (default=1);
     * @return The result from the decrement operation
     */
    public static function decrementObjectFieldByID($table, $decfield, $id, $idfield = 'id', $deccount = 1)
    {
        return self::incrementObjectFieldByID($table, $decfield, $id, $idfield, 0 - $deccount);
    }

    /**
     * Generate and execute a delete SQL statement for the given object
     *
     * @param object       The object we wish to delete
     * @param table        The treated table reference
     * @param where        The where clause to use (optional) (default='')
     * @param idfield      The column which contains the ID field (optional) (default='id')
     * @return The result from the delete operation
     * @deprecated
     * @see CategorisableListener, AttributableListener, MetaDataListener, LoggableListener
     */
    public static function deleteObject(array $object, $table, $where = '', $idfield = 'id')
    {
        if ($object && $where) {
            throw new Exception(__("Can't specify both object and where-clause"));
        }

        if (!$object && !$where) {
            throw new Exception(__('Missing either object or where-clause'));
        }

        $tables    = pnDBGetTables();
        $tableName = $tables[$table];
        $columns   = $tables["{$table}_column"];
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
        if (!DBConnectionStack::isDefaultConnection() || $where) {
            return $object;
        }

        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (($enableAllServices ||
            (isset($tables["{$tableName}_db_extra_enable_categorization"]) && $tables["{$tableName}_db_extra_enable_categorization"])  ) &&
            pnConfigGetVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') &&
            $tableName != 'categories_' &&
            $tableName != 'objectdata_attributes' &&
            $tableName != 'objectdata_log' &&
            pnModAvailable('Categories'))
        {
            ObjectUtil::deleteObjectCategories ($object, $tableName, $idcolumn);
        }

        if (((isset($tables["{$tableName}_db_extra_enable_all"]) && $tables["{$tableName}_db_extra_enable_all"]) ||
             (isset($tables["{$tableName}_db_extra_enable_attribution"]) && $tables["{$tableName}_db_extra_enable_attribution"] ) ||
            pnConfigGetVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) &&
            $tableName != 'objectdata_attributes' &&
            $tableName != 'objectdata_log' &&
            pnModAvailable('ObjectData'))
        {
            ObjectUtil::deleteObjectAttributes ($object, $tableName, $idcolumn);
        }

        if (($enableAllServices ||
            (isset($tables["{$tableName}_db_extra_enable_meta"]) && $tables["{$tableName}_db_extra_enable_meta"] ) ||
            pnConfigGetVar('Z_CONFIG_USE_OBJECT_META')) &&
            $tableName != 'objectdata_attributes' &&
            $tableName != 'objectdata_meta' &&
            $tableName != 'objectdata_log' &&
            pnModAvailable('ObjectData'))
        {
            ObjectUtil::deleteObjectMetaData ($object, $tableName, $idcolumn);
        }

        if (($enableAllServices ||
            (isset($tables["{$table}_db_extra_enable_logging"]) && $tables["{$table}_db_extra_enable_logging"])  ) &&
            pnConfigGetVar('Z_CONFIG_USE_OBJECT_LOGGING') &&
            strcmp($table, 'objectdata_log') !== 0)
        {
            $log = new ObjectData_Log();
            $log['object_type'] = $table;
            $log['object_id']   = $object[$idfield];
            $log['op']          = 'D';
            $log['diff']        = serialize($object);
            $log->save();
        }

        return $res;
    }


    /**
     * generate and execute a delete SQL statement
     *
     * @param array $keyarray
     * @param mixed $table
     * @param string $field
     * @return unknown
     */
    public static function deleteObjectsFromKeyArray(array $keyarray, $table, $field = 'id')
    {
        $tables    = pnDBGetTables();
        $tableName = $tables[$table];
        $columns   = $tables["{$table}_column"];
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
     * @param table       The treated table reference
     * @param id          The ID of the object to delete
     * @param idFieldName The column which contains the ID field (optional) (default='id')
     * @return The result from the delete operation
     */
    public static function deleteObjectByID($table, $id, $idFieldName = 'id')
    {
        $object = array();
        $object[$idFieldName] = $id;
        return self::deleteObject($object, $table, '', $idFieldName);
    }

    /**
     * Delete (an) object(s) via a where clause
     *
     * @param table        The treated table reference
     * @param where        The where-clause to use
     * @return The result from the delete operation
     */
    public static function deleteWhere($table, $where)
    {
        $tables    = pnDBGetTables();
        $tableName = $tables[$table];
        $where     = self::_checkWhereClause($where);
        $sql       = 'DELETE FROM ' . $tableName . ' ' . $where;
        return self::executeSQL($sql);
    }

/**
     * Convenience function to ensure that the where-clause starts with "WHERE"
     *
     * @param where        The original where clause
     * @return The value held by the global counter
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
     * @param orderby    The original order-by clause.
     * @param tableName  The table reference, only used for oracle quote determination (optional) (default=null).
     *
     * @return The (potentially) altered order-by-clause.
     */
    public static function _checkOrderByClause($orderby, $table = null)
    {
        if (!strlen(trim($orderby))) {
            return $orderby;
        }

        $tables = pnDBGetTables();
        $dbType = DBConnectionStack::getConnectionDBType();

        // given that we use quotes in our generated SQL, oracle requires the same quotes in the order-by
        if ($dbType == 'oracle') {
            $t = str_replace('ORDER BY ', '', $orderby); // remove "ORDER BY" for easier parsing
            $t = str_replace('order by ', '', $t); // remove "order by" for easier parsing


            $columns = $tables["{$tableName}_column"];

            // anything which doesn't look like a basic ORDER BY clause (with possibly an ASC/DESC modifier)
            // we don't touch. To use such stuff with Oracle, you'll have to apply the quotes yourself.


            $tokens = explode(',', $t); // split on comma
            foreach ($tokens as $k => $v) {
                $v = trim($v);
                if (strpos($v, ' ') === false) { // 1 word
                    if (strpos($v, '(') === false) { // not a function call
                        if (strpos($v, '"') === false) { // not surrounded by quotes already
                            if (isset($columns[$v])) { // ensure that token is an alias
                                $tokens[$k] = '"' . $v . '"'; // surround it by quotes
                            }
                        }
                    }
                } else { // multiple words, perform a few basic hecks
                    $ttok = explode(' ', $v); // split on space
                    if (count($ttok) == 2) { // see if we have 2 tokens
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
     * Convenience function to ensure that the field to be used as ORDER BY
     * is not a CLOB/BLOB when using Oracle
     *
     * @param table     The treated table reference
     * @param field     The field name to be used for order by
     * @return string   the order-by-clause to be used, may be ''
     */
    public static function _checkOrderByField($table = '', $field = '')
    {
        $orderby = '';

        if (empty($field) || empty($table)) {
            return $orderby;
        }

        $dbType = DBConnectionStack::getConnectionDBType();
        $tables = pnDBGetTables();
        $columns = $tables["{$table}_column"];
        $columnsdef = $tables["{$table}_column_def"];
        $fieldName = $columns[$field];
        $fieldDef = $columnsdef[$field];

        if ($dbType == 'oracle') {
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
     * Build a basic select clause for the specified table with the specified where and orderBy clause
     *
     * @param table          The treated table reference
     * @param where          The original where clause (optional) (default='')
     * @param orderBy        The original order-by clause (optional) (default='')
     * @param columnArray    The columns to marshall into the resulting object (optional) (default=null)
     * @return Nothing, the order-by-clause is altered in place
     */
    public static function _getSelectAllColumnsFrom($table, $where = '', $orderBy = '', $columnArray = null)
    {
        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        $query = 'SELECT ' . self::_getAllColumns($table, $columnArray) . " FROM $tableName AS tbl ";

        if (trim($where)) {
            $query .= self::_checkWhereClause($where) . ' ';
        }

        if (trim($orderBy)) {
            $query .= self::_checkOrderByClause($orderBy, $table) . ' ';
        }

        return $query;
    }

    /**
     * Set the gobal object fetch counter to the specified value
     *
     * This function is workaround for PHP4 limitations when passing default arguments by reference
     *
     * @param count        The value to set the object marhsall counter to
     * @return Nothing, the global variable is assigned counter
     */
    public static function _setFetchedObjectCount($count = 0)
    {
        // TODO D [remove PHP4 stuff in DBUtil] (Guite)
        $GLOBALS['DBUtilFetchObjectCount'] = $count;
    }

    /**
     * Get the gobal object fetch counter
     *
     * This function is workaround for PHP4 limitations when passing default arguments by reference
     *
     * @return The value held by the global
     * @deprecated
     */
    public static function _getFetchedObjectCount()
    {
        // TODO D [remove PHP4 stuff in DBUtil] (Guite)
        if (isset($GLOBALS['DBUtilFetchObjectCount'])) {
            return (int) $GLOBALS['DBUtilFetchObjectCount'];
        }

        return false;
    }

    /**
     * Transform a result set into an array of field values
     *
     * @param result          The result set we wish to marshall
     * @param closeResultSet  whether or not to close the supplied result set (optional) (default=true)
     * @param assocKey        The key field to use to build the associative index (optional) (default='')
     * @param clean            whether or not to clean up the marshalled data (optional) (default=true)
     * @return The resulting field array
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

        global $ZConfig;
        if ($ZConfig['Debug']['sql_detail']) {
            $uid = SessionUtil::getVar('uid', 0);
            $suid = $ZConfig['Debug']['sql_user'];
            if (!$suid || (is_array($suid) && in_array($uid)) || ($suid === $uid)) {
                global $ZRuntime;
                $last = count($ZRuntime['sql']);
                $ZRuntime['sql'][$last - 1]['rows_marshalled'] = count($fieldArray);
            }
        }

        return $fieldArray;
    }

    /**
     * Transform a SQL query result set into an object/array, optionally applying an permission filter
     *
     * @param result           The result set we wish to marshall
     * @param objectColumns    The column array to map onto the result set
     * @param closeResultSet   whether or not to close the supplied result set (optional) (default=true)
     * @param assocKey         The key field to use to build the associative index (optional) (default='')
     * @param clean            whether or not to clean up the marshalled data (optional) (default=true)
     * @param permissionFilter The permission structure to use for permission checking (optional) (default=null)
     * @return The marshalled array of objects
     */
    public static function marshallObjects($result, $objectColumns, $closeResultSet = true, $assocKey = '', $clean = true, $permissionFilter = null)
    {
        if (!$result) {
            throw new Exception(__f('The parameter %s must not be empty', 'result'));
        }

        if (!$objectColumns) {
            throw new Exception(__f('The parameter %s must not be empty', 'objectColumns'));
        }

        if ($assocKey && !in_array($assocKey, $objectColumns)) {
            throw new Exception(__f('Unable to find assocKey [%s] in objectColumns for table [%s]', array(
                $assocKey, $table)));
        }

        // since the single-object selects don't need to init
        // the paging logic, we ensure values are set here
        // in order to avoid E_ALL issues
        if (!isset($GLOBALS['DBUtilFetchObjectCount'])) {
            self::_setFetchedObjectCount(0);
        }

        $object = array();
        $objectArray = array();
        $cSize = count($objectColumns);
        $fetchedObjectCount = 0;

        $resultRows = $result->fetchAll(Doctrine::FETCH_ASSOC);
        foreach ($resultRows as $resultRow) {
            $fetchedObjectCount++;
            $object = $resultRow;

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

        global $ZConfig;
        global $ZRuntime;
        $debugSettings = $ZConfig['Debug'];
        $uid = SessionUtil::getVar('uid', 0);
        $suid = $debugSettings['sql_user'];

        if ($debugSettings['sql_detail']) {
            if (!$suid || (is_array($suid) && in_array($uid)) || ($suid === $uid)) {
                $last = count($ZRuntime['sql']);
                $ZRuntime['sql'][$last - 1]['rows_marshalled'] = count($objectArray);
            }
        }

        if ($debugSettings['sql_data']) {
            if (!$suid || (is_array($suid) && in_array($uid)) || ($suid === $uid)) {
                $last = count($ZRuntime['sql']);
                $ZRuntime['sql'][$last - 1]['rows'] = $objectArray;
            }
        }

        self::_setFetchedObjectCount($fetchedObjectCount);

        return $objectArray;
    }

    /**
     * Execute SQL select statement and return the value of the first column in the first row
     *
     * Mostly useful for places where you want to do a "select count(*)" or similar scalar selection.
     *
     * @return mixed selected value
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
     * Select & return a field
     *
     * @param table         The treated table reference
     * @param field         The name of the field we wish to marshall
     * @param where         The where clause (optional) (default='');
     *
     * @return The resulting field array
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
     * @param tableName     The treated table reference.
     * @param field         The field we wish to select.
     * @param id            The ID value we wish to select with.
     * @param idfield       The idfield to use (optional) (default='id');.
     * @return The resulting field value.
     */
    public static function selectFieldByID($tableName, $field, $id, $idfield = 'id')
    {
        $tables = pnDBGetTables();
        $cols = $tables["{$tableName}_column"];
        $idFieldName = $cols[$idfield];

        $where = $idFieldName . " = '" . DataUtil::formatForStore($id) . "'";
        return self::selectField($tableName, $field, $where);
    }

    /**
     * Select & return a field array
     *
     * @param table        The treated table reference
     * @param field        The name of the field we wish to marshall
     * @param where        The where clause (optional) (default='')
     * @param orderby      The orderby clause (optional) (default='')
     * @param distinct     whether or not to add a 'DISTINCT' clause (optional) (default=false)
     * @param assocKey     The key field to use to build the associative index (optional) (default='')
     * @return The resulting field array
     */
    public static function selectFieldArray($table, $field, $where = '', $orderby = '', $distinct = false, $assocKey = '')
    {
        $key = $field . $where . $orderby . $distinct . $assocKey;
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        $tables = pnDBGetTables();
        if (!isset($tables["{$table}_column"])) {
            return false;
        }

        $columns = $tables["{$table}_column"];
        $tableName = $tables[$table];
        $dSql = ($distinct ? "DISTINCT($columns[$field])" : "$columns[$field]");
        if ($assocKey) {
            $assocColumn = $columns[$assocKey];
        }

        $assoc = ($assocKey ? ", $assocColumn" : '');
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
     * @param tableName    The treated table reference.
     * @param field        The field we wish to select.
     * @param id           The ID value we wish to select with.
     * @param idfield       The idfield to use (optional) (default='id');.
     *
     * @return The resulting field value.
     */
    public static function selectFieldArrayByID($tableName, $field, $id, $idfield = 'id')
    {
        $tables = pnDBGetTables();
        $cols = $tables["{$tableName}_column"];
        $idFieldName = $cols[$idfield];

        $where = $idFieldName . " = '" . DataUtil::formatForStore($id) . "'";

        return self::selectFieldArray($tableName, $field, $where);
    }

    /**
     * Select & return the max/min value of a field
     *
     * @param table        The treated table reference
     * @param field         The name of the field we wish to marshall
     * @param option        MIN, MAX, SUM or COUNT (optional) (default='MAX')
     * @param where         The where clause (optional) (default='')
     * @return The resulting min/max value
     */
    public static function selectFieldMax($table, $field, $option = 'MAX', $where = '')
    {
        $tables = pnDBGetTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        $sql = "SELECT $option($fieldName) FROM $tableName AS tbl";
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
     * Select & return the max/min array of a field grouped by the associated key
     *
     * @param table        The treated table reference
     * @param field         The name of the field we wish to marshall
     * @param option        MIN, MAX, SUM or COUNT (optional) (default='MAX')
     * @param where         The where clause (optional) (default='')
     * @param assocKey      The key field to use to build the associative index (optional) (default='' which defaults to the primary key)
     * @return The resulting min/max value
     */
    public static function selectFieldMaxArray($table, $field, $option = 'MAX', $where = '', $assocKey = '')
    {
        $tables = pnDBGetTables();
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
     * Build a list of objects which are mapped to the specified categories
     *
     * @param table            treated table reference
     * @param categoryFilter   The category list to use for filtering
     * @param returnArray      Whether or not to return an array (optional) (default=false)
     * @return The resulting string or array
     */
    public static function _generateCategoryFilter($table, $categoryFilter, $returnArray = false)
    {
        if (!$categoryFilter) {
            return '';
        }

        if (!pnModDBInfoLoad('Categories')) {
            return '';
        }

        // check the meta data
        $modname = '';
        if (isset($categoryFilter['__META__']['module'])) {
            $modname = $categoryFilter['__META__']['module'];
            unset($categoryFilter['__META__']);
        } else {
            $modname = pnModGetName();
        }

        // get the properties IDs in the category register
        $propids = CategoryRegistryUtil::getRegisteredModuleCategoriesIds($modname, $table);

        // build the where clause
        $where = array();
        foreach ($categoryFilter as $property => $category) {
            if (is_array($category)) {
                // we have an array of categories IDs
                $wherecat = array();
                foreach ($category as $cat) {
                    $wherecat[] = 'cmo_category_id = \'' . DataUtil::formatForStore($cat) . '\'';
                }
                $wherecat = '(' . implode(' OR ', $wherecat) . ')';
            } else {
                // there is only one category ID
                $wherecat = 'cmo_category_id = \'' . DataUtil::formatForStore($category) . '\'';
            }
            $where[] = '(cmo_reg_id = \'' . DataUtil::formatForStore($propids[$property]) . '\' AND ' . $wherecat . ')';
        }

        $where = 'cmo_table = \'' . DataUtil::formatForStore($table) . '\' AND (' . implode(' OR ', $where) . ')';

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
     * @param table            The treated table reference
     * @param where            The where clause (optional) (default='')
     * @param categoryFilter   The category list to use for filtering
     * @param returnArray      Whether or not to return an array (optional) (default=false)
     * @param usesJoin         Whether a join is used (if yes, then a prefix is prepended to the column name) (optional) (default=false)
     * @return The resulting string or array
     */
    public static function generateCategoryFilterWhere($table, $where, $categoryFilter, $returnArray = false, $usesJoin = false)
    {
        $tables = pnDBGetTables();
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
     * Select & return a specific object using the given sql statement
     *
     * @param sql              The sql statement to execute for the selection
     * @param table            The treated table reference
     * @param columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param permissionFilter The permission filter to use for permission checking (optional) (default=null)
     *
     * @return The resulting object
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

        $ca = self::getColumnsArray($table, $columnArray);
        $objArr = self::marshallObjects($res, $ca, true, '', true, $permissionFilter);

        if (count($objArr) > 0) {
            return $objArr[0];
        }
    }

    /**
     * Select & return a specific object based on a table definition
     *
     * @param table            The treated table reference
     * @param where            The where clause (optional) (default='')
     * @param columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @param categoryFilter   The category list to use for filtering (optional) (default=null)
     * @return The resulting object
     */
    public static function selectObject($table, $where = '', $columnArray = null, $permissionFilter = null, $categoryFilter = null)
    {
        $key = $where . serialize($columnArray) . serialize($permissionFilter) . serialize($categoryFilter);
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        $tables = pnDBGetTables();
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
     * Select & return a specific object by using the ID field
     *
     * @param table            The treated table reference
     * @param id               The object ID to query
     * @param field            The field key which holds the ID value (optional) (default='id')
     * @param columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param permissionFilter The permission structure to use for permission checking (optional) (default=null)
     * @param categoryFilter   The category list to use for filtering (optional) (default=null)
     * @param cacheObject      If true returns a cached object if available (optional) (default=true)
     * @param transformFunc    Transformation function to apply to $id (optional) (default=null)
     * @return The resulting object
     * @deprecated
     * @see Doctrine_Table::find*
     */
    public static function selectObjectByID($table, $id, $field = 'id', $columnArray = null, $permissionFilter = null, $categoryFilter = null, $cacheObject = true, $transformFunc = null)
    {
        $tables = pnDBGetTables();
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
     * Select & return an object array based on a table definition
     *
     * @param table            The treated table reference
     * @param where          The where clause (optional) (default='')
     * @param orderby        The order by clause (optional) (default='')
     * @param limitOffset    The lower limit bound (optional) (default=-1)
     * @param limitNumRows   The upper limit bound (optional) (default=-1)
     * @param assocKey       The key field to use to build the associative index (optional) (default='')
     * @param permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @param categoryFilter   The category list to use for filtering (optional) (default=null)
     * @param columnArray    The columns to marshall into the resulting object (optional) (default=null)
     * @return The resulting object array
     */
    public static function selectObjectArray($table, $where = '', $orderby = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '', $permissionFilter = null, $categoryFilter = null, $columnArray = null)
    {
        $key = $where . $orderby . $limitOffset . $limitNumRows . $assocKey . serialize($permissionFilter) . serialize($categoryFilter) . serialize($columnArray);
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        self::_setFetchedObjectCount(0);

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $objects = array();
        $ca = self::getColumnsArray($table, $columnArray);
        $sql = self::_getSelectAllColumnsFrom($table, $where, $orderby, $columnArray);

        do {
            $fetchedObjectCount = self::_getFetchedObjectCount();
            $stmt = $sql;
            $limitOffset += $fetchedObjectCount;

            $res = self::executeSQL($stmt, $limitOffset, $limitNumRows);
            if ($res === false) {
                return $res;
            }

            $objArr = self::marshallObjects($res, $ca, true, $assocKey, true, $permissionFilter);
            $fc     = self::_getFetchedObjectCount();
            if ($objArr) {
                $objects = $objects + (array) $objArr; // append new array
            }
        } while ($permissionFilter && ($limitNumRows != -1 && $limitNumRows > 0) && $fetchedObjectCount > 0 && count($objects) < $limitNumRows);

        if ($limitNumRows != -1 && count($objects) > $limitNumRows) {
            $objects = array_slice($objects, 0, $limitNumRows);
        }

        if (!DBConnectionStack::isDefaultConnection()) {
            return $objects;
        }

        $tables = pnDBGetTables();
        $idFieldName = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';

        $objects = self::_selectPostProcess($objects, $table, $idFieldName);

        self::setCache($table, $key, $objects);

        return $objects;
    }

    /**
     * Select and return an object array based on a table definition
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
     * @param table          The treated table reference
     * @param where          The where clause (optional) (default='')
     * @param orderby        The order by clause (optional) (default='')
     * @param limitOffset    The lower limit bound (optional) (default=-1)
     * @param limitNumRows   The upper limit bound (optional) (default=-1)
     * @param assocKey       The key field to use to build the associative index (optional) (default='')
     * @param filterCallback The filter callback object.
     * @param columnArray    The columns to marshall into the resulting object (optional) (default=null)
     * @return The resulting object array
     */
    public static function selectObjectArrayFilter($table, $where = '', $orderby = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '', $filterCallback, $columnArray = null)
    {
        self::_setFetchedObjectCount(0);

        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $objects = array();
        $fetchedObjectCount = 0;
        $ca = self::getColumnsArray($table, $columnArray);
        $sql = self::_getSelectAllColumnsFrom($table, $where, $orderby, $columnArray);

        do {
            $stmt = $sql;
            $limitOffset += $fetchedObjectCount;

            $res = self::executeSQL($stmt, $limitOffset, $limitNumRows);
            if ($res === false) {
                return $res;
            }

            $objArr = self::marshallObjects($res, $ca, true, $assocKey, true, null);
            $fetchedObjectCount = self::_getFetchedObjectCount();

            for ($i = 0, $cou = count($objArr); $i < $cou; ++$i) {
                $obj = & $objArr[$i];
                if ($filterCallback->checkResult($obj)) {
                    $objects[] = $obj;
                }
            }
        } while ($limitNumRows != -1 && $limitNumRows > 0 && $fetchedObjectCount > 0 && count($objects) < $limitNumRows);

        if (!DBConnectionStack::isDefaultConnection()) {
            return $objects;
        }

        $tables = pnDBGetTables();
        $idFieldName = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';
        $objects = self::_selectPostProcess($objects, $table, $idFieldName);

        return $objects;
    }

    /**
     * Return the sum of a column
     *
     * @param table            The treated table reference
     * @param column           The column to place in the sum phrase
     * @param where            The where clause (optional) (default='')
     * @param categoryFilter   The category list to use for filtering (optional) (default=null)
     * @return The resulting column sum
     */
    public static function selectObjectSum($table, $column, $where = '', $categoryFilter = null)
    {
        $tables = pnDBGetTables();
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
     * Return the number of rows affected
     *
     * @param table            The treated table reference
     * @param where            The where clause (optional) (default='')
     * @param column           The column to place in the count phrase (optional) (default='*')
     * @param distinct         Whether or not to count distinct entries (optional) (default='false')
     * @param categoryFilter   The category list to use for filtering (optional) (default=null)
     * @return The resulting object count
     */
    public static function selectObjectCount($table, $where = '', $column = '1', $distinct = false, $categoryFilter = null)
    {
        $tables = pnDBGetTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];

        $dst = ($distinct && $column != '1' ? 'DISTINCT' : '');
        $col = ($column === '1' ? '1' : $columns[$column]);

        $where = self::generateCategoryFilterWhere($table, $where, $categoryFilter);
        $where = self::_checkWhereClause($where);

        $sql = "SELECT COUNT($dst $col) FROM $tableName AS tbl $where";

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
     * Select an object count by ID
     *
     * @param table         The treated table reference
     * @param id            The id value to match
     * @param field         The field to match the ID against (optional) (default='id')
     * @param transformFunc Transformation function to apply to $id (optional) (default=null)
     * @return The resulting object count
     */
    public static function selectObjectCountByID($table, $id, $field = 'id', $transformFunc = '')
    {
        if (!$id) {
            throw new Exception(__f('The parameter %s must not be empty', 'id'));
        }

        if ($field == 'id' && !is_numeric($id)) {
            throw new Exception(__f('The parameter %s must be numeric', 'id'));
        }

        $tables = pnDBGetTables();
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
     * Select & return an expanded field array
     *
     * @param table            The treated table reference
     * @param joinInfo         The array containing the extended join information
     * @param field            The name of the field we wish to marshall
     * @param where            The where clause (optional) (default='')
     * @param orderby          The orderby clause (optional) (default='')
     * @param distinct         whether or not to add a 'DISTINCT' clause (optional) (default=false)
     * @param assocKey         The key field to use to build the associative index (optional) (default='')
     * @param permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @return The resulting field array
     */
    public static function selectExpandedFieldArray($table, $joinInfo, $field, $where = '', $orderby = '', $distinct = false, $assocKey = '', $permissionFilter = null)
    {
        $key = $field . $where . $orderby . $distinct . $assocKey . serialize($joinInfo) . serialize($permissionFilter);
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        self::_setFetchedObjectCount(0);

        $tables = pnDBGetTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];
        $fieldName = $columns[$field];

        $sqlJoinArray = self::_processJoinArray($table, $joinInfo);
        $sqlJoin = $sqlJoinArray[0];
        $sqlJoinFieldList = $sqlJoinArray[1];

        $where = self::_checkWhereClause($where);
        $orderby = self::_checkOrderByClause($orderby, $table);

        $dSql = ($distinct ? "DISTINCT($fieldName)" : $fieldName);
        $sqlStart = "SELECT $dSql ";
        $sqlFrom = "FROM $tableName AS tbl ";

        $sql = "$sqlStart $sqlJoinFieldList $sqlFrom $sqlJoin $where $orderby";
        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }

        $fields =  self::marshallFieldArray($res, true, $assocKey);
        self::setCache($table, $key, $fields);

        return $fields;
    }

    /**
     * Select & return a object with it's left join fields filled in
     *
     * @param table             The treated table reference
     * @param joinInfo          The array containing the extended join information
     * @param where             The where clause (optional)
     * @param columnArray       The columns to marshall into the resulting object (optional) (default=null)
     * @param permissionFilter  The permission structure to use for permission checking (optional) (default=null)
     * @param categoryFilter    The category list to use for filtering (optional) (default=null)
     * @return The resulting object
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
     * Select & return an object by it's ID  with it's left join fields filled in
     *
     * @param table            The treated table reference
     * @param joinInfo         The array containing the extended join information
     * @param id               The ID value to use for object retrieval
     * @param field            The field key which holds the ID value (optional) (default='id')
     * @param columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param permissionFilter The permission structure to use for permission checking (optional) (default=null)
     * @param categoryFilter   The category list to use for filtering (optional) (default=null)
     * @param transformFunc    Transformation function to apply to $id (optional) (default=null)
     * @return The resulting object
     */
    public static function selectExpandedObjectByID($table, $joinInfo, $id, $field = 'id', $columnArray = null, $permissionFilter = null, $categoryFilter = null, $transformFunc = null)
    {
        $tables = pnDBGetTables();
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
     * Select & return an array of objects with it's left join fields filled in
     *
     * @param table             The treated table reference
     * @param joinInfo          The array containing the extended join information
     * @param where             The where clause (optional) (default='')
     * @param orderby           The order by clause (optional) (default='')
     * @param limitOffset       The lower limit bound (optional) (default=-1)
     * @param limitNumRows      The upper limit bound (optional) (default=-1)
     * @param assocKey          The key field to use to build the associative index (optional) (default='')
     * @param permissionFilter  The permission filter to use for permission checking (optional) (default=null)
     * @param columnArray       The columns to marshall into the resulting object (optional) (default=null)
     * @return The resulting object
     */
    public static function selectExpandedObjectArray($table, $joinInfo, $where = '', $orderby = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '', $permissionFilter = null, $categoryFilter = null, $columnArray = null)
    {
        $key = serialize($joinInfo) . $where . $orderby . $limitOffset . $limitNumRows . serialize($assocKey) . serialize($permissionFilter) . serialize($categoryFilter) . serialize($columnArray);
        $objects = self::getCache($table, $key);
        if ($objects !== false) {
            return $objects;
        }

        self::_setFetchedObjectCount(0);

        $tables = pnDBGetTables();
        $tableName = $tables[$table];
        $columns = $tables["{$table}_column"];

        $sqlStart = "SELECT " . self::_getAllColumnsQualified($table, 'tbl', $columnArray);
        $sqlFrom = "FROM $tableName AS tbl ";

        $sqlJoinArray = self::_processJoinArray($table, $joinInfo, $columnArray);
        $sqlJoin = $sqlJoinArray[0];
        $sqlJoinFieldList = $sqlJoinArray[1];
        $ca = $sqlJoinArray[2];

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
            $fc     = self::_getFetchedObjectCount();
            if ($objArr) {
                $objects = $objects + (array) $objArr; // append new array
            }
        } while ($permissionFilter && ($limitNumRows != -1 && $limitNumRows > 0) && $fetchedObjectCount > 0 && count($objects) < $limitNumRows);

        if (count($objects) > $limitNumRows && $limitNumRows > 0) {
            $objects = array_slice($objects, 0, $limitNumRows);
        }

        if (!DBConnectionStack::isDefaultConnection()) {
            return $objects;
        }

        $idFieldName = isset($tables["{$table}_primary_key_column"]) ? $tables["{$table}_primary_key_column"] : 'id';

        $objects = self::_selectPostProcess($objects, $table, $idFieldName);

        self::setCache($table, $key, $objects);

        return $objects;
    }

    /**
     * Return the number of rows affected
     *
     * @param table             The treated table reference
     * @param joinInfo          The array containing the extended join information
     * @param where             The where clause (optional) (default='')
     * @param distinct          whether or not to count distinct entries (optional) (default='false') /* turned off as fix for http://code.zikula.org/core/ticket/49, not supported in SQL)
     * @param categoryFilter    The category list to use for filtering (optional) (default=null)
     * @return The resulting object count
     */
    public static function selectExpandedObjectCount($table, $joinInfo, $where = '', $distinct = false, $categoryFilter = null)
    {
        self::_setFetchedObjectCount(0);

        $tables = pnDBGetTables();
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

        $sql = "$sqlStart $sqlJoinFieldList $sqlFrom $sqlJoin $where GROUP BY NULL";
        $res = self::executeSQL($sql);
        if ($res === false) {
            return $res;
        }

        $count = false;
        if ($data = $res->fetchColumn(0)) {
            $count = $data;
        }

        return $count;
    }

    /**
     * This method creates the necessary sql information for retrieving
     * fields from joined tables defined by a joinInfo array described
     * at the top of this class.
     *
     * @param  table          The treated table reference
     * @param  joinInfo       The array containing the extended join information
     * @param  columnArray    The columns to marshall into the resulting object (optional) (default=null)
     * @return array($sqlJoin, $sqlJoinFieldList, $ca)
     * @deprecated
     * @see    Doctrine_Record
     */
    private static function _processJoinArray($table, $joinInfo, $columnArray = null)
    {
        $tables = pnDBGetTables();
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
            $cft = $joinInfo[$k]['compare_field_table'];
            $cfj = $joinInfo[$k]['compare_field_join'];

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

            $compareColumn = $jcol[$cfj];
            // attempt to remove encoded table name in column list used by some tables
            $t = strstr($compareColumn, '.');
            if ($t !== false) {
                $compareColumn = substr($t, 1);
            }

            $t = isset($columns[$cft]) ? "tbl.$columns[$cft]" : $cft; // if not a column reference assume litereal column name
            $line = ' ' . $joinMethod . " $jtab $alias ON $alias.$compareColumn = $t ";

            $sqlJoin .= $line;
            ++$alias;
        }
        return array($sqlJoin, $sqlJoinFieldList, $ca);
    }

    /**
     * Post-processing for selected objects. This routine is responsible for reading the 'extra' data
     * (attributes, categories, and meta data) from the database and inserting the relevant sub-objects into the object.
     *
     * @param  objects        The object-array or the object we just selected
     * @param  table          The treated table reference
     * @param  idFieldName    The id column for the object/table combination
     * @return the object with it's relevant sub-objects set
     * @deprecated
     * @see CategorisableListener, AttributableListener, MetaDataListener
     */
    public static function _selectPostProcess($objects, $table, $idFieldName)
    {
        // nothing to do if objects is empty
        if (is_array($objects) && count($objects) == 0) {
            return $objects;
        }

        $tables = pnDBGetTables();
        $enableAllServices = (isset($tables["{$table}_db_extra_enable_all"]) && $tables["{$table}_db_extra_enable_all"]);

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_categorization"]) && $tables["{$table}_db_extra_enable_categorization"])) && pnConfigGetVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') && strcmp($table, 'categories_') !== 0 && strcmp($table, 'objectdata_attributes') !== 0 && strcmp($table, 'objectdata_log') !== 0 && pnModAvailable('Categories')) {
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
        if ($table == 'modules' || !pnModAvailable('ObjectData')) {
            return $objects;
        }

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_attribution"]) && $tables["{$table}_db_extra_enable_attribution"]) || pnConfigGetVar('Z_CONFIG_USE_OBJECT_ATTRIBUTION')) && strcmp($table, 'objectdata_attributes') !== 0 && strcmp($table, 'objectdata_log') !== 0) {
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

        if (($enableAllServices || (isset($tables["{$table}_db_extra_enable_meta"]) && $tables["{$table}_db_extra_enable_meta"]) || pnConfigGetVar('Z_CONFIG_USE_OBJECT_META')) && strcmp($table, 'objectdata_attributes') !== 0 && strcmp($table, 'objectdata_meta') !== 0 && strcmp($table, 'objectdata_log') !== 0) {
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
     * Select & return an object array based on a table definition using the given SQL statement
     *
     * @param sql              The sql statement to execute for the selection
     * @param table            The treated table reference
     * @param columnArray      The columns to marshall into the resulting object (optional) (default=null)
     * @param permissionFilter The permission filter to use for permission checking (optional) (default=null)
     * @param limitOffset      The lower limit bound (optional) (default=-1)
     * @param limitNumRows     The upper limit bound (optional) (default=-1)
     * @return The resulting object array
     */
    function selectObjectArraySQL($sql, $table, $columnArray = null, $permissionFilter = null, $limitOffSet = -1, $limitNumRows = -1)
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

        $ca = self::getColumnsArray($table, $columnArray);
        $objArr = self::marshallObjects($res, $ca, true, '', true, $permissionFilter);
        self::setCache($table, $key, $objArr);

        return $objArr;
    }

/**
     * Returns the last inserted ID
     *
     * @param mixed $table            The treated table reference
     * @param string $field
     * @param boolean $exitOnError
     * @param boolean $verbose
     * @return unknown
     */
    public static function getInsertID($table, $field = 'id', $exitOnError = true, $verbose = true)
    {
        $tables = pnDBGetTables();
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
            if (!$resultID = Doctrine_Manager::connection()->lastInsertId($tableName, $fieldName)) {
                if ($verbose) {
                    print '<br />' . $dbconn->ErrorMsg() . '<br />'; //TODO A this isnt right (drak)
                }

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
     * get the table definition for a database table. Convert the representation
     * from ADODB Datadict to Doctrine
     *
     * @param mixed $table table to get adodb sql string for
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
        $tables = pnDBGetTables();
        $tablecol = $table . '_column';
        $tabledef = $table . '_column_def';

        if (array_key_exists($tabledef, $tables) && is_array($tables[$tabledef])) {
            // we have a {$tablename}_column_def array as defined in pntables.php. This is a real array, not a string.
            // The format is like "C(24) NOTNULL DEFAULT ''" which means we have to prepend the field name now
            $typemap = array(
                            'B' => 'blob',
                            'C' => 'string',
                            'C2' => 'blob',
                            'D' => 'date',
                            'F' => 'float',
                            'I' => 'integer',
                            'I1' => 'integer',
                            'I2' => 'integer',
                            'I3' => 'integer',
                            'I4' => 'integer',
                            'I8' => 'integer',
                            'N' => 'number',
                            'L' => 'integer',
                            'T' => 'timestamp',
                            'TS' => 'timestamp',
                            'X' => 'clob',
                            'X2' => 'blob',
                            'XL' => 'clob');
            $iLengthMap = array(
                            'I1' => 3,
                            'I2' => 5,
                            'I3' => 8,
                            'I4' => 11,
                            'I8' => 20,
                            'L' => 1);
            $search = array(
                            '+',
                            '-',
                            '*',
                            '/',
                            '%');
            $replace = array(
                            '');
            foreach ($tables[$tablecol] as $id => $val) {
                $hasMath = (bool) (strcmp($val, str_replace($search, $replace, $val)));
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

                $clean = preg_replace('/\s\s+/', ' ', $tables[$tabledef][$id]);
                $fields = explode(' ', $clean);

                // determine field type
                $type = $fields[0];
                if (($pos = strpos($type, '(')) !== false) {
                    $type = substr($type, 0, $pos);
                }
                $fType = $typemap[$type];

                // determine field length
                if ($pos) {
                    $fLen = substr($fields[0], $pos + 1);
                    if (($pos = strpos($fLen, ')')) !== false) {
                        $fLen = substr($fLen, 0, $pos);
                    } else {
                        throw new Exception(__f('Missing closing bracket in field datadict specification for %s.%s', $table, $id));
                    }
                }
                unset($fields[0]);

                // transform to Doctrine datadict representation
                for ($i = 1; $i <= count($fields); $i++) {
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
                            throw new Exception(__f('Missing default value in field datadict specification for %s.%s', $table, $id));
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
                $fieldDef['length'] = (!$fLen && isset($iLengthMap[$type]) ? ($fUSign ? $iLengthMap[$type] : $iLengthMap[$type] - 1) : $fLen);
                $fieldDef['autoincrement'] = $fAuto;
                $fieldDef['primary'] = $fPrim;
                $fieldDef['unsigned'] = $fUSign;
                $fieldDef['notnull'] = ($fNull !== null ? ($fNull == 'NOTNULL' ? 1 : 0) : null);
                $fieldDef['default'] = $fDef;
                $ddict[$val] = $fieldDef;
            }
            //if ($table == 'zws_coupon') {
            //prayer ($ddict); exit();
            //}
            return $ddict;
        } else {
            throw new Exception(__f('Neither the sql parameter nor the table structure contain the ADODB dictionary representation of table [%s] ...', $table));
        }
    }

    /**
     * get the table definition for a database table
     *
     * @param mixed $table table to get adodb sql string for
     */
    public static function _getTableDefinition($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $flag = false;
        $sql = '';

        // try to read table definitions from $table array if present
        $tables = pnDBGetTables();
        $tablecol = $table . '_column';
        $tabledef = $table . '_column_def';
        if (array_key_exists($tabledef, $tables) && is_array($tables[$tabledef])) {
            // we have a {$tablename}_column_def array as defined in pntables.php. This is a real array, not a string.
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
     * get the constraints for a given table
     *
     * @param String $table treated table
     */
    public static function getTableConstraints($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        /*
        try {
            return Doctrine_Manager::connection()->import->listTableConstraints($tableName);
        }
        catch (Exception $e) {
            return LogUtil::registerError(__('Error! Table constraints determination failed.') . ' ' . $e->getMessage());
        }
        */
        $tablecol = $table . '_column';
        $tableopt = $table . '_constraints';
        $tables = pnDBGetTables();
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
     * get table prefix
     *
     * gets the database prefix for the current site
     *
     * In a non multisite scenario this will be the 'prefix' config var
     * from config/config.php. For a multisite configuration the multistes
     * module will manage the prefixes for a given table
     *
     * The table name parameter is the table name to get the prefix for
     * minus the prefix and seperating _
     * e.g. getTablePrefix returns z for tables z_modules with getTablePrefix('modules');
     *
     * @param table - table name
     */
    public static function getTablePrefix($table)
    {
        if (!isset($table)) {
            return false;
        }

        return pnConfigGetVar('prefix');
    }

    /**
     * verify that column and column_def definition match
     *
     * @param  table   The treated table reference
     * @return bool
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
            $hasMath = (bool) (strcmp($v, str_replace($search, $replace, $v)));
            if (!$hasMath) {
                if (!isset($columnDefs[$k])) {
                    throw new Exception(__f('Inconsistent table definition detected for table [%s]: column [%s] has no counterpart in column_def structure', array(
                                    $table,
                                    $k)));
                    //$success = LogUtil::registerError(__f('Inconsistent table definition detected for table [%s]: column [%s] has no counterpart in column_def structure', array($table, $k)));
                }
            }
        }
        foreach ($columnDefs as $k => $v) {
            if (!isset($columns[$k])) {
                throw new Exception(__f('Inconsistent table definition detected for table [%s]: column_def [%s] has no counterpart in column structure', array(
                                $table,
                                $k)));
                //$success = LogUtil::registerError(__f('Inconsistent table definition detected for table [%s]: column_def [%s] has no counterpart in column structure', array($table, $k)));
            }
        }

        return true;
    }

    /**
     * create a database table
     *
     * @param  string table a tablename key for the tables structure
     * @param  array Doctrine table definition array
     * @param  tabopt Table options specific to this table (optional)
     * @return bool
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

        $connection = Doctrine_Manager::connection();

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

        $tables = pnDBGetTables();
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
                self::createIndex($indexName, $table, $indexDefinition);
            }
        }
        return true;
    }

    /**
     * change database table using Doctrine dictionary method
     *
     * @param  string table a tablename key for the tables structure
     * @param  array Doctrine table definition array
     * @param  tabopt Table options specific to this table (optional) (default=null)
     * @return bool
     */
    public static function changeTable($table, $definition = null, $tabopt = null)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $success = self::verifyTableDefinitionConsistency($table);
        if (!$success) {
            throw new Exception(__f('Table consistency check failed for %s', $table));
            return false;
        }

        $connection = Doctrine_Manager::connection();

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

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        foreach ($definition as $key => $columnDefinition) {
            $alterTableDefinition = array('change' => array($key => array('definition' => $columnDefinition)));
            try {
                Doctrine_Manager::connection()->export->alterTable($tableName, $alterTableDefinition, false);
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
                    self::createIndex($indexName, $table, $indexDefinition);
                }
            }
        }

        self::flushCache($table);
        return true;
    }

    /**
     * truncate database table
     *
     * @param  String table a tablename key for the tables structure
     * @return bool
     */
    public static function truncateTable($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
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
     * rename a database table
     *
     * @param  String table a tablename key for the tables structure
     * @param  String newTable a tablename key for the tables structure
     * @return bool
     */
    public static function renameTable($table, $newTable)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }
        if (empty($newTable)) {
            throw new Exception(__f('The parameter %s must not be empty', 'newTable'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];
        $newTableName = $tables[$newTable];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        if (empty($newTableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $newTable));
        }

        try {
            Doctrine_Manager::connection()->export->alterTable($tableName, array(
                            'name' => $newTableName), true);
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Table rename failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);

        return true;
    }

    /**
     * delete a database table
     *
     * @param  String table a tablename key for the tables structure
     * @return bool
     */
    public static function dropTable($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        try {
            Doctrine_Manager::connection()->export->dropTable($tableName);
            ObjectUtil::deleteAllObjectTypeAttributes($table);
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Table drop failed.') . ' ' . $e->getMessage());
        }

        self::flushCache($table);

        return true;
    }

    /**
     * create index on table
     *
     * @param  idxname
     * @param  table     The treated table reference
     * @param  flds string field name, or non-associative array of field names
     * @param  idxoptarray
     * return  bool
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

        $tables = pnDBGetTables();
        $tableName = $tables[$table];
        $column = $tables["{$table}_column"];

        if (empty($column)) {
            throw new Exception(__f('%s does not point to a valid column definition', $column));
        }

        $idxDef = array();
        if (!is_array($flds)) {
            $idxDef[$column[$flds]] = array();
        } else {
            $newflds = array();
            foreach ($flds as $fld) {
                if (is_array($fld)) {
                    // this adds support to specifying index lengths in your pntables. So you can say
                    // $flds[] = array('path', 100);
                    // $flds[] = array('name', 10);
                    // $idxoptarray['UNIQUE'] = true;
                    // self::createIndex($idxname, $table, $flds, $idxoptarray);
                    $idxDef[$column[$fld]] = array();
                    if (isset($idxoptarray[$fld])) {
                        if (strtoupper($idxoptarray[$fld]) == 'UNIQUE') {
                            $idxDef[$column[$fld]]['unique'] = true;
                        }
                    }
                } else {
                    $idxDef[$column[$fld]] = array();
                }
            }
        }

        try {
            Doctrine_Manager::connection()->export->createIndex($tableName, $idxname, array(
                            'fields' => $idxDef));
            return true;
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Index creation failed.') . ' ' . $e->getMessage());
        }
    }

    /**
     * drop index on table
     *
     * @param  idxname index name
     * @param  table   The treated table reference
     * @return bool
     */
    public static function dropIndex($idxname, $table)
    {
        if (empty($idxname)) {
            throw new Exception(__f('The parameter %s must not be empty', 'idxname'));
        }

        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $tableName));
        }

        try {
            Doctrine_Manager::connection()->export->dropIndex($tableName, $idxname);
            return true;
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Index deletion failed.') . ' ' . $e->getMessage());
        }
    }

    /**
     * get a list of columns in a table
     *
     * @param  table The treated table reference
     * @param  notcasesensitive normalize case of table name
     * @return array of column objects
     */
    public static function metaColumns($table, $assoc = false, $notcasesensitive = true)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $tableName));
        }

        // TODO B [migrate adodb fetchmode constants to Doctrine equivalents] (Guite)
        /*
        if ($assoc) {
            global $ADODB_FETCH_MODE;
            $save = $ADODB_FETCH_MODE;
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        }
*/
        // TODO C [use use $assoc and $notcasesensitive params in DBUtil::metaColumns() for backwards compatability] (Guite)


        try {
            return Doctrine_Manager::connection()->import->listTableColumns($tableName);
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Fetching table column list failed.') . ' ' . $e->getMessage());
        }
        /*
        if ($assoc) {
            $ADODB_FETCH_MODE = $save;
        }
*/
    }

    /**
     * get a list of column names in a table
     *
     * @param  table The treated table reference
     * @param  numericIndex use numeric keys
     * @return array of column names
     */
    public static function metaColumnNames($table, $numericIndex = false)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        return Doctrine_Manager::connection()->MetaColumnNames($tableName, $numericIndex);
    }

    /**
     * get a list of primary keys for a table
     *
     * @param  table The treated table reference
     * @return array of primary keys
     */
    public static function metaPrimaryKeys($table)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        return Doctrine_Manager::connection()->MetaPrimaryKeys($tableName);
    }

    /**
     * get a list of foreign keys for a table
     *
     * @param  table The treated table reference
     * @param  owner
     * @param  upper upper case key names
     * @return array of foreign keys
     */
    public static function metaForeignKeys($table, $owner = false, $upper = false)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        return Doctrine_Manager::connection()->MetaForeignKeys($tableName, $owner, $upper);
    }

    /**
     * get a list of indexes for a table
     *
     * @param table The treated table reference
     * @param primary show only primary keys
     * @return array of column names
     */
    public static function metaIndexes($table, $primary = false)
    {
        if (empty($table)) {
            throw new Exception(__f('The parameter %s must not be empty', 'table'));
        }

        $tables = pnDBGetTables();
        $tableName = $tables[$table];

        if (empty($tableName)) {
            throw new Exception(__f('%s does not point to a valid table definition', $table));
        }

        try {
            return Doctrine_Manager::connection()->import->listTableIndexes($tableName);
        } catch (Exception $e) {
            return LogUtil::registerError(__('Error! Fetching table index list failed.') . ' ' . $e->getMessage());
        }
    }

    /**
     * limit the table name if necessary and prepend the prefix
     *
     * When using Oracle the object name may not be longer than 30 chars. Now ADODB uses TRIGGERS and SEQUENCEs to emulate the AUTOINCREMENT
     * which eats up to 9 chars (TRIG_SEQ_<prefix>_<tablename>) so we have to limit the length of the table name to
     * 30 - 9 - length(prefix) - separator.
     * We use this function as a central point to shorten table name (there might be restrictions in ' other RDBMS too). If the resulting tablename is
     * empty we will show an error. In this case the prefix is too long.
     *
     * @param  $table  The treated table reference
     * @param  $dbtype (optional) The driver used for this DB
     * @return bool
     * @deprecated
     * @see Doctrines DBAL layer
     */
    public static function getLimitedTablename($table, $dbType = '')
    {
        if (!$dbType) {
            $dbType = DBConnectionStack::getConnectionDBType();
        }

        $prefix = self::getTablePrefix($table);

        switch ($dbType) {
            case 'oci8': // Oracle
            case 'oci': // oracle
                $maxlen = 30; // max length for a tablename
                $_tablename = $table; // save for later if we need to show an error
                $lenTable = strlen($table);
                $lenPrefix = strlen($prefix);
                if ($lenTable + $lenPrefix + 10 > $maxlen) { // 10 for length of TRIG_SEQ_ + _
                    $table = substr($table, 0, $maxlen - 10 - $lenPrefix); // same as 20-strlen(), but easier to understand :-)
                }
                if (empty($table)) {
                    return pn_exit(__f('%1$s: unable to limit tablename [%2$s] because database prefix is too long for Oracle, please shorten it (recommended length is 4 chars)', array(
                                    __CLASS__ . '::' . __FUNCTION__,
                                    DataUtil::formatForDisplay($_tablename))));
                }
                break;

            default: // no action necessary, use tablename as is
                break;
        }

        // finally build the tablename
        $tablename = $prefix . '_' . $table;
        return $tablename;
    }

    /**
     * Build a Doctrine Model class dynamically to allow pntable based modules to use DQL
     *
     * @param string $table
     */
    public static function buildDoctrineModuleClass($table)
    {
        $def = self::getTableDefinition($table);
        $opt = self::getTableOptions($table);
        $hasColumns = '';
        foreach ($def as $columnName => $array) {
            $length = (is_null($array['length']) ? 'null' : $array['length']);
            $hasColumns .= "\$this->hasColumn('$columnName', '$array[type]', $length, " . var_export($array, true) . ");\n";
        }

        $options = '';
        foreach ($opt as $k => $v) {
            if ($k == 'type') {
                continue;
            }
            $options .= "\$this->option('$k', '$v');\n";
        }
        // generate the model class:
        $class = "
class {$table}_DBUtilRecord extends Doctrine_Record
{
    public function setTableDefinition()
    {
        \$this->setTableName('$table');
        $hasColumns
        $options
    }

    public function setUp()
    {
        self::setUp();
    }
}

class {$table}_DBUtilRecordTable extends Doctrine_Table {}
";
        return $class;
    }


    /**
     * Include dynamically created Doctrine Model class into runtime environment
     *
     * @param string $table
     */
    public static function loadDBUtilDoctrineModel($table)
    {
        // don't double load
        if (class_exists("{$table}_DBUtilRecord")) {
            return;
        }
        eval(self::buildDoctrineModuleClass($table));
    }
}

