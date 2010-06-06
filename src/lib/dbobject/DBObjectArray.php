<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * DBObjectArray
 *
 * @package Zikula_Core
 * @subpackage DBObjectArray
 */
class DBObjectArray
{
    // state/type (static)
    public $_objType; // object type
    public $_objJoin; // object join data
    public $_objValidation; // object validation data


    // data + access descriptor
    public $_objAssocKey; // associative-keyfield for select
    public $_objCategoryFilter; // category filter used for select
    public $_objColumnArray; // columns to select
    public $_objColumnPrefix; // object column prefix
    public $_objData; // object data
    public $_objDistinct; // whether or not to use a distinct() clause
    public $_objField; // object key retrieval field
    public $_objInsertPreserve; // DBUtil insertObject preserve flag
    public $_objInsertForce; // DBUtil insertObject force flag
    public $_objKey; // object key value
    public $_objLimitOffset; // offset for select
    public $_objLimitNumRows; // number of rows for select
    public $_objPath; // object input path
    public $_objPermissionFilter; // object permission filter applied
    public $_objSessionPath; // object session access path
    public $_objSort; // orderBy clause for select
    public $_objWhere; // where clause for select


    // support
    public $_table; // table name
    public $_columns; // column array


    // constants
    const GET_FROM_DB = 'DB'; // get data from DB
    const GET_FROM_GET = 'GET'; // get data from $_GET
    const GET_FROM_POST = 'POST'; // get data from $_POST
    const GET_FROM_REQUEST = 'REQUEST'; // get data from $_REQUEST
    const GET_FROM_SESSION = 'SESSION'; // get data from $_SESSION
    const GET_FROM_VALIDATION_FAILED = 'VALIDATION'; // get data from failed validation

    public function __construct($init = null, $where = null, $orderBy = null, $limitOffset = -1, $limitNumRows = -1, $assocKey = null)
    {
        $this->DBObjectArray($init, $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }

    /**
     * Old constructor, init everything to sane defaults and handle parameters
     *
     * @param init        Initialization value (see _init() for details)
     * @param where       The where clause to apply to the DB get/select (optional) (default='')
     */
    public function DBObjectArray($init = null, $where = null, $orderBy = null, $limitOffset = -1, $limitNumRows = -1, $assocKey = null)
    {
        $this->_objType = 'DBOBJECTARRAY';
        $this->_objJoin = null;
        $this->_objValidation = null;

        $this->_objAssocKey = null;
        $this->_objCategoryFilter = null;
        $this->_objColumnArray = null;
        $this->_objColumnPrefix = null;
        $this->_objData = null;
        $this->_objDistinct = false;
        $this->_objField = 'id';
        $this->_objInsertPreserve = false;
        $this->_objInsertForce = false;
        $this->_objKey = 0;
        $this->_objLimitOffset = -1;
        $this->_objLimitNumRows = -1;
        $this->_objPath = 'DBOBJECT_PATH';
        $this->_objPermissionFilter = null;
        $this->_objSessionPath = null;
        $this->_objSort = null;
        $this->_objWhere = null;

        $this->_init($init, $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }

    /**
     * Internal intialization routine
     *
     * @param init          Initialization value (can be an object or a string directive) (optional) (default=null)
     * @param where         The where clause to use when retrieving the object array (optional) (default='')
     * @param orderBy       The order-by clause to use when retrieving the object array (optional) (default='')
     * @param limitOffset   The limiting offset
     * @param limitNumRows  The limiting number of rows
     * @param assocKey      Key field to use for building an associative array (optional) (default=null)
     *
     * If $init is an arrary it is set(), otherwise it is interpreted as a string specifying
     * the source from where the data should be retrieved from.
     */
    public function _init($init = null, $where = null, $orderBy = null, $limitOffset = -1, $limitNumRows = -1, $assocKey = null)
    {
        if ($this->_objType != 'DBOBJECTARRAY') {
            $pntables = System::dbGetTables();
            $tkey = $this->_objType;
            $ckey = $tkey . "_column";
            $this->_table = isset($pntables[$tkey]) ? $pntables[$tkey] : '';
            $this->_columns = isset($pntables[$ckey]) ? $pntables[$ckey] : '';
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
                    return z_exit(__f("Error! An invalid initialization directive '%s' found in 'DBObjectArray::init()'.", $init));
            }
        } else
            return z_exit(__f("Error! An unexpected parameter type initialization '%s' was encountered in 'PNObject::init()'.", $init));
    }

    /**
     * Set (and return) the object data. Since we dont' have a definitive key, we don't cache
     *
     * @param data      The data to assign
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
     * Generate an empty object with the fields initialized to null
     */
    public function generateEmptyObjectArray($num = 1)
    {
        $pntables = System::dbGetTables();
        $tkey = $this->_objType;
        $ckey = $this->_objType . "_column";
        $this->_table = isset($pntables[$tkey]) ? $pntables[$tkey] : '';
        $this->_columns = isset($pntables[$ckey]) ? $pntables[$ckey] : '';

        $item = array();
        foreach ($this->_columns as $k => $v) {
            $item[$k] = null;
        }

        $data = array();
        for ($i = 0; $i < $num; $i++) {
            $data[] = $item;
        }

        $this->setData($data);
        return $data;
    }

    /**
     * Return the record count for the given object set
     *
     * @param where     The where-clause to use
     * @param doJoin    whether or not to use the auto-join for the count
     *
     * @return The object's data set count
     */
    public function getCount($where = '', $doJoin = false)
    {
        if ($this->_objJoin && $doJoin) {
            $this->_objData = DBUtil::selectExpandedObjectCount($this->_objType, $this->_objJoin, $where, false, $this->_objCategoryFilter);
        } else {
            $this->_objData = DBUtil::selectObjectCount($this->_objType, $where, '1', false, $this->_objCategoryFilter);
        }
        return $this->_objData;
    }

    /**
     * Ensure that a filter has all used fields set in order to to ensure that there are no E_ALL
     * issues when accessing filter fields which may not be set + do additional processing as necessary.
     * Default implementation which can be overridden by subclasses.
     *
     * @param filter    An array containing the set filter values (optional) (default=array())
     *
     * @return The processed filter array
     */
    public function genFilterPreProcess ($filter = array())
    {
        return $filter;
    }

    /**
     * Return/Select the object using the given where clause.
     **
     * Generate a filter for the array view. Default implementation which can be overridden by subclasses.
     *
     * @param filter    An array containing the set filter values (optional) (default=array())
     *
     * @return The generated filter (where-clause) string
     */
    public function genFilter($filter = array())
    {
        $filter = $this->genFilterPreProcess ($filter);
        return '';
    }

    /**
     * Return/Select the object using the given where clause.
     *
     * @param where         The where-clause to use
     * @param orderBy          The order-by clause to use
     * @param limitOffset   The limiting offset
     * @param limitNumRows  The limiting number of rows
     * @param assocKey      Key field to use for building an associative array (optional) (default=null)
     * @param force         whether or not to force a DB-get (optional) (default=false)
     * @param distinct      whether or not to do a select distinct (optional) (default=false)
     *
     * @return The object's data value
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
     * @param where         The where-clause to use
     * @param orderBy          The order-by clause to use
     * @param limitOffset   The limiting offset
     * @param limitNumRows  The limiting number of rows
     * @param assocKey      Key field to use for building an associative array (optional) (default=null)
     * @param force         whether or not to force a DB-get (optional) (default=false)
     * @param distinct      whether or not to do a select distinct (optional) (default=false)
     *
     * @return The object's data value
     */
    public function get($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = null, $force = false, $distinct = false)
    {
        return $this->getWhere($where, $orderBy, $limitOffset, $limitNumRows, $assocKey, $force, $distinct);
    }

    /**
     * Return the currently set object data
     *
     * @return The object's data array
     */
    public function getData()
    {
        return $this->_objData;
    }

    /**
     * Generic select handler for an object. Select (and set) the specified object array
     *
     * @param where         The where-clause to use
     * @param orderBy       The order-by clause to use
     * @param limitOffset   The limiting offset
     * @param limitNumRows  The limiting number of rows
     * @param assocKey      Key field to use for building an associative array (optional) (default=null)
     * @param distinct      whether or not to use the distinct clause
     *
     * @return The selected Object-Array
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
     * Iterate over the object data and post-process it
     *
     * @return The Object Data
     */
    public function selectPostProcess($data = null)
    {
        return $this->_objData;
    }

    /**
     * Get the data from the various input streams provided.
     *
     * @param key        The access key of the object (optional) (default=null, reverts to $this->_objPath)
     * @param default    The default value to return (optional) (default=null)
     * @param source     Where to get the variable from (optional) (default='REQUEST')
     *
     * @return The requested object/value
     */
    public function getDataFromInput($key = null, $default = null, $source = 'REQUEST')
    {
        if (!$key) {
            $key = $this->_objPath;
        }

        $objectArray = FormUtil::getPassedValue($key, $default, $source);

        if ($objectArray) {
            $this->_objData = $objectArray;
            $this->getDataFromInputPostProcess();
            return $this->_objData;
        }

        return $default;
    }

    /**
     * Get the data from the various input streams provided.
     *
     * @param key        The access key of the object (optional) (default=null, reverts to $this->_objPath)
     * @param default    The default value to return (optional) (default=null)
     * @param source     Where to get the variable from (optional) (default=null)
     *
     * @return The requested object/value
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
     * Set the current object data into session
     *
     * @param autocreate            The autocreate passed to SessionUtil::setVar
     * @param overwriteExistingVar  The overwriteExistingVar variable passed to SessionUtil::setVar
     *
     * @return The session data
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
     * Post-Process the data after getting it from Input. Subclasses can define appropriate implementations.
     */
    public function getDataFromInputPostProcess($data = null)
    {
        return $this->_objData;
    }

    /**
     * Post-Process the data after getting it from Session. Subclasses can define appropriate implementations.
     */
    public function getDataFromSessionPostProcess($data = null)
    {
        return $this->_objData;
    }

    /**
     * Pre-Process the data before writing it to Session. Subclasses can define appropriate implementations.
     */
    public function setDataToSessionPreProcess($data = null)
    {
        return $this->_objData;
    }

    /**
     * Generic access function to retrieve data from the specified source
     *
     * @param source    The source data
     * @param key       The access key of the object (optional) (default=null)
     * @param default   The default value to return (optional) (default=null)
     * @param clean     Whether or not to clean the acquired data (optional) (default=true)
     *
     * @return The requested object/value
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
     * Generic function to retrieve
     *
     * @return The Object Data
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
     * Save an object - if it has an ID update it, otherwise insert it
     *
     * @return The result set
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
     * Generic insert handler for an object (ID is inserted into the object data)
     *
     * @return The Object Data
     */
    public function insert()
    {
        if (!$this->insertPreProcess()) {
            return false;
        }

        $res = true;
        foreach ($this->_objData as $k => $v) {
            $res = $res && DBUtil::insertObject($this->_objData[$k], $this->_objType, $this->_objField);
        }

        if ($res) {
            $this->insertPostProcess();
            return $this->_objData;
        }

        return false;
    }

    /**
     * Pre-Process the data prior to an insert. Subclasses can define appropriate implementations.
     */
    public function insertPreProcess($data = null)
    {
        EventManagerUtil::notify(new Event('dbobject.insertpreprocess', $this));
        return $this->_objData;
    }

    /**
     * Post-Process the data after an insert. Subclasses can define appropriate implementations.
     */
    public function insertPostProcess($data = null)
    {
        EventManagerUtil::notify(new Event('dbobject.insertpostprocess', $this));
        return $this->_objData;
    }

    /**
     * Generic upate handler for an object
     *
     * @return The Object Data
     */
    public function update()
    {
        if (!$this->updatePreProcess()) {
            return false;
        }

        $res = true;
        foreach ($this->_objData as $k => $v) {
            $res = $res && DBUtil::updateObject($this->_objData[$k], $this->_objType, '', $this->_objField);
        }

        if ($res) {
            $this->updatePostProcess();
            return $this->_objData;
        }

        return false;
    }

    /**
     * Pre-Process the data prior to an update. Subclasses can define appropriate implementations.
     */
    public function updatePreProcess($data = null)
    {
        EventManagerUtil::notify(new Event('dbobject.updatepreprocess', $this));
        return $this->_objData;
    }

    /**
     * Post-Process the data after an update. Subclasses can define appropriate implementations.
     */
    public function updatePostProcess($data = null)
    {
        EventManagerUtil::notify(new Event('dbobject.updatepostprocess', $this));
        return $this->_objData;
    }

    /**
     * Generic delete handler for an object
     *
     * @return The Object Data
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
     * Pre-Process the data prior a delete. Subclasses can define appropriate implementations.
     */
    public function deletePreProcess($data = null)
    {
        EventManagerUtil::notify(new Event('dbobject.deletepreprocess', $this));
        return $this->_objData;
    }

    /**
     * Post-Process the data after a delete. Subclasses can define appropriate implementations.
     */
    public function deletePostProcess($data = null)
    {
        EventManagerUtil::notify(new Event('dbobject.deletepostprocess', $this));
        return $this->_objData;
    }

    /**
     * Delete with a where-clause
     *
     * @param where         The where-clause to use
     *
     * @return The Object Data
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
     * Clean the acquired input
     *
     * @param objArray    The object-array to clean (optional) (default=null, reverts to $this->_objData)
     *
     * @return The Object Data
     */
    public function clean($objArray = null)
    {
        if (!$objArray) {
            $objArray = & $this->_objData;
        }

        $ak = array_keys($objArray);
        foreach ($ak as $k) {
            $obj = & $objArray[$k];
            $ak2 = array_keys($obj);
            foreach ($ak2 as $f) {
                $obj[$f] = FormUtil::getPassedValue(trim($obj[$f]));
            }
        }

        return $objArray;
    }

    /**
     * Get a selector for the object array
     *
     * @param name          The name of the selector to generate
     * @param selected      The currently selected value (optional) (default=-1234)
     * @param defaultValue  The default value (optional) (default=0)
     * @param defaultText   The default text (optional) (default='')
     * @param allValue      The all-selected value (optional) (default=0)
     * @param allText       The all-selected text (optional) (default='')
     * @param idField       The id field to use (optional) (default=null)
     * @param nameField     The name field to use (optional) (default='title')
     * @param submit        whether or not to submit the form upon selection (optional) (default=false)
     * @param ignoreList    The list of entries to ignore (default=null)
     *
     * @return The generated selector html text
     */
    public function getSelector($name, $selected = -1234, $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $idField = '', $nameField = 'title', $submit = false, $disabled = false, $multipleSize = 1)
    {
        if (!$idField) {
            $idField = $this->_objField;
        }

        return HtmlUtil::getSelector_Generic($name, $this->_objData, $selected, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    public function getValidation()
    {
        return $this->_objValidation;
    }

    /**
     * Post-Process the basic object validation with class specific logic.
     * Subclasses can define appropriate implementations.
     */
    public function validatePostProcess($type = 'user')
    {
        // empty function, should be implemented by child classes
        return true;
    }

    /**
     * Constructur, init everything to sane defaults and handle parameters
     *
     * @return Boolean indicating whether or not validation passed successfully
     */
    public function validate()
    {
        $res1 = ValidationUtil::validateObjectPlain($this->_objPath, $this->_objData, $this->_objValidation); // FIXME!!!, handle array
        $res2 = $this->validatePostProcess();

        if (!$res1 || !$res2) {
            return false;
        }

        return true;
    }

    /**
     * Get the hashcode for this object data array
     */
    public function getHash($includeStandardFields = true, $objArray = null)
    {
        if (!$objArray) {
            $objArray = $this->_objData;
        }

        $arrayHash = array();
        foreach ($objArray as $obj) {
            if (!$includeStandardFields) {
                ObjectUtil::removeStandardFieldsFromObject($obj);
            }
            $arrayHash[] = DataUtil::hash(serialize($obj));
        }

        return DataUtil::hash(serialize($arrayHash));
    }

    /**
     * Clear the failed validation errors for this object
     */
    public function clearValidationErrors()
    {
        FormUtil::clearValidationErrors($this->_objPath);
    }

    /**
     * Clear the failed validation object data for this object
     */
    public function clearFailedValidationData()
    {
        FormUtil::clearValidationFailedObjects($this->_objPath);
    }

    /**
     * Print HTML-formatted debug output for the object
     */
    public function prayer($print = true)
    {
        if ($print) {
            return prayer($this->_objData);
        }

        return _prayer($this);

    }

    /**
     * Print HTML-formatted debug output for the object data
     */
    public function prayerData($print = true, $offset = null)
    {
        if ($print) {
            if ($offset !== null) {
                return prayer($this->_objData[$offset]);
            }
            prayer($this->_objData);
        }

        if ($offset !== null) {
            return _prayer($this->_objData[$offset]);
        }
        return _prayer($this->_objData);
    }
}
