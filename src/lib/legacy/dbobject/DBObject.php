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
 * DBObject.
 *
 * @deprecated
 */
class DBObject
{
    /**
     * Object type.
     *
     * @var string
     */
    public $_objType = 'DBOBJECT';

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
     * Constructor, init everything to sane defaults and handle parameters.
     *
     * @param mixed  $init  Initialization value (see _init() for details)
     * @param string $key   The DB key to use to retrieve the object (optional) (default=null)
     * @param string $field The field containing the key value (optional) (default=null)
     */
    public function __construct($init = null, $key = null, $field = null)
    {
        $this->_objField = $field ? $field : 'id';
        $this->_objPath = 'DBOBJECT_PATH';
        $this->_objSessionPath = null;

        $this->_init($init, $key, $field);
    }

    /**
     * Internal intialization routine.
     *
     * If $_init is an arrary it is set(), otherwise it is interpreted as a string specifying
     * the source from where the data should be retrieved from.
     *
     * @param mixed  $init  Initialization value (can be an object or a string directive)
     * @param string $key   The DB key to use to retrieve the object (optional) (default=null)
     * @param strubg $field The field containing the key value (optional) (default=null)
     *
     * @return void
     */
    public function _init($init = null, $key = null, $field = null)
    {
        if ($this->_objType != 'DBOBJECT') {
            $dbtables = DBUtil::getTables();
            $tkey = $this->_objType;
            $ckey = $this->_objType . "_column";
            $this->_table = isset($dbtables[$tkey]) ? $dbtables[$tkey] : null;
            $this->_columns = isset($dbtables[$ckey]) ? $dbtables[$ckey] : null;
            if ($field) {
                $this->_objField = $field;
            } else {
                $this->_objField = 'id';
            }
        }

        if (!$init) {
            return;
        }

        if (is_array($init)) {
            $this->setData($init);
        } elseif (is_string($init)) {
            switch ($init) {
                case self::GET_FROM_DB:
                    if (!$key) {
                        throw new \Exception("Invalid DB-key in DBObject::_init() ...");
                    }
                    $this->get($key, $field);
                    break;

                case self::GET_FROM_GET:
                case self::GET_FROM_POST:
                case self::GET_FROM_REQUEST:
                    $this->setData($this->getDataFromInput($this->_objPath, null, $init));
                    break;

                case self::GET_FROM_SESSION:
                    $this->getDataFromSource($_SESSION, $this->_objPath);
                    break;

                case self::GET_FROM_VALIDATION_FAILED:
                    $this->getDataFromSource($_SESSION['validationFailedObjects'], $this->_objPath);
                    break;

                default:
                    throw new \Exception(__f("Error! An invalid initialization directive '%s' found in 'DBObject::_init()'.", $init));
            }
        } else {
            throw new \Exception(__f("Error! An unexpected parameter type initialization '%s' was encountered in 'DBObject::_init()'.", $init));
        }
    }

    /**
     * Generate an empty object with the fields initialized to null.
     *
     * @return array Empty data object
     */
    public function createEmptyObject()
    {
        $data = ObjectUtil::createEmptyObject($this->_objType);
        if ($data) {
            $this->_objData = $data;
        }

        return $this->_objData;
    }

    /**
     * Set (and return) the object data.
     *
     * @param array   $data  The data to assign
     * @param boolean $cache Whether or not to cache the data in session (optional) (default=true) - currently unused
     *
     * @return array Object data
     */
    public function setData($data, $cache = false)
    {
        if (!is_array($data)) {
            return false;
        }

        $this->_objData = $data;

        return $this->_objData;
    }

    /**
     * Return the current object data. If $key and $field are supplied the object is fetched again from the database.
     *
     * @param string $key   The record's key value
     * @param string $field The field we wish to get (optional) (default=null)
     *
     * @return The object's data value
     */
    public function get($key = 0, $field = null)
    {
        if (!$key) {
            return $this->_objData;
        }

        return $this->select($key, $field);
    }

    /**
     * Return the currently set object data.
     *
     * @return The object's data array
     */
    public function getData()
    {
        return $this->_objData;
    }

    /**
     * Return the object ID field name.
     *
     * @return The object's ID field name
     */
    public function getIDField()
    {
        return $this->_objField;
    }

    /**
     * Return the object-ID or false.
     *
     * @return The object-ID or false
     */
    public function getID()
    {
        if (isset($this->_objData[$this->_objField])) {
            return $this->_objData[$this->_objField];
        }

        return false;
    }

    /**
     * Return/Select the object using the given where clause.
     *
     * @param string $where The where-clause to use
     *
     * @return The object's data value
     */
    public function getWhere($where)
    {
        return $this->select(null, null, $where);
    }

    /**
     * Get the object which failed validation.
     *
     * @return array The object's data value
     */
    public function getFailedValidationData()
    {
        $this->_objData = FormUtil::getFailedValidationObjects($this->_objPath);

        return $this->_objData;
    }

    /**
     * Return whether or not this object has a set id field.
     *
     * @return boolean
     */
    public function hasID()
    {
        return isset($this->_objData[$this->_objField]) && $this->_objData[$this->_objField];
    }

    /**
     * Select the object from the database using the specified key (and field).
     *
     * @param string $key   The record's key value (if init is a string directive)
     * @param string $field The key-field we wish to select by (optional) (default=null, reverts to this->_objField)
     * @param string $where The key-field we wish to select by (optional) (default='')
     *
     * @return array The object's data value
     */
    public function select($key, $field = '', $where = '')
    {
        if (!$this->_objType) {
            return [];
        }

        if (!$field) {
            $field = $this->_objField;
        }

        if ((!$key || !$field) && !$where) {
            return [];
        }

        // use explicit where clause
        if ($where) {
            if ($this->_objJoin) {
                $objArray = DBUtil::selectExpandedObjectArray($this->_objType, $this->_objJoin, $where, '', -1, -1, '', $this->_objPermissionFilter, $this->_objCategoryFilter, $this->_objColumnArray);
            } else {
                $objArray = DBUtil::selectObjectArray($this->_objType, $where, '', -1, -1, '', $this->_objPermissionFilter, $this->_objCategoryFilter, $this->_objColumnArray);
            }

            if ($objArray === false) {
                $this->_objData = false;
            } else {
                if (isset($objArray[0])) {
                    $this->_objData = $objArray[0];
                } else {
                    $this->_objData = [];
                }
            }

            $this->_objKey = $where;
        } else {
            // generic key=>value lookup
            if ($this->_objJoin) {
                $this->_objData = DBUtil::selectExpandedObjectById($this->_objType, $this->_objJoin, $key, $field, $this->_objColumnArray, $this->_objPermissionFilter, $this->_objCategoryFilter);
            } else {
                $this->_objData = DBUtil::selectObjectById($this->_objType, $key, $field, $this->_objColumnArray, $this->_objPermissionFilter, $this->_objCategoryFilter);
            }

            $this->_objKey = $key;
            $this->_objField = $field;
        }

        $this->selectPostProcess();

        return $this->_objData;
    }

    /**
     * Post-Process the newly selected object. Subclasses can define appropriate implementations.
     *
     * @param array $obj Override object (needed for selectObjectArray processing) (optional) (default=null)
     *
     * @return array The object's data value
     */
    public function selectPostProcess($obj = null)
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
     * @return mixed The requested object/value
     */
    public function getDataFromInput($key = null, $default = null, $source = 'REQUEST', $filter = null, array $args = [])
    {
        if (!$key) {
            $key = $this->_objPath;
        }

        $obj = FormUtil::getPassedValue($key, $default, $source, $filter, $args, $this->_objPath);

        if ($obj) {
            $this->_objData = $obj;
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
     * @return mixed The requested object/value
     */
    public function getDataFromSession($key = null, $default = null, $path = '', $autocreate = true, $overwriteExistingVar = false)
    {
        if (!$key) {
            $key = $this->_objPath;
        }
        if (!$path) {
            $path = $this->_objSessionPath;
        }

        $obj = SessionUtil::getVar($key, $default, $path, $autocreate, $overwriteExistingVar);
        if ($obj && is_array($obj)) {
            $this->_objData = $obj;
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
     * @param string $key     Field key
     * @param mixed  $default Default return value
     *
     * @return mixed The Object Data
     */
    public function getDataField($key, $default = null)
    {
        $objData = $this->_objData;
        if (isset($objData[$key])) {
            return $objData[$key];
        }

        return $default;
    }

    /**
     * Generic function to retrieve.
     *
     * @param string $key   The access key of the object field
     * @param mixed  $value The value to assign to the specified object field
     *
     * @return mixed The value which was set into the specified object field
     */
    public function setDataField($key, $value)
    {
        $objData = $this->_objData;
        $objData[$key] = $value;

        return $value;
    }

    /**
     * Generic insert handler for an object (ID is inserted into the object data).
     *
     * If the object contains a valid ID, it is updated, otherwise it it inserted.
     *
     * @return array|boolean The result set
     */
    public function save()
    {
        $rc = true;
        if ($this->hasID()) {
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

        $res = DBUtil::insertObject($this->_objData, $this->_objType, $this->_objField, $this->_objInsertPreserve, $this->_objInsertForce);
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
        EventUtil::dispatch('dbobject.insertpreprocess', new \Zikula\Core\Event\GenericEvent($this));

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
        EventUtil::dispatch('dbobject.insertpostprocess', new \Zikula\Core\Event\GenericEvent($this));

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

        $res = DBUtil::updateObject($this->_objData, $this->_objType, '', $this->_objField, $this->_objInsertPreserve);
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
        EventUtil::dispatch('dbobject.updatepreprocess', new \Zikula\Core\Event\GenericEvent($this));

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
        EventUtil::dispatch('dbobject.updatepostprocess', new \Zikula\Core\Event\GenericEvent($this));

        return $this->_objData;
    }

    /**
     * Generic delete handler for an object.
     *
     * @return array|boolean The Object Data
     */
    public function delete()
    {
        if ($this->hasID()) {
            if (!$this->deletePreProcess()) {
                return false;
            }
            $res = DBUtil::deleteObjectById($this->_objType, $this->_objData[$this->_objField], $this->_objField);
            if ($res) {
                $this->deletePostProcess();

                return $this->_objData;
            }
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
        EventUtil::dispatch('dbobject.deletepreprocess', new \Zikula\Core\Event\GenericEvent($this));

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
        EventUtil::dispatch('dbobject.deletepostprocess', new \Zikula\Core\Event\GenericEvent($this));

        return $this->_objData;
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
     * Generic function to validate an object.
     *
     * @return boolean indicating whether validation has passed or failed
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
            $res = $res && ValidationUtil::validateObjectPlain($this->_objPath, $this->_objData, $this->_objValidation);
            if ($res) {
                $res = $res && $this->validatePostProcess();
            }
        }

        return $res;
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
        EventUtil::dispatch('dbobject.validatepreprocess', new \Zikula\Core\Event\GenericEvent($this));

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
        EventUtil::dispatch('dbobject.validatepostprocess', new \Zikula\Core\Event\GenericEvent($this));

        return true;
    }

    /**
     * Get the hashcode for this object data array.
     *
     * @param boolean $includeStandardFields Wheter or not to include standard fields to hashcode
     * @param array   $objData               Object data
     *
     * @return string Hashcode
     */
    public function getHash($includeStandardFields = true, $objData = null)
    {
        if (!$objData) {
            $objData = $this->_objData;
        }

        if (!$includeStandardFields) {
            $objData = $this->_objData; // copy
            ObjectUtil::removeStandardFieldsFromObject($objData);
        }

        return hash('sha1', serialize($objData));
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
            return prayer($this);
        }

        return _prayer($this);
    }

    /**
     * Print HTML-formatted debug output for the object data.
     *
     * @param boolean $print Whether to print or to return the object data debug output
     *
     * @return string|void Debug output
     */
    public function prayerData($print = true)
    {
        if ($print) {
            return prayer($this->_objData);
        }

        return _prayer($this->_objData);
    }
}
