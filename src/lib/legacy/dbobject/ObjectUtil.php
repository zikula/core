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
 * ObjectUtil.
 *
 * @deprecated
 */
class ObjectUtil
{
    /**
     * Add standard PN architecture fields to the table definition.
     *
     * @param array  &$columns   The column list from the tables structure for the current table.
     * @param string $col_prefix Colum prefix (deprecated). Default ''.
     *
     * @return void
     */
    public static function addStandardFieldsToTableDefinition(&$columns, $col_prefix = '')
    {
        // ensure correct handling of prefix with and without underscore
        if ($col_prefix) {
            $plen = strlen($col_prefix);
            if ($col_prefix[$plen - 1] != '_') {
                $col_prefix .= '_';
            }
        }

        // add standard fields
        $columns['obj_status'] = $col_prefix . 'obj_status';
        $columns['cr_date'] = $col_prefix . 'cr_date';
        $columns['cr_uid'] = $col_prefix . 'cr_uid';
        $columns['lu_date'] = $col_prefix . 'lu_date';
        $columns['lu_uid'] = $col_prefix . 'lu_uid';

        return;
    }

    /**
     * Generate the SQL to create the standard PN architecture fields.
     *
     * @param array $columns The column list from the PNTables structure for the current table.
     *
     * @return The generated SQL string
     */
    public static function generateCreateSqlForStandardFields($columns)
    {
        $sql = "$columns[obj_status] CHAR(1)  NOT NULL DEFAULT 'A',
                $columns[cr_date]    DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                $columns[cr_uid]     INTEGER  NOT NULL DEFAULT '0',
                $columns[lu_date]    DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                $columns[lu_uid]     INTEGER  NOT NULL DEFAULT '0'";

        return $sql;
    }

    /**
     * Generate the ADODB DD field descruptors for the standard PN architecture fields.
     *
     * @param array &$columns The column list from the PNTables structure for the current table.
     *
     * @return void
     */
    public static function addStandardFieldsToTableDataDefinition(&$columns)
    {
        $columns['obj_status'] = "C(1) NOTNULL DEFAULT 'A'";
        $columns['cr_date'] = "T NOTNULL DEFAULT '1970-01-01 00:00:00'";
        $columns['cr_uid'] = "I NOTNULL DEFAULT '0'";
        $columns['lu_date'] = "T NOTNULL DEFAULT '1970-01-01 00:00:00'";
        $columns['lu_uid'] = "I NOTNULL DEFAULT '0'";

        return;
    }

    /**
     * Generate the ADODB datadict entries to create the standard PN architecture fields.
     *
     * @param string $table The table to add standard fields using ADODB dictionary method.
     *
     * @return The generated SQL string
     */
    public static function generateCreateDataDictForStandardFields($table)
    {
        $dbtables = DBUtil::getTables();
        $columns = $dbtables["{$table}_column"];
        $sql = ",
                $columns[obj_status] C(1) NOTNULL DEFAULT 'A',
                $columns[cr_date]    T    NOTNULL DEFAULT '1970-01-01 00:00:00',
                $columns[cr_uid]     I    NOTNULL DEFAULT '0',
                $columns[lu_date]    T    NOTNULL DEFAULT '1970-01-01 00:00:00',
                $columns[lu_uid]     I    NOTNULL DEFAULT '0'";

        return $sql;
    }

    /**
     * Set the standard PN architecture fields for object creation/insert.
     *
     * @param array   &$obj           The object we need to set the standard fields on.
     * @param boolean $preserveValues Whether or not to preserve value fields which have a valid value set (optional) (default=false).
     * @param string  $idcolumn       The column name of the primary key column (optional) (default='id').
     *
     * @return void
     */
    public static function setStandardFieldsOnObjectCreate(&$obj, $preserveValues = false, $idcolumn = 'id')
    {
        if (!is_array($obj)) {
            throw new \Exception(__f('%s called on a non-object', 'ObjectUtil::setStandardFieldsOnObjectCreate'));

            return;
        }

        $obj[$idcolumn] = (isset($obj[$idcolumn]) && $obj[$idcolumn] && $preserveValues ? $obj[$idcolumn] : null);
        $obj['cr_date'] = (isset($obj['cr_date']) && $obj['cr_date'] && $preserveValues ? $obj['cr_date'] : DateUtil::getDatetime());
        $obj['cr_uid'] = (isset($obj['cr_uid']) && $obj['cr_uid'] && $preserveValues ? $obj['cr_uid'] : UserUtil::getVar('uid'));
        $obj['lu_date'] = (isset($obj['lu_date']) && $obj['lu_date'] && $preserveValues ? $obj['lu_date'] : DateUtil::getDatetime());
        $obj['lu_uid'] = (isset($obj['lu_uid']) && $obj['lu_uid'] && $preserveValues ? $obj['lu_uid'] : UserUtil::getVar('uid'));

        if (is_null($obj['cr_uid'])) {
            $obj['cr_uid'] = 0;
        }
        if (is_null($obj['lu_uid'])) {
            $obj['lu_uid'] = 0;
        }

        return;
    }

    /**
     * Set the standard PN architecture fields to sane values for an object update.
     *
     * @param array   &$obj           The object we need to set the standard fields on.
     * @param boolean $preserveValues Whether or not to preserve value fields which have a valid value set (optional) (default=false).
     *
     * @return void
     */
    public static function setStandardFieldsOnObjectUpdate(&$obj, $preserveValues = false)
    {
        if (!is_array($obj)) {
            throw new \Exception(__f('%s called on a non-object', 'ObjectUtil::setStandardFieldsOnObjectUpdate'));

            return;
        }

        $obj['lu_date'] = (isset($obj['lu_date']) && $obj['lu_date'] && $preserveValues ? $obj['lu_date'] : DateUtil::getDatetime());
        $obj['lu_uid'] = (isset($obj['lu_uid']) && $obj['lu_uid'] && $preserveValues ? $obj['lu_uid'] : UserUtil::getVar('uid'));

        if (is_null($obj['lu_uid'])) {
            $obj['lu_uid'] = 0;
        }

        return;
    }

    /**
     * Remove the standard fields from the given object.
     *
     * @param array &$obj The object to operate on.
     *
     * @return void
     */
    public static function removeStandardFieldsFromObject(&$obj)
    {
        unset($obj['obj_status']);
        unset($obj['cr_date']);
        unset($obj['cr_uid']);
        unset($obj['lu_date']);
        unset($obj['lu_uid']);

        return;
    }

    /**
     * Create an empty object: all fields known via pntables are set to null
     *
     * @param array $tablename The system tablename registered in the dbtables array.
     *
     * @return The create object (success) or false (failure)
     */
    public static function createEmptyObject($tablename)
    {
        if (!$tablename) {
            return LogUtil::registerError('Invalid [tablename] received');
        }

        $dbtables = DBUtil::getTables();
        if (!isset($dbtables[$tablename])) {
            return LogUtil::registerError("Tablename [$tablename] not set in pntables array");
        }
        if (!isset($dbtables["${tablename}_column"])) {
            return LogUtil::registerError("Columns [${tablename}_column] not set in pntables array");
        }

        $cols = $dbtables["${tablename}_column"];
        $data = [];
        foreach ($cols as $k => $v) {
            $data[$k] = null;
        }

        return $data;
    }

    /**
     * If the specified field is set return it, otherwise return default.
     *
     * @param array  $obj     The object to get the field from.
     * @param string $field   The field to get.
     * @param mixed  $default The default value to return if the field is not set on the object (default=null) (optional).
     *
     * @return The object field value or the default
     */
    public static function getField($obj, $field, $default = null)
    {
        if (isset($obj[$field])) {
            return $obj[$field];
        }

        return $default;
    }

    /**
     * Create an object of the reuqested type and set the cr_date and cr_uid fields.
     *
     * @param string $type The type of the object to create.
     *
     * @return The newly created object
     */
    public static function createObject($type)
    {
        $dbtable = DBUtil::getTables();
        if (!$dbtable[$type]) {
            throw new \Exception(__f('%1$s: Unable to reference object type [%2$s]', ['ObjectUtil::createObject', $type]));
        }

        $obj = [
            '__TYPE__' => $type,
            'cr_date' => DateUtil::getDateTime(),
            'cr_uid' => UserUtil::getVar('uid')
        ];

        return $obj;
    }

    /**
     * Diff 2 objects/arrays.
     *
     * @param array $obj1 The first array/object.
     * @param array $obj2 The second object/array.
     *
     * @return The difference between the two objects
     */
    public static function diff($obj1, $obj2)
    {
        if (!is_array($obj1)) {
            throw new \Exception(__f('%1$s: %2$s is not an object.', ['ObjectUtil::diff', 'object1']));
        }
        if (!is_array($obj2)) {
            throw new \Exception(__f('%1$s: %2$s is not an object.', ['ObjectUtil::diff', 'object2']));
        }

        return array_diff($obj1, $obj2);
    }

    /**
     * Provide an informative extended diff array when comparing 2 arrays.
     *
     * @param array   $a1      Array 1.
     * @param array   $a2      Array 2.
     * @param boolean $detail  Whether or not to give detailed update info (optional (default=false).
     * @param boolean $recurse Whether or not to recurse (optional) (default=true).
     *
     * @return A data array containing the diff results
     */
    public static function diffExtended($a1, $a2, $detail = false, $recurse = true)
    {
        $res = [];

        if (!is_array($a1) || !is_array($a2)) {
            return $res;
        }

        foreach ($a1 as $k => $v) {
            if (is_array($v)) {
                if ($recurse) {
                    $res[$k] = self::diff($v, $a2[$k], $detail);
                }
            } elseif (!isset($a2[$k])) {
                $res[$k] = 'I: ' . $v;
            } elseif ($v !== $a2[$k]) {
                if ($detail) {
                    $res[$k] = [];
                    $res[$k]['old'] = $v;
                    $res[$k]['new'] = $a2[$k];
                } else {
                    $res[$k] = 'U: ' . $a2[$k];
                }
            }

            unset($a2[$k]);
        }

        foreach ($a2 as $k => $v) {
            if (is_array($v)) {
                if ($recurse) {
                    $res[$k] = self::diff($a1[$k], $v, $detail);
                }
            } else {
                $res[$k] = 'D: ' . $v;
            }
        }

        return $res;
    }

    /**
     * Increments or decremnts a sequence number (column position) in a table for a given ID.
     *
     * If exists, it swaps the sequence of the field above or down.
     *
     * @param array  $obj       The object we wish to increment or decrement.
     * @param string $tablename The tablename key for the PNTables structure.
     * @param string $direction Whether we want to increment or decrement the position of the object. Possible values are 'up' (default) and 'down'.
     * @param string $field     The name of the field we wish to resequence.
     * @param string $idcolumn  The column which contains the unique ID.
     * @param string $field2    An additional field to consider in the where-clause.
     * @param string $value2    An additional value to consider in the where-clause.
     *
     * @return true/false on success/failure
     */
    public static function moveField($obj, $tablename, $direction = 'up', $field = 'position', $idcolumn = 'id', $field2 = '', $value2 = '')
    {
        if (!is_array($obj)) {
            throw new \Exception(__f('%1$s: %2$s is not an array.', ['ObjectUtil::moveField', 'object']));
        }

        if (!isset($obj[$idcolumn])) {
            throw new \Exception(__f('Unable to determine a valid ID in object [%1$s, %2$s]', [$idcolumn, 'ObjectUtil::moveField']));
        }

        $dbtables = DBUtil::getTables();
        $table = $dbtables[$tablename];
        $column = $dbtables["{$tablename}_column"];

        if (!$column[$field]) {
            throw new \Exception(__f('%1$s: there is no [%2$s] field in the [%3$s] table.', ['ObjectUtil::moveField', $field, $tablename]));
        }

        // Get info on current position of field
        $where = "$column[$idcolumn]='" . DataUtil::formatForStore($obj[$idcolumn]) . "'";
        $seq = DBUtil::selectField($tablename, $field, $where);

        // Get info on displaced field
        $direction = strtolower($direction);
        $where2 = '';
        if ($field2 && $value2) {
            $where2 = " AND $column[$field2]='" . DataUtil::formatForStore($value2) . "'";
        }

        if ($direction == 'up') {
            $sql = "SELECT $column[$idcolumn], $column[$field]
                    FROM $table
                    WHERE $column[$field] < '" . DataUtil::formatForStore($seq) . "' $where2
                    ORDER BY $column[$field] DESC LIMIT 0,1";
        } elseif ($direction == 'down') {
            $sql = "SELECT $column[$idcolumn], $column[$field]
                    FROM $table
                    WHERE $column[$field] > '" . DataUtil::formatForStore($seq) . "' $where2
                    ORDER BY $column[$field] ASC LIMIT 0,1";
        } else {
            throw new \Exception(__f('%1$s: invalid direction [%2$s] supplied.', ['ObjectUtil::moveField', $direction]));
        }

        $res = DBUtil::executeSQL($sql);
        if ($res->EOF) {
            // No field directly above or below that one
            return false;
        }

        list($altid, $altseq) = $res->fields;

        // Swap sequence numbers
        $sql = "UPDATE $table SET $column[$field]='" . DataUtil::formatForStore($seq) . "' WHERE $column[$idcolumn]='" . DataUtil::formatForStore($altid) . "'";
        $upd1 = DBUtil::executeSQL($sql);
        $sql = "UPDATE $table SET $column[$field]='" . DataUtil::formatForStore($altseq) . "' WHERE $column[$idcolumn]='" . DataUtil::formatForStore($obj[$idcolumn]) . "'";
        $upd2 = DBUtil::executeSQL($sql);

        DBUtil::flushCache($tablename);

        return true;
    }

    /**
     * Retrieve the attribute maps for the specified object.
     *
     * @param array  $obj      The object whose attribute we wish to retrieve.
     * @param string $type     The type of the given object.
     * @param string $idcolumn The column which holds the ID value (optional) (default='id').
     *
     * @return The object attribute (array)
     */
    public static function retrieveObjectAttributes($obj, $type, $idcolumn = 'id')
    {
        if (!$obj) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['object', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$type) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['type', __CLASS__ . '::' . __FUNCTION__]));
        }

        // ensure that only objects with a valid ID are used
        if (!$obj[$idcolumn]) {
            return false;
        }

        $dbtables = DBUtil::getTables();
        $table = $dbtables['objectdata_attributes'];
        $column = $dbtables['objectdata_attributes_column'];

        $where = "WHERE $column[object_id]= '" . DataUtil::formatForStore($obj[$idcolumn]) . "' AND
                        $column[object_type]='" . DataUtil::formatForStore($type) . "'";

        return DBUtil::selectObjectArray('objectdata_attributes', $where);
    }

    /**
     * Expand the given object with it's attributes.
     *
     * @param array  &$obj     The object whose attribute we wish to retrieve.
     * @param string $type     The type of the given object.
     * @param string $idcolumn The column which holds the ID value (optional) (default='id').
     *
     * @return The object which has been altered in place
     */
    public static function expandObjectWithAttributes(&$obj, $type, $idcolumn = 'id')
    {
        if (!isset($obj[$idcolumn]) || !$obj[$idcolumn]) {
            throw new \Exception(__f('Unable to determine a valid ID in object [%1$s, %2$s]', [$type, $idcolumn]));
        }

        $atrs = self::retrieveObjectAttributes($obj, $type, $idcolumn);
        if (!$atrs) {
            return false;
        }

        foreach ($atrs as $atr) {
            $obj['__ATTRIBUTES__'][$atr['attribute_name']] = $atr['value'];
        }

        return $obj;
    }

    /**
     * Store the attributes for the given object.
     *
     * @param array   $obj            The object whose attributes we wish to store.
     * @param string  $type           The type of the given object.
     * @param string  $idcolumn       The idcolumn of the object (optional) (default='id').
     * @param boolean $wasUpdateQuery True after an update and false after an insert.
     *
     * @return true/false on success/failure
     */
    public static function storeObjectAttributes($obj, $type, $idcolumn = 'id', $wasUpdateQuery = true)
    {
        if (!$obj) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['object', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$type) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['type', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$idcolumn) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['idcolumn', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!isset($obj['__ATTRIBUTES__']) || !is_array($obj['__ATTRIBUTES__'])) {
            return false;
        }

        $dbtables = DBUtil::getTables();
        $table = $dbtables['objectdata_attributes'];
        $column = $dbtables['objectdata_attributes_column'];

        $objID = $obj[$idcolumn];
        if (!$objID) {
            throw new \Exception(__f('Unable to determine a valid ID in object [%1$s, %2$s]', [$type, $idcolumn]));
        }

        if ($wasUpdateQuery) {
            // delete old attribute values for this object
            $sql = "DELETE FROM $table WHERE $column[object_type] = '" . DataUtil::formatForStore($type) . "' AND
                                             $column[object_id] = '" . DataUtil::formatForStore($objID) . "'";
            DBUtil::executeSQL($sql);
        }

        DBUtil::flushCache('objectdata_attributes');
        if (isset($dbtables[$type])) {
            DBUtil::flushCache($type);
        }

        $atrs = (isset($obj['__ATTRIBUTES__']) ? $obj['__ATTRIBUTES__'] : null);
        if (!$atrs) {
            return true;
        }

        // process all the attribute fields
        $tobj = [];
        foreach ($atrs as $k => $v) {
            if (strlen($v) || $v == false) {
                // special treatment for false value, otherwise it doesn't get stored at all
                $tobj['attribute_name'] = $k;
                $tobj['object_id'] = $objID;
                $tobj['object_type'] = $type;
                $tobj['value'] = $v;

                DBUtil::insertObject($tobj, 'objectdata_attributes');
            }
        }

        return true;
    }

    /**
     * Update the attributes for the given objects.
     *
     * @param array   $obj      The object whose attributes we wish to store.
     * @param string  $type     The type of the given object.
     * @param string  $idcolumn The idcolumn of the object (optional) (default='id').
     * @param boolean $force    Flag to force the attribute update.
     *
     * @todo check if the function can supersede storeObjectAttributes().
     *
     * @return boolean true/false on success/failure.
     */
    public static function updateObjectAttributes($obj, $type, $idcolumn = 'id', $force = false)
    {
        if (!$obj) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['object', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$type) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['type', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!isset($obj['__ATTRIBUTES__']) || !is_array($obj['__ATTRIBUTES__'])) {
            return false;
        }

        $objID = $obj[$idcolumn];
        if (!$objID) {
            throw new \Exception(__f('Unable to determine a valid ID in object [%1$s, %2$s]', [$type, $idcolumn]));
        }

        $dbtables = DBUtil::getTables();
        $column = $dbtables['objectdata_attributes_column'];

        // select all attributes so that we can check if we have to update or insert
        // this will be an assoc array of attributes with 'attribute_name' as key
        $where = 'WHERE ' . $column['object_type'] . '=\'' . DataUtil::formatForStore($type) . '\'
                    AND ' . $column['object_id'] . '=\'' . DataUtil::formatForStore($objID) . '\'';
        $attrs = DBUtil::selectObjectArray('objectdata_attributes', $where, null, null, null, 'attribute_name');

        // process all the attribute fields
        foreach ($obj['__ATTRIBUTES__'] as $k => $v) {
            // only fill empty attributes when force
            if ($force || strlen($v)) {
                if (!array_key_exists($k, $attrs)) {
                    $newobj['attribute_name'] = $k;
                    $newobj['object_id'] = $objID;
                    $newobj['object_type'] = $type;
                    $newobj['value'] = $v;
                    DBUtil::insertObject($newobj, 'objectdata_attributes');
                } else {
                    $attrs[$k]['value'] = $v;
                    DBUtil::updateObject($attrs[$k], 'objectdata_attributes');
                }
            }
        }

        if (isset($dbtables[$type])) {
            DBUtil::flushCache($type);
        }

        return true;
    }

    /**
     * Delete the attributes for the given object.
     *
     * @param array  &$obj     The object whose attributes we wish to store.
     * @param string $type     The type of the given object.
     * @param string $idcolumn The idcolumn of the object (optional) (default='id').
     *
     * @return the SQL result of the delete operation
     */
    public static function deleteObjectAttributes(&$obj, $type, $idcolumn = 'id')
    {
        if (!$obj) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['object', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$type) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['type', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$idcolumn) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['idcolumn', __CLASS__ . '::' . __FUNCTION__]));
        }

        $dbtables = DBUtil::getTables();
        $table = $dbtables['objectdata_attributes'];
        $column = $dbtables['objectdata_attributes_column'];

        // ensure module was successfully loaded
        if (!$table) {
            return false;
        }

        $objID = $obj[$idcolumn];
        if (!$objID) {
            throw new \Exception(__f('Unable to determine a valid ID in object [%1$s, %2$s]', [$type, $idcolumn]));
        }

        $sql = "DELETE FROM $table WHERE $column[object_type] = '" . DataUtil::formatForStore($type) . "' AND
                                         $column[object_id] = '" . DataUtil::formatForStore($objID) . "'";

        $recordsDeleted = DBUtil::executeSQL($sql);

        DBUtil::flushCache('objectdata_attributes');
        if (isset($dbtables[$type])) {
            DBUtil::flushCache($type);
        }

        return $recordsDeleted;
    }

    /**
     * Delete a single attribute for the given object.
     *
     * @param integer $objID         The object whose attributes we wish to store.
     * @param string  $type          The type of the given object.
     * @param string  $attributename The name of the attribute to delete.
     *
     * @return the SQL result of the delete operation
     */
    public static function deleteObjectSingleAttribute($objID, $type, $attributename)
    {
        if (!$objID) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['objectid', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$type) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['type', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$attributename) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['attributename', __CLASS__ . '::' . __FUNCTION__]));
        }

        $dbtables = DBUtil::getTables();
        $table = $dbtables['objectdata_attributes'];
        $column = $dbtables['objectdata_attributes_column'];

        // ensure module was successfully loaded
        if (!$table) {
            return false;
        }

        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $column['attribute_name'] . ' = \'' . DataUtil::formatForStore($attributename) . '\' AND '
                . $column['object_type'] . ' = \'' . DataUtil::formatForStore($type) . '\' AND '
                . $column['object_id'] . ' = \'' . DataUtil::formatForStore($objID) . '\'';

        $recordDeleted = (bool)DBUtil::executeSQL($sql);

        DBUtil::flushCache('objectdata_attributes');
        if (isset($dbtables[$type])) {
            DBUtil::flushCache($type);
        }

        return $recordDeleted;
    }

    /**
     * Delete the all attributes for the given tab.
     *
     * @param string $type The type/tablename we wish to delete attributes for.
     *
     * @return the SQL result of the delete operation
     */
    public static function deleteAllObjectTypeAttributes($type)
    {
        $dbtables = DBUtil::getTables();
        $table = $dbtables['objectdata_attributes'];
        $column = $dbtables['objectdata_attributes_column'];

        $sql = "DELETE FROM $table WHERE $column[object_type] = '" . DataUtil::formatForStore($type) . "'";
        $res = DBUtil::executeSQL($sql);

        DBUtil::flushCache('objectdata_attributes');
        if (isset($dbtables[$type])) {
            DBUtil::flushCache($type);
        }

        return $res;
    }

    /**
     * Delete all instances of the specified attribute for the given object type.
     *
     * This can be used to remove an attribute from the object attributes table when it is no longer defined by the object type.
     *
     * @param string $type          The type/tablename that defines the given attribute.
     * @param string $attributeName The name of the attribute to delete for all users.
     *
     * @return the SQL result of the delete operation
     */
    public static function deleteObjectTypeAttribute($type, $attributeName)
    {
        $dbtables = DBUtil::getTables();
        $table = $dbtables['objectdata_attributes'];
        $column = $dbtables['objectdata_attributes_column'];

        $sql = "DELETE FROM $table WHERE $column[object_type] = '" . DataUtil::formatForStore($type) . "' ";
        $sql .= "AND $column[attribute_name] = '" . DataUtil::formatForStore($attributeName) . "' ";
        $res = DBUtil::executeSQL($sql);

        DBUtil::flushCache('objectdata_attributes');
        if (isset($dbtables[$type])) {
            DBUtil::flushCache($type);
        }

        return $res;
    }

    /**
     * Retrieve a list of attributes defined in the system.
     *
     * @param string $sort The column to sort by (optional) (default='attribute_name').
     *
     * @return the system attributes field array
     */
    public static function getSystemAttributes($sort = 'attribute_name')
    {
        $dbtables = DBUtil::getTables();
        $table = $dbtables['objectdata_attributes'];
        $column = $dbtables['objectdata_attributes_column'];

        // ensure module was successfully loaded
        if (!$table) {
            return false;
        }

        $atrs = DBUtil::selectFieldArray('objectdata_attributes', 'attribute_name', '', 'attribute_name', true);

        return $atrs;
    }

    /**
     * Retrieve the count for a given attribute name.
     *
     * @param string $atrName The name of the attribute.
     *
     * @return The count for the given attribute
     */
    public static function getAttributeCount($atrName)
    {
        $dbtables = DBUtil::getTables();
        $column = $dbtables['objectdata_attributes_column'];

        $where = "$column[attribute_name]='" . DataUtil::formatForStore($atrName) . "'";

        return DBUtil::selectObjectCount('objectdata_attributes', $where);
    }

    /**
     * Ensure that a meta-data object has reasonable default values.
     *
     * @param array  &$obj      The object we wish to store metadata for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     *
     * @return Altered meta object (meta object is also altered in place)
     */
    public static function fixObjectMetaData(&$obj, $tablename, $idcolumn)
    {
        if (!isset($obj['__META__']) || !is_array($obj['__META__'])) {
            $obj['__META__'] = [];
        }

        $meta = &$obj['__META__'];
        $meta['table'] = $tablename;
        $meta['idcolumn'] = $idcolumn;

        if (!isset($meta['module']) || !$meta['module']) {
            $meta['module'] = ModUtil::getName();
        }

        if (!isset($meta['obj_id']) || !$meta['obj_id']) {
            $meta['obj_id'] = (isset($obj[$idcolumn]) ? $obj[$idcolumn] : -1);
        }

        return $meta;
    }

    /**
     * Insert a meta data object.
     *
     * @param array  &$obj      The object we wish to store metadata for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     *
     * @return The result from the metadata insert operation
     */
    public static function insertObjectMetaData(&$obj, $tablename, $idcolumn = 'id')
    {
        $meta = self::fixObjectMetaData($obj, $tablename, $idcolumn);
        if ($meta['obj_id'] > 0) {
            $result = DBUtil::insertObject($meta, 'objectdata_meta');
            $obj['__META__']['metaid'] = $meta['id'];

            return $meta['id'];
        }

        $dbtables = DBUtil::getTables();
        if (isset($dbtables[$tablename])) {
            DBUtil::flushCache($tablename);
        }

        return false;
    }

    /**
     * Update a meta data object.
     *
     * @param array  &$obj      The object we wish to store metadata for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     *
     * @return The result from the metadata insert operation
     */
    public static function updateObjectMetaData(&$obj, $tablename, $idcolumn = 'id')
    {
        if (!isset($obj['__META__']['id'])) {
            return false;
        }

        $meta = $obj['__META__'];
        if ($meta['obj_id'] > 0) {
            return DBUtil::updateObject($meta, 'objectdata_meta');
        }

        $dbtables = DBUtil::getTables();
        if (isset($dbtables[$tablename])) {
            DBUtil::flushCache($tablename);
        }

        return true;
    }

    /**
     * Delete a meta data object.
     *
     * @param array  &$obj      The object we wish to delete metadata for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     *
     * @return The result from the metadata insert operation
     */
    public static function deleteObjectMetaData(&$obj, $tablename, $idcolumn = 'id')
    {
        self::fixObjectMetaData($obj, $tablename, $idcolumn);

        if (isset($obj['__META__']['id']) && $obj['__META__']['id']) {
            $rc = DBUtil::deleteObjectByID($obj['__META__'], 'objectdata_meta');
        } elseif (isset($obj['__META__']['idcolumn']) && $obj['__META__']['obj_id']) {
            $dbtables = DBUtil::getTables();
            $meta_column = $dbtables['objectdata_meta_column'];

            $meta = $obj['__META__'];
            $where = "WHERE $meta_column[module]='" . DataUtil::formatForStore($meta['module']) . "'
                        AND $meta_column[table]='" . DataUtil::formatForStore($meta['table']) . "'
                        AND $meta_column[idcolumn]='" . DataUtil::formatForStore($meta['idcolumn']) . "'
                        AND $meta_column[obj_id]='" . DataUtil::formatForStore($meta['obj_id']) . "'";

            $rc = DBUtil::deleteObject([], 'objectdata_meta', $where);
        }

        $dbtables = DBUtil::getTables();
        if (isset($dbtables[$tablename])) {
            DBUtil::flushCache($tablename);
        }

        return (bool)$rc;
    }

    /**
     * Retrieve object meta data.
     *
     * @param array  &$obj      The object we wish to retrieve metadata for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     *
     * @return The object with the meta data filled in
     */
    public static function retrieveObjectMetaData(&$obj, $tablename, $idcolumn = 'id')
    {
        $meta = self::fixObjectMetaData($obj, $tablename, $idcolumn);
        if ($meta['obj_id'] > 0) {
            $dbtables = DBUtil::getTables();
            $meta_column = $dbtables['objectdata_meta_column'];

            $where = "WHERE $meta_column[module]='" . DataUtil::formatForStore($meta['module']) . "'
                        AND $meta_column[table]='" . DataUtil::formatForStore($meta['table']) . "'
                        AND $meta_column[idcolumn]='" . DataUtil::formatForStore($meta['idcolumn']) . "'
                        AND $meta_column[obj_id]='" . DataUtil::formatForStore($meta['obj_id']) . "'";

            return DBUtil::selectObject('objectdata_meta', $where);
        }

        return true;
    }

    /**
     * Expand an object with it's Meta data.
     *
     * @param array  &$obj      The object we wish to get the metadata for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     *
     * @return The object with the meta data filled in. The object passed in is altered in place
     */
    public static function expandObjectWithMeta(&$obj, $tablename, $idcolumn = 'id')
    {
        if (!isset($obj[$idcolumn]) || !$obj[$idcolumn]) {
            throw new \Exception(__f('Unable to determine a valid ID in object [%1$s, %2$s]', [$type, $idcolumn]));
        }

        $meta = self::retrieveObjectMetaData($obj, $tablename, $idcolumn);
        if (!$meta) {
            return false;
        }

        $obj['__META__'] = $meta;

        return $obj;
    }

    /**
     * Insert a categorization data object.
     *
     * @param array   $obj            The object we wish to store categorization data for.
     * @param string  $tablename      The object's tablename.
     * @param string  $idcolumn       The object's idcolumn (optional) (default='id').
     * @param boolean $wasUpdateQuery True after an update and false after an insert.
     *
     * @return The result from the category data insert operation
     */
    public static function storeObjectCategories($obj, $tablename, $idcolumn = 'id', $wasUpdateQuery = true)
    {
        if (!$obj) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['object', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$tablename) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['tablename', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!$idcolumn) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['idcolumn', __CLASS__ . '::' . __FUNCTION__]));
        }

        if (!ModUtil::dbInfoLoad('ZikulaCategoriesModule')) {
            return false;
        }

        if (!isset($obj['__CATEGORIES__']) || !is_array($obj['__CATEGORIES__']) || !$obj['__CATEGORIES__']) {
            return false;
        }

        if ($wasUpdateQuery) {
            self::deleteObjectCategories($obj, $tablename, $idcolumn);
        }

        // ensure that we don't store duplicate object mappings
        $values = [];
        foreach ($obj['__CATEGORIES__'] as $k => $v) {
            if (isset($values[$v])) {
                unset($obj['__CATEGORIES__'][$k]);
            } else {
                $values[$v] = 1;
            }
        }

        // cache category id arrays to improve performance with DBUtil::(insert|update)ObjectArray()
        static $modTableCategoryIDs = [];

        // Get the ids of the categories properties of the object
        $modname = isset($obj['__META__']['module']) ? $obj['__META__']['module'] : ModUtil::getName();
        $reg_key = $modname . '_' . $tablename;

        if (!isset($modTableCategoryIDs[$reg_key])) {
            $modTableCategoryIDs[$reg_key] = CategoryRegistryUtil::getRegisteredModuleCategoriesIds($modname, $tablename);
        }
        $reg_ids = $modTableCategoryIDs[$reg_key];

        $cobj = [
            'table' => $tablename,
            'obj_idcolumn' => $idcolumn
        ];

        $res = true;
        foreach ($obj['__CATEGORIES__'] as $prop => $cat) {
            // if there's all the data and the Registry exists
            // the category is mapped
            if ($cat && $prop && isset($reg_ids[$prop])) {
                $cobj['id'] = '';
                $cobj['modname'] = $modname;
                $cobj['obj_id'] = $obj[$idcolumn];
                $cobj['category_id'] = $cat;
                $cobj['reg_id'] = $reg_ids[$prop];

                $res = DBUtil::insertObject($cobj, 'categories_mapobj');
            }
        }

        $dbtables = DBUtil::getTables();
        if (isset($dbtables[$tablename])) {
            DBUtil::flushCache($tablename);
        }

        return (bool)$res;
    }

    /**
     * Delete a meta data object.
     *
     * @param array  $obj       The object we wish to delete categorization data for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='obj_id').
     *
     * @return The result from the metadata insert operation
     */
    public static function deleteObjectCategories($obj, $tablename, $idcolumn = 'obj_id')
    {
        if (!ModUtil::dbInfoLoad('ZikulaCategoriesModule')) {
            return false;
        }

        $where = "tablename='" . DataUtil::formatForStore($tablename) . "' AND obj_id='" . DataUtil::formatForStore($obj[$idcolumn]) . "' AND obj_idcolumn='" . DataUtil::formatForStore($idcolumn) . "'";
        $categoriesDeleted = (bool)DBUtil::deleteWhere('categories_mapobj', $where);

        $dbtables = DBUtil::getTables();
        if (isset($dbtables[$tablename])) {
            DBUtil::flushCache($tablename);
        }

        return $categoriesDeleted;
    }

    /**
     * Retrieve object category data.
     *
     * @param array  $obj       The object we wish to retrieve metadata for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     *
     * @return The object with the meta data filled in
     */
    public static function retrieveObjectCategoriesList($obj, $tablename, $idcolumn = 'id')
    {
        static $cache;

        $key = $tablename . '_' . $obj[$idcolumn];
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if (!ModUtil::dbInfoLoad('ZikulaCategoriesModule')) {
            return false;
        }

        $pntabs = DBUtil::getTables();
        $cat = $pntabs['categories_mapobj_column'];

        $where = "WHERE tbl.$cat[table]='" . DataUtil::formatForStore($tablename) . "'
                    AND tbl.$cat[obj_idcolumn]='" . DataUtil::formatForStore($idcolumn) . "'
                    AND tbl.$cat[obj_id]='" . DataUtil::formatForStore($obj[$idcolumn]) . "'";
        $orderby = "ORDER BY tbl.$cat[category_id]";

        $joinInfo[] = [
            'join_table' => 'categories_registry',
            'join_field' => 'property',
            'object_field_name' => 'property',
            'compare_field_table' => 'reg_id',
            'compare_field_join' => 'id'
        ];

        $cache[$key] = DBUtil::selectExpandedFieldArray('categories_mapobj', $joinInfo, 'category_id', $where, $orderby, false, 'property');

        return $cache[$key];
    }

    /**
     * Retrieve object category data.
     *
     * @param array   $obj                   The object we wish to retrieve metadata for.
     * @param string  $tablename             The object's tablename.
     * @param string  $idcolumn              The object's idcolumn (optional) (default='id').
     * @param string  $assocKey              The field to use for the associative array index (optional) (default='id').
     * @param boolean $enablePermissionCheck Whether or not to enable the permission filter (optional) (default=true).
     *
     * @return The object with the meta data filled in
     */
    public static function retrieveObjectCategoriesObjects($obj, $tablename, $idcolumn = 'id', $assocKey = '', $enablePermissionCheck = true)
    {
        $catlist = self::retrieveObjectCategoriesList($obj, $tablename, $idcolumn);
        if (!$catlist) {
            return [];
        }

        $cats = implode(',', array_values($catlist));
        $where = "c.id IN ($cats)";
        $catsdata = CategoryUtil::getCategories($where, '', 'id', $enablePermissionCheck);

        $result = [];
        foreach ($catlist as $prop => $cat) {
            if (isset($catsdata[$cat])) {
                $result[$prop] = $catsdata[$cat];
            }
        }

        return $result;
    }

    /**
     * Expand an object array with it's category data.
     *
     * @param array  &$objArray The object array we wish to get the category for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     * @param string $field     The category field to return the object's category info (optional) (default='id').
     * @param string $locale    Locale.
     *
     * @return The object with the meta data filled in. The object passed in is altered in place
     */
    public static function expandObjectArrayWithCategories(&$objArray, $tablename, $idcolumn = 'id', $field = 'id', $locale = 'en')
    {
        if (!ModUtil::dbInfoLoad('ZikulaCategoriesModule')) {
            return false;
        }

        if (!$objArray || !is_array($objArray)) {
            return false;
        }

        $pntabs = DBUtil::getTables();
        $tab = $pntabs['categories_mapobj'];
        $col = $pntabs['categories_mapobj_column'];

        $w1 = [];
        $w2 = [];
        foreach ($objArray as $obj) {
            $w1[] = DataUtil::formatForStore($obj[$idcolumn]);
        }

        $t = implode(',', $w1);
        $w2[] = "tbl.$col[obj_id] IN (" . $t . ')';
        $w2[] = "tbl.$col[table]='" . DataUtil::formatForStore($tablename) . "' AND tbl.$col[obj_idcolumn]='" . DataUtil::formatForStore($idcolumn) . "' ";
        $where = "WHERE " . implode(' AND ', $w2);
        $sort = "ORDER BY tbl.$col[obj_id], tbl.$col[category_id]";

        $joinInfo[] = [
            'join_table' => 'categories_registry',
            'join_field' => 'property',
            'object_field_name' => 'property',
            'compare_field_table' => 'reg_id',
            'compare_field_join' => 'id'
        ];

        $maps = DBUtil::selectExpandedObjectArray('categories_mapobj', $joinInfo, $where, $sort);
        if (!$maps) {
            return false;
        }

        // since we don't know the order in which our data array will be, we
        // have to do this iteratively. However, this is still a lot faster
        // than doing a select for every data line.
        $catlist = [];
        foreach ($objArray as $k => $obj) {
            $last = null;
            foreach ($maps as $map) {
                if ($map['obj_id'] == $obj[$idcolumn]) {
                    $last = $map['obj_id'];
                    $prop = $map['property'];
                    $catid = $map['category_id'];
                    $objArray[$k]['__CATEGORIES__'][$prop] = $catid;
                    $catlist[] = $catid;
                }

                if ($last && $last != $map['obj_id']) {
                    break;
                }
            }
        }

        // now retrieve the full category data
        $where = 'WHERE id IN (' . implode(',', $catlist) . ')';

        $catArray = new Categories_DBObject_CategoryArray();
        $data = $catArray->get($where, '', -1, -1, 'id');

        // use the cagtegory map created previously to build the object category array
        foreach ($objArray as $k => $obj) {
            if (isset($obj['__CATEGORIES__'])) {
                foreach ($obj['__CATEGORIES__'] as $prop => $cat) {
                    $data[$cat]['path'] = str_replace('__SYSTEM__', __('Root Category'), $data[$cat]['path']);
                    $objArray[$k]['__CATEGORIES__'][$prop] = $data[$cat];
                }
            }
        }

        // now generate the relative paths
        //$rootCatID = CategoryRegistryUtil::getRegisteredModuleCategory (ModUtil::getName(), $tablename, 'main_table', '/__SYSTEM__/Modules/Quotes/Default');
        //postProcessExpandedObjectArrayCategories ($objArray, $rootCatID, false);

        return $objArray;
    }

    /**
     * Expand an object with it's category data.
     *
     * @param array  &$obj      The object we wish to get the metadata for.
     * @param string $tablename The object's tablename.
     * @param string $idcolumn  The object's idcolumn (optional) (default='id').
     * @param string $assocKey  The field to use for the associative array index (optional) (default='id').
     *
     * @return The object with the meta data filled in. The object passed in is altered in place
     */
    public static function expandObjectWithCategories(&$obj, $tablename, $idcolumn = 'id', $assocKey = '')
    {
        if (!isset($obj[$idcolumn]) || !$obj[$idcolumn]) {
            throw new \Exception(__f('Unable to determine a valid ID in object [%1$s, %2$s]', [$type, $idcolumn]));
        }

        if (!ModUtil::dbInfoLoad('ZikulaCategoriesModule')) {
            return false;
        }

        $cats = self::retrieveObjectCategoriesObjects($obj, $tablename, $idcolumn, $assocKey, false);
        $obj['__CATEGORIES__'] = $cats;

        // now generate the relative paths
        //$module = ModUtil::getName();
        //$rootCatID = CategoryRegistryUtil::getRegisteredModuleCategory (ModUtil::getName(), $tablename, 'main_table', '/__SYSTEM__/Modules/Quotes/Default');
        //postProcessExpandedObjectCategories ($obj, $rootCatID);

        return $obj;
    }

    /**
     * Post-process an object-array's expanded categories to generate relative paths.
     *
     * @param array   &$objArray   The object array we wish to post-process.
     * @param array   $rootCats    The root category ID for the relative path creation.
     * @param boolean $includeRoot Whether or not to include the root folder in the relative path (optional) (default=false).
     *
     * @return The object-array with the additionally expanded category data is altered in place and returned
     */
    public static function postProcessExpandedObjectArrayCategories(&$objArray, $rootCats, $includeRoot = false)
    {
        if (!$objArray) {
            throw new \Exception(__f('Invalid object in %s', 'postProcessExpandedObjectArrayCategories'));
        }

        $ak = array_keys($objArray);
        foreach ($ak as $k) {
            if (isset($objArray[$k]['__CATEGORIES__']) && $objArray[$k]['__CATEGORIES__']) {
                self::postProcessExpandedObjectCategories($objArray[$k]['__CATEGORIES__'], $rootCats, $includeRoot);
            }
        }

        return $objArray;
    }

    /**
     * Post-process an object's expanded category data to generate relative paths.
     *
     * @param array   &$obj        The object we wish to post-process.
     * @param array   $rootCatsIDs The root category ID for the relative path creation.
     * @param boolean $includeRoot Whether or not to include the root folder in the relative path (optional) (default=false).
     *
     * @return The object with the additionally expanded category data is altered in place and returned
     */
    public static function postProcessExpandedObjectCategories(&$obj, $rootCatsIDs, $includeRoot = false)
    {
        if (!$obj) {
            throw new \Exception(__f('Invalid object in %s', 'postProcessExpandedObjectCategories'));
        }

        $rootCats = CategoryUtil::getCategoriesByRegistry($rootCatsIDs);

        if (empty($rootCats)) {
            return false;
        }

        // if the function was called to process the object categories
        if (isset($obj['__CATEGORIES__'])) {
            $ak = array_keys($obj['__CATEGORIES__']);
            foreach ($ak as $prop) {
                CategoryUtil::buildRelativePathsForCategory($rootCats[$prop], $obj['__CATEGORIES__'][$prop], $includeRoot);
            }
            self::makeBC($obj['__CATEGORIES__']);
            // else, if the function was called to process the categories array directly
        } else {
            $ak = array_keys($obj);
            foreach ($ak as $prop) {
                CategoryUtil::buildRelativePathsForCategory($rootCats[$prop], $obj[$prop], $includeRoot);
            }
            self::makeBC($obj);
        }

        return;
    }

    /**
     * Convert new Doctrine Entity version of Category to BC Compatible version
     * by removing or converting related properties
     *
     * @param array $obj array of Categories indexed by their category registry title (e.g. "Main")
     */
    private static function makeBC(&$obj)
    {
        foreach ($obj as &$prop) {
            unset($prop['parent'], $prop['children']);
            $prop['attributes'] = isset($prop['attributes']) && ($prop['attributes'] instanceof \Doctrine\ORM\PersistentCollection) ? $prop['attributes']->toArray() : [];
            $prop['cr_date'] = isset($prop['cr_date']) && ($prop['cr_date'] instanceof \DateTime) ? $prop['cr_date']->format('Y-m-d H:i:s') : $prop['cr_date'];
            $prop['lu_date'] = isset($prop['lu_date']) && ($prop['lu_date'] instanceof \DateTime) ? $prop['lu_date']->format('Y-m-d H:i:s') : $prop['lu_date'];
            $prop['cr_uid'] = isset($prop['cr_uid']) && ($prop['cr_uid'] instanceof \Zikula\UsersModule\Entity\UserEntity) ? $prop['cr_uid']->getUid() : $prop['cr_uid'];
            $prop['lu_uid'] = isset($prop['lu_uid']) && ($prop['lu_uid'] instanceof \Zikula\UsersModule\Entity\UserEntity) ? $prop['lu_uid']->getUid() : $prop['lu_uid'];
        }
    }
}
