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
 * DBObjectArray.
 *
 * @deprecated
 */
class DBObjectArray
{
    // state/type (static)
    /**
     * Object type.
     *
     * @var string
     */
    public $_objType = 'DBOBJECTARRAY';

    /**
     * Object join data.
     *
     * @var array
     */
    public $_objJoin;

    /**
     * Object validation data.
     *
     * @var array
     */
    public $_objValidation; // object validation data

    // data + access descriptor

    /**
     * Associative keyfield for select.
     *
     * @var string
     */
    public $_objAssocKey;

    /**
     * Category filter used for select.
     *
     * @var array
     */
    public $_objCategoryFilter;

    /**
     * Columns to select.
     *
     * @var array
     */
    public $_objColumnArray;

    /**
     * Object column prefix.
     *
     * @var string
     */
    public $_objColumnPrefix;

    /**
     * Object data.
     *
     * @var array
     */
    public $_objData;

    /**
     * Whether or not to use a distinct() clause.
     *
     * @var boolean
     */
    public $_objDistinct = false;

    /**
     * Object key retrieval field.
     *
     * @var string
     */
    public $_objField = 'id';

    /**
     * DBUtil insertObject preserve flag.
     *
     * @var boolean
     */
    public $_objInsertPreserve = false;

    /**
     * DBUtil insertObject force flag.
     *
     * @var boolean
     */
    public $_objInsertForce = false;

    /**
     * Object key value.
     *
     * @var integer
     */
    public $_objKey = 0;

    /**
     * Offset for select.
     *
     * @var integer
     */
    public $_objLimitOffset = -1;

    /**
     * Number of rows for select.
     *
     * @var integer
     */
    public $_objLimitNumRows = -1;

    /**
     * Object input path.
     *
     * @var string
     */
    public $_objPath = 'DBOBJECT_PATH';

    /**
     * Object permission filter applied.
     *
     * @var array
     */
    public $_objPermissionFilter;

    /**
     * Object session access path.
     *
     * @var string
     */
    public $_objSessionPath;

    /**
     * OrderBy clause for select.
     *
     * @var string
     */
    public $_objSort;

    /**
     * Where clause for select.
     *
     * @var string
     */
    public $_objWhere;

    // support

    /**
     * Table name.
     *
     * @var string
     */
    public $_table;

    /**
     * Column array.
     *
     * @var array
     */
    public $_columns;

    // constants
    const GET_FROM_DB                = 'DB'; // get data from DB
    const GET_FROM_GET               = 'GET'; // get data from $_GET
    const GET_FROM_POST              = 'POST'; // get data from $_POST
    const GET_FROM_REQUEST           = 'REQUEST'; // get data from $_REQUEST
    const GET_FROM_SESSION           = 'SESSION'; // get data from $_SESSION
    const GET_FROM_VALIDATION_FAILED = 'VALIDATION'; // get data from failed validation

    /**
     * Init everything to sane defaults and handle parameters.
     *
     * If $init is an arrary it is set(), otherwise it is interpreted as a string specifying
     * the source from where the data should be retrieved from.
     *
     * @param string|array $init         Initialization value (can be an object or a string directive) (optional) (default=null)
     * @param string       $where        The where clause to use when retrieving the object array (optional) (default='')
     * @param string       $orderBy      The order-by clause to use when retrieving the object array (optional) (default='')
     * @param integer      $limitOffset  The limiting offset
     * @param integer      $limitNumRows The limiting number of rows
     * @param string       $assocKey     Key field to use for building an associative array (optional) (default=null)
     */
    public function __construct($init = null, $where = null, $orderBy = null, $limitOffset = -1, $limitNumRows = -1, $assocKey = null)
    {
        @trigger_error('DBObjectArray is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $this->_init($init, $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }

    /**
     * Internal intialization routine.
     *
     * If $init is an arrary it is set(), otherwise it is interpreted as a string specifying
     * the source from where the data should be retrieved from.
     *
     * @param string|array $init         Initialization value (can be an object or a string directive) (optional) (default=null)
     * @param string       $where        The where clause to use when retrieving the object array (optional) (default='')
     * @param string       $orderBy      The order-by clause to use when retrieving the object array (optional) (default='')
     * @param integer      $limitOffset  The limiting offset
     * @param integer      $limitNumRows The limiting number of rows
     * @param string       $assocKey     Key field to use for building an associative array (optional) (default=null)
     *
     * @return void
     */
    public function _init($init = null, $where = null, $orderBy = null, $limitOffset = -1, $limitNumRows = -1, $assocKey = null)
    {
        if ('DBOBJECTARRAY' != $this->_objType) {
            $dbtables = DBUtil::getTables();
            $tkey = $this->_objType;
            $ckey = $tkey . "_column";
            $this->_table = isset($dbtables[$tkey]) ? $dbtables[$tkey] : '';
            $this->_columns = isset($dbtables[$ckey]) ? $dbtables[$ckey] : '';
        }

        if (!$init) {
            return;
        }

        if (is_array($init)) {
            $this->setData($init);
        } elseif (is_string($init)) {
            switch ($init) {
                case DBObject::GET_FROM_DB:
                    $this->get($where, $orderBy, $limitOffset, $limitNumRows, $assocKey, true);
                    break;

                case DBObject::GET_FROM_GET:
                case DBObject::GET_FROM_POST:
                case DBObject::GET_FROM_REQUEST:
                    $this->setData($this->getDataFromInput($this->_objPath, null, $init));
                    break;

                case DBObject::GET_FROM_SESSION:
                    $this->getDataFromSource($_SESSION, $this->_objPath);
                    break;

                case DBObject::GET_FROM_VALIDATION_FAILED:
                    $this->getDataFromSource($_SESSION['validationFailedObjects'], $this->_objPath);
                    break;

                default:
                    throw new \Exception(__f("Error! An invalid initialization directive '%s' found in 'DBObjectArray::init()'.", $init));
            }
        } else {
            throw new \Exception(__f("Error! An unexpected parameter type initialization '%s' was encountered in 'PNObject::init()'.", $init));
        }
    }

    /**
     * Set (and return) the object data. Since we dont' have a definitive key, we don't cache.
     *
     * @param array $data The data to assign
     *
     * @return array The object data
     */
    public function setData($data)
    {
        if (!is_array($data)) {
            return false;
        }

        $this->_objData = $data;

        return $this->_objData;
    }

    /**
     * Generate an empty object with the fields initialized to null.
     *
     * @param integer $num Number of empty objects
     *
     * @return array The generated object
     */
    public function generateEmptyObjectArray($num = 1)
    {
        $item = ObjectUtil::createEmptyObject($this->_objType);
        if ($item) {
            $data = [];
            for ($i = 0; $i < $num; $i++) {
                $data[] = $item;
            }
            $this->_objData = $data;
        }

        return $this->_objData;
    }

    /**
     * Return the record count for the given object set.
     *
     * @param string  $where  The where-clause to use
     * @param boolean $doJoin Whether or not to use the auto-join for the count
     *
     * @return array The object's data set count
     */
    public function getCount($where = '', $doJoin = null)
    {
        if (null === $doJoin) {
            $doJoin = (bool)($this->_objJoin);
        } else {
            $doJoin = (bool)$doJoin;
        }

        if ($this->_objJoin && $doJoin) {
            $this->_objData = DBUtil::selectExpandedObjectCount($this->_objType, $this->_objJoin, $where, false, $this->_objCategoryFilter);
        } else {
            $this->_objData = DBUtil::selectObjectCount($this->_objType, $where, '1', false, $this->_objCategoryFilter);
        }

        return $this->_objData;
    }

    /**
     * Filter generator pre processor.
     *
     * Ensure that a filter has all used fields set in order to to ensure that there are no E_ALL
     * issues when accessing filter fields which may not be set + do additional processing as necessary.
     * Default implementation which can be overridden by subclasses.
     *
     * @param array $filter An array containing the set filter values (optional) (default=[])
     *
     * @return array The processed filter array
     */
    public function genFilterPreProcess($filter = [])
    {
        return $filter;
    }

    /**
     * Return/Select the object using the given where clause.
     *
     * Generate a filter for the array view. Default implementation which can be overridden by subclasses.
     *
     * @param array $filter An array containing the set filter values (optional) (default=[])
     *
     * @return string The generated filter (where-clause) string
     */
    public function genFilter($filter = [])
    {
        $filter = $this->genFilterPreProcess($filter);

        return '';
    }

    /**
     * Return/Select the object using the given where clause.
     *
     * @param string  $where        The where-clause to use
     * @param string  $orderBy      The order-by clause to use
     * @param integer $limitOffset  The limiting offset
     * @param integer $limitNumRows The limiting number of rows
     * @param string  $assocKey     Key field to use for building an associative array (optional) (default=null)
     * @param boolean $force        Whether or not to force a DB-get (optional) (default=false)
     * @param boolean $distinct     Whether or not to do a select distinct (optional) (default=false)
     *
     * @return array The object's data value
     */
    public function getWhere($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = null, $force = false, $distinct = false)
    {
        if ((($where != $this->_objWhere || $orderBy != $this->_objSort || $limitOffset != $this->_objLimitOffset || $limitNumRows != $this->_objLimitNumRows || $assocKey != $this->_objAssocKey || $distinct != $this->_objDistinct) || !$this->_objData) || $force) {
            $this->select($where, $orderBy, $limitOffset, $limitNumRows, $assocKey, $distinct);
        }

        return $this->_objData;
    }

    /**
     * Return the current object data. Maps to $this->getWhere().
     *
     * @param string  $where        The where-clause to use
     * @param string  $orderBy      The order-by clause to use
     * @param integer $limitOffset  The limiting offset
     * @param integer $limitNumRows The limiting number of rows
     * @param string  $assocKey     Key field to use for building an associative array (optional) (default=null)
     * @param boolean $force        Whether or not to force a DB-get (optional) (default=false)
     * @param boolean $distinct     Whether or not to do a select distinct (optional) (default=false)
     *
     * @return array The object's data value
     */
    public function get($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = null, $force = false, $distinct = false)
    {
        return $this->getWhere($where, $orderBy, $limitOffset, $limitNumRows, $assocKey, $force, $distinct);
    }

    /**
     * Return the currently set object data.
     *
     * @return array The object's data array
     */
    public function getData()
    {
        return $this->_objData;
    }

    /**
     * Generic select handler for an object. Select (and set) the specified object array.
     *
     * @param string  $where        The where-clause to use
     * @param string  $orderBy      The order-by clause to use
     * @param integer $limitOffset  The limiting offset
     * @param integer $limitNumRows The limiting number of rows
     * @param string  $assocKey     Key field to use for building an associative array (optional) (default=null)
     * @param boolean $distinct     Whether or not to use the distinct clause
     *
     * @return array The selected Object-Array
     */
    public function select($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = false, $distinct = false)
    {
        if ($this->_objJoin) {
            $objArr = DBUtil::selectExpandedObjectArray($this->_objType, $this->_objJoin, $where, $orderBy, $limitOffset, $limitNumRows, $assocKey, $this->_objPermissionFilter, $this->_objCategoryFilter, $this->_objColumnArray);
        } else {
            $objArr = DBUtil::selectObjectArray($this->_objType, $where, $orderBy, $limitOffset, $limitNumRows, $assocKey, $this->_objPermissionFilter, $this->_objCategoryFilter, $this->_objColumnArray);
        }

        $this->_objData = $objArr;
        $this->_objWhere = $where;
        $this->_objSort = $orderBy;
        $this->_objLimitOffset = $limitOffset;
        $this->_objLimitNumRows = $limitNumRows;
        $this->_objAssocKey = $assocKey;
        $this->_objDistinct = $distinct;

        $this->selectPostProcess();

        return $this->_objData;
    }

    /**
     * Iterate over the object data and post-process it.
     *
     * @param array $data The data object
     *
     * @return array The Object Data
     */
    public function selectPostProcess($data = null)
    {
        return $this->_objData;
    }

    /**
     * Get the data from the various input streams provided.
     *
     * @param string  $key     The access key of the object (optional) (default=null, reverts to $this->_objPath)
     * @param mixed   $default The default value to return (optional) (default=null)
     * @param string  $source  Where to get the variable from (optional) (default='REQUEST')
     * @param integer $filter  Filtering directives, use FILTER_* constants from filter_*()
     * @param array   $args    The filter processing args to apply
     *
     * @return array The requested object/value
     */
    public function getDataFromInput($key = null, $default = null, $source = 'REQUEST', $filter = null, array $args = [])
    {
        if (!$key) {
            $key = $this->_objPath;
        }

        $objectArray = FormUtil::getPassedValue($key, $default, $source, $filter, $args, $this->_objPath);

        if ($objectArray) {
            $this->_objData = $objectArray;
            $this->getDataFromInputPostProcess();

            return $this->_objData;
        }

        return $default;
    }

    /**
     * Get the data from the session.
     *
     * @param string  $key                  The access key of the object (optional) (default=null, reverts to $this->_objPath)
     * @param mixed   $default              The default value to return (optional) (default=null)
     * @param string  $path                 The session object input path
     * @param boolean $autocreate           The autocreate passed to SessionUtil::setVar
     * @param boolean $overwriteExistingVar The overwriteExistingVar variable passed to SessionUtil::setVar
     *
     * @return array The requested object/value
     */
    public function getDataFromSession($key = null, $default = null, $path = '', $autocreate = true, $overwriteExistingVar = false)
    {
        if (!$key) {
            $key = $this->_objPath;
        }
        if (!$path) {
            $path = $this->_objSessionPath;
        }

        $objectArray = SessionUtil::getVar($key, $default, $path, $autocreate, $overwriteExistingVar);
        if ($objectArray && is_array($objectArray)) {
            $this->_objData = $objectArray;
            $this->getDataFromSessionPostProcess();

            return $this->_objData;
        }

        return $default;
    }

    /**
     * Set the current object data into session.
     *
     * @param array   $data                 The object data
     * @param string  $key                  The session key
     * @param string  $path                 The session object input path
     * @param boolean $autocreate           The autocreate passed to SessionUtil::setVar
     * @param boolean $overwriteExistingVar The overwriteExistingVar variable passed to SessionUtil::setVar
     *
     * @return array The session data
     */
    public function setDataToSession($data = null, $key = null, $path = '', $autocreate = true, $overwriteExistingVar = false)
    {
        if (!$data) {
            $data = $this->_objData;
        }
        if (!$key) {
            $key = $this->_objPath;
        }
        if (!$path) {
            $path = $this->_objSessionPath;
        }

        if (!$this->setDataToSessionPreProcess($data)) {
            return false;
        }

        SessionUtil::setVar($path, $data, $path, $autocreate, $overwriteExistingVar);
        $this->_objData = $data;

        return $this->_objData;
    }

    /**
     * Post-Process the data after getting it from Input.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function getDataFromInputPostProcess($data = null)
    {
        return $this->_objData;
    }

    /**
     * Post-Process the data after getting it from Session.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function getDataFromSessionPostProcess($data = null)
    {
        return $this->_objData;
    }

    /**
     * Pre-Process the data before writing it to Session.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function setDataToSessionPreProcess($data = null)
    {
        return $this->_objData;
    }

    /**
     * Generic access function to retrieve data from the specified source.
     *
     * @param array   $source  The source data
     * @param string  $key     The access key of the object (optional) (default=null)
     * @param boolean $default The default value to return (optional) (default=null)
     * @param boolean $clean   Whether or not to clean the acquired data (optional) (default=true)
     *
     * @return array The requested object/value
     */
    public function getDataFromSource($source, $key = null, $default = null, $clean = true)
    {
        if (!$key) {
            $key = $this->_objPath;
        }

        if (isset($source[$key])) {
            return $this->setData($source[$key]);
        }

        return $this->setData($default);
    }

    /**
     * Generic function to retrieve.
     *
     * @param integer $offset  Data row offset
     * @param string  $key     Field key
     * @param mixed   $default Default return value
     *
     * @return mixed The Object Data
     */
    public function getDataField($offset, $key, $default = null)
    {
        $obj = $this->_objData[$offset];
        if (isset($obj[$key])) {
            return $obj[$key];
        }

        return $default;
    }

    /**
     * Save an object - if it has an ID update it, otherwise insert it.
     *
     * @return array|boolean The result set
     */
    public function save()
    {
        $rc = true;
        $ak = array_keys($this->_objData);
        if (isset($this->_objData[$ak[0]][$this->_objField]) && $this->_objData[$ak[0]][$this->_objField]) {
            $rc = $this->update();
        } else {
            $rc = $this->insert();
        }

        return $rc;
    }

    /**
     * Generic insert handler for an object (ID is inserted into the object data).
     *
     * @return array|boolean The Object Data
     */
    public function insert()
    {
        if (!$this->insertPreProcess()) {
            return false;
        }

        $res = true;
        foreach ($this->_objData as $k => $v) {
            $res = $res && DBUtil::insertObject($this->_objData[$k], $this->_objType, $this->_objField, $this->_objInsertPreserve, $this->_objInsertForce);
        }

        if ($res) {
            $this->insertPostProcess();

            return $this->_objData;
        }

        return false;
    }

    /**
     * Pre-Process the data prior to an insert.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function insertPreProcess($data = null)
    {
        EventUtil::dispatch('dbobjectarray.insertpreprocess', new \Zikula\Core\Event\GenericEvent($this));

        return $this->_objData;
    }

    /**
     * Post-Process the data after an insert.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function insertPostProcess($data = null)
    {
        EventUtil::dispatch('dbobjectarray.insertpostprocess', new \Zikula\Core\Event\GenericEvent($this));

        return $this->_objData;
    }

    /**
     * Generic upate handler for an object.
     *
     * @return array|boolean The Object Data
     */
    public function update()
    {
        if (!$this->updatePreProcess()) {
            return false;
        }

        $res = true;
        foreach ($this->_objData as $k => $v) {
            $res = $res && DBUtil::updateObject($this->_objData[$k], $this->_objType, '', $this->_objField, $this->_objInsertPreserve);
        }

        if ($res) {
            $this->updatePostProcess();

            return $this->_objData;
        }

        return false;
    }

    /**
     * Pre-Process the data prior to an update.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function updatePreProcess($data = null)
    {
        EventUtil::dispatch('dbobjectarray.updatepreprocess', new \Zikula\Core\Event\GenericEvent($this));

        return $this->_objData;
    }

    /**
     * Post-Process the data after an update.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function updatePostProcess($data = null)
    {
        EventUtil::dispatch('dbobjectarray.updatepostprocess', new \Zikula\Core\Event\GenericEvent($this));

        return $this->_objData;
    }

    /**
     * Generic delete handler for an object.
     *
     * @return array|boolean The Object Data
     */
    public function delete()
    {
        if (!$this->deletePreProcess()) {
            return false;
        }

        $res = true;
        foreach ($this->_objData as $k => $v) {
            $res = $res && DBUtil::deleteObjectById($this->_objType, $v[$this->_objField], $this->_objField);
        }

        if ($res) {
            $this->deletePostProcess();

            return $this->_objData;
        }

        return false;
    }

    /**
     * Pre-Process the data prior a delete.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function deletePreProcess($data = null)
    {
        EventUtil::dispatch('dbobjectarray.deletepreprocess', new \Zikula\Core\Event\GenericEvent($this));

        return $this->_objData;
    }

    /**
     * Post-Process the data after a delete.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param array $data Object data
     *
     * @return array Object data
     */
    public function deletePostProcess($data = null)
    {
        EventUtil::dispatch('dbobjectarray.deletepostprocess', new \Zikula\Core\Event\GenericEvent($this));

        return $this->_objData;
    }

    /**
     * Delete with a where-clause.
     *
     * @param string $where The where-clause to use
     *
     * @return array|boolean The Object Data
     */
    public function deleteWhere($where = null)
    {
        if (!$where) {
            return false;
        }

        if (!$this->deletePreProcess()) {
            return false;
        }

        $res = DBUtil::deleteWhere($this->_objType, $where);
        $this->deletePostProcess();

        return $this->_objData;
    }

    /**
     * Set a database field on all objects
     *
     * @param string $column The column to update
     * @param mixed $value The value to update the column to
     *
     * @return resultSet The resultSet from the update SQL statement
     */
    public function setDBFieldAll($column, $value)
    {
        EventUtil::dispatch('dbobjectarray.setdbfieldall', new \Zikula\Core\Event\GenericEvent($this));

        return $this->setDBField($column, $value, null, true);
    }

    /**
     * Set a database field on a filtered set of objects
     *
     * @param string $column The column to update
     * @param mixed $value The value to update the column to
     * @param array $filter The filter to apply to the selection
     * @param boolean $fromAll Indicator whether the method was called from the 'fromAll' variant -> for filter error checking
     *
     * @return resultSet The resultSet from the update SQL statement
     */
    public function setDBField($column, $value, $filter = null, $fromAll = false)
    {
        EventUtil::dispatch('dbobjectarray.setdbfield', new \Zikula\Core\Event\GenericEvent($this));

        if (null === $column) {
            throw new \Exception(__f("Invalid [column] received"));
        }

        if (null === $value) {
            throw new \Exception(__f("Invalid [value] received"));
        }

        if (!$filter && !$fromAll) {
            throw new \Exception(__f("Invalid [filterCol] received"));
        }

        if (!is_array($filter) && !$fromAll) {
            throw new \Exception(__f("Non-array [filterCol] received"));
        }

        $dbtables = DBUtil::getTables();
        $tkey = $this->_objType;
        $ckey = $tkey . "_column";
        $tab = isset($dbtables[$this->_objType]) ? $dbtables[$this->_objType] : null;
        $col = isset($dbtables[$ckey][$column]) ? $dbtables[$ckey][$column] : null;
        if (!$col) {
            throw new \Exception(__f("Non-existing field [%s] received", $column));
        }

        $where  = '';
        if ($filter) {
            $filter = $this->cleanFilter($filter);
            $where  = DBUtil::_checkWhereClause($this->genFilter($filter));
            if (!$where) {
                throw new \Exception(__f("Supplied filter did not result in a where-clause"));
            }
        }
        $val = DBUtil::_typesafeQuotedID($this->_objType, $column, $value);
        $sql = "UPDATE $tab SET $col = $val $where";
        $res = DBUtil::executeSQL($sql);

        return $res;
    }

    /**
     * Clean the acquired input.
     *
     * @param array $objArray The object-array to clean (optional) (default=null, reverts to $this->_objData)
     *
     * @return array The Object Data
     */
    public function clean($objArray = null)
    {
        if (!$objArray) {
            $objArray = &$this->_objData;
        }

        $ak = array_keys($objArray);
        foreach ($ak as $k) {
            $obj = &$objArray[$k];
            $ak2 = array_keys($obj);
            foreach ($ak2 as $f) {
                $obj[$f] = FormUtil::getPassedValue(trim($obj[$f]));
            }
        }

        return $objArray;
    }

    /**
     * Get a selector for the object array.
     *
     * @param string  $name         The name of the selector to generate
     * @param string  $selected     The currently selected value (optional) (default=-1234)
     * @param string  $defaultValue The default value (optional) (default=0)
     * @param string  $defaultText  The default text (optional) (default='')
     * @param string  $allValue     The all-selected value (optional) (default=0)
     * @param string  $allText      The all-selected text (optional) (default='')
     * @param string  $idField      The id field to use (optional) (default=null)
     * @param string  $nameField    The name field to use (optional) (default='title')
     * @param boolean $submit       Whether or not to submit the form upon selection (optional) (default=false)
     * @param boolean $disabled     Whether or not the select field is disabled
     * @param integer $multipleSize The size of the select field for multiple select
     *
     * @return string The generated selector html text
     */
    public function getSelector($name, $selected = -1234, $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $idField = '', $nameField = 'title', $submit = false, $disabled = false, $multipleSize = 1)
    {
        if (!$idField) {
            $idField = $this->_objField;
        }

        return HtmlUtil::getSelector_Generic($name, $this->_objData, $selected, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Get the object validation data
     *
     * @return array Object validation data
     */
    public function getValidation()
    {
        return $this->_objValidation;
    }

    /**
     * Pre-Process the basic object validation with class specific logic.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param string $type Controller type
     * @param array  $data Data to be used for validation
     *
     * @return boolean
     */
    public function validatePreProcess($type = 'user', $data = null)
    {
        EventUtil::dispatch('dbobjectarray.validatepreprocess', new \Zikula\Core\Event\GenericEvent($this));

        return true;
    }

    /**
     * Post-Process the basic object validation with class specific logic.
     *
     * Subclasses can define appropriate implementations.
     *
     * @param string $type Controller type
     * @param array  $data Data to be used for validation
     *
     * @return boolean
     */
    public function validatePostProcess($type = 'user', $data = null)
    {
        // empty function, should be implemented by child classes.
        EventUtil::dispatch('dbobjectarray.validatepostprocess', new \Zikula\Core\Event\GenericEvent($this));

        return true;
    }

    /**
     * Constructur, init everything to sane defaults and handle parameters.
     *
     * @return boolean Indicating whether or not validation passed successfully
     */
    public function validate()
    {
        if (!$this->_objValidation) {
            return true;
        }

        if (!$this->_objData) {
            return false;
        }

        $res = $this->validatePreProcess();
        if ($res) {
            foreach ($this->_objData as $k => $v) {
                $res = $res && ValidationUtil::validateObjectPlain($this->_objPath, $v, $this->_objValidation);
                if (!$res) {
                    break;
                }
            }
            if ($res) {
                $res = $res && $this->validatePostProcess();
            }
        }

        return $res;
    }

    /**
     * Get the hashcode for this object data array.
     *
     * @param boolean $includeStandardFields Wheter or not to include standard fields to hashcode
     * @param array   $objArray              Object data
     *
     * @return string Hashcode
     */
    public function getHash($includeStandardFields = true, $objArray = null)
    {
        if (!$objArray) {
            $objArray = $this->_objData;
        }

        $arrayHash = [];
        foreach ($objArray as $obj) {
            if (!$includeStandardFields) {
                ObjectUtil::removeStandardFieldsFromObject($obj);
            }
            $arrayHash[] = DataUtil::hash(serialize($obj));
        }

        return DataUtil::hash(serialize($arrayHash));
    }

    /**
     * Clear the failed validation errors for this object.
     *
     * @return void
     */
    public function clearValidationErrors()
    {
        FormUtil::clearValidationErrors($this->_objPath);
    }

    /**
     * Clear the failed validation object data for this object.
     *
     * @return void
     */
    public function clearFailedValidationData()
    {
        FormUtil::clearValidationFailedObjects($this->_objPath);
    }

    /**
     * Print HTML-formatted debug output for the object.
     *
     * @param boolean $print Whether to print or to return the debug output
     *
     * @return string|void Debug output
     */
    public function prayer($print = true)
    {
        if ($print) {
            return prayer($this->_objData);
        }

        return _prayer($this);
    }

    /**
     * Print HTML-formatted debug output for the object data.
     *
     * @param boolean $print  Whether to print or to return the object data debug output
     * @param integer $offset Object row offset
     *
     * @return string|void Debug output
     */
    public function prayerData($print = true, $offset = null)
    {
        if ($print) {
            if (null !== $offset) {
                return prayer($this->_objData[$offset]);
            }
            prayer($this->_objData);
        }

        if (null !== $offset) {
            return _prayer($this->_objData[$offset]);
        }

        return _prayer($this->_objData);
    }
}
