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
 * FormUtil
 *
 * @package Zikula_Core
 * @subpackage FormUtil
 */
class FormUtil
{
    /**
     * Return the requested key from input in a safe way. This function
     * is safe to use for recursive arrays and either returns a non-empty
     * string or the (optional) default.
     *
     * This method is based on pnVarCleanFromInput but array-safe.
     *
     * @param key        The field to return
     * @param default    The value to return if the requested field is not found (optional) (default=false)
     * @param source     The sourc field to get a parameter from
     *
     * @return The requested input key or the specified default
     */
    public static function getPassedValue($key, $default = null, $source = null)
    {
        if (!$key) {
            return pn_exit(__f('Empty %1$s passed to %2$s.', array('key', 'FormUtil::getPassedValueSafe')));
        }

        $source = strtoupper($source);
        $src = ($source ? $source : 'REQUEST') . '_' . ($default != null ? $default : 'null');

        $doClean = false;
        switch (true)
        {
            case (isset($_REQUEST[$key]) && !isset($_FILES[$key]) && (!$source || $source == 'R' || $source == 'REQUEST')):
                $value = $_REQUEST[$key];
                $doClean = true;
                break;
            case isset($_GET[$key]) && (!$source || $source == 'G' || $source == 'GET'):
                $value = $_GET[$key];
                $doClean = true;
                break;
            case isset($_POST[$key]) && (!$source || $source == 'P' || $source == 'POST'):
                $value = $_POST[$key];
                $doClean = true;
                break;
            case isset($_COOKIE[$key]) && (!$source || $source == 'C' || $source == 'COOKIE'):
                $value = $_COOKIE[$key];
                $doClean = true;
                break;
            case isset($_FILES[$key]) && ($source == 'F' || $source == 'FILES'):
                $value = $_FILES[$key];
                break;
            case (isset($_GET[$key]) || isset($_POST[$key])) && ($source == 'GP' || $source == 'GETPOST'):
                if (isset($_GET[$key])) {
                    $value = $_GET[$key];
                }
                if (isset($_POST[$key])) {
                    $value = $_POST[$key];
                }
                $doClean = true;
                break;
            default:
                if ($source) {
                    static $valid = array('R', 'REQUEST', 'G', 'GET', 'P', 'POST', 'C', 'COOKIE', 'F', 'FILES', 'GP', 'GETPOST');
                    if (!in_array($source, $valid)) {
                        pn_exit(__f('Invalid input source [%s] received.', DataUtil::formatForDisplay($source)));
                        return $default;
                    }
                }
        }

        if (isset($value) && !is_null($value)) {
            if (is_array($value)) {
                self::cleanArray($value);
            } else {
                static $alwaysclean = array('name', 'module', 'type', 'file', 'authid');
                if (in_array($key, $alwaysclean)) {
                    $doClean = true;
                }
                if ($doClean && !defined('_ZINSTALLVER')) {
                    self::cleanValue($value);
                }
            }

            return $value;
        }

        return $default;
    }

    /**
     * Clean an array acquired from input. This method is safe to use for recursive arrays
     * and cleans the array in place as well as returning it.
     *
     * @param array     The array to clean up
     *
     * @return The the altered/cleaned data array.
     */
    public static function cleanArray(&$array)
    {
        if (!is_array($array)) {
            return pn_exit(__f('Non-array passed to %s.', 'FormUtil::cleanArray'));
        }

        $ak = array_keys($array);
        $kc = count($ak);

        for ($i = 0; $i < $kc; $i++) {
            $key = $ak[$i];
            if (is_array($array[$key])) {
                self::cleanArray($array[$key]);
            } else {
                self::cleanValue($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Clean an individual data element in place; cleans the array in place
     * as well as returning it.
     *
     * @param value     The value to clean
     *
     * @return A reference to the original (altered/cleaned) data array
     */
    public static function cleanValue(&$value)
    {
        static $isAdmin = null;
        if ($isAdmin === null)
            $isAdmin = SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN);

        if (!$value) {
            return $value;
        }

        if (get_magic_quotes_gpc()) {
            pnStripslashes($value);
        }

        if (!$isAdmin) {
            static $replace = array();
            static $search = array('|</?\s*SCRIPT.*?>|si', '|</?\s*FRAME.*?>|si', '|</?\s*OBJECT.*?>|si', '|</?\s*META.*?>|si', '|</?\s*APPLET.*?>|si', '|</?\s*LINK.*?>|si', '|</?\s*IFRAME.*?>|si', '|STYLE\s*=\s*"[^"]*"|si');

            $value = preg_replace($search, $replace, $value);
        }

        $value = trim($value);
        return $value;
    }

    /**
     * Return a boolean indicating whether the specified field is required
     *
     * @param validationInfo   The plain (non-structured) validation array
     * @param field            The fieldname
     *
     * @return A boolean indicating whether or not the specified field is required
     */
    public static function isRequiredField($validationInfo, $field)
    {
        if (!$validationInfo) {
            return pn_exit(__f('Empty %1$s passed to %2$s.', array('validationInfo', 'FormUtil::isRequiredField')));
        }

        if (!$field) {
            return pn_exit(__f('Empty %1$s passed to %2$s.', array('fieldname', 'FormUtil::isRequiredField')));
        }

        $rec = isset($validationInfo[$field]) ? $validationInfo[$field] : null;

        if (!$rec) {
            return false;
        }

        return $rec[1];
    }

    /**
     * Get the required field marker (or nothing) for the specified field
     *
     * @param validationInfo   The plain (non-structured) validation array
     * @param field            The fieldname
     *
     * @return The required field marker or an empty string
     */
    public static function getRequiredFieldMarker($validationInfo, $field)
    {
        if (self::isRequiredField($validationInfo, $field)) {
            return _REQUIRED_MARKER;
        }

        return _MARKER_NONE;
    }

    /**
     * Clear the validation error array
     *
     * @param objectType       The (string) object type
     *
     * @return nothing
     */
    public static function clearValidationErrors($objectType = null)
    {
        if ($objectType) {
            if (isset($_SESSION['validationErrors'][$objectType])) {
                unset($_SESSION['validationErrors'][$objectType]);
            }
        } else {
            if (isset($_SESSION['validationErrors'])) {
                unset($_SESSION['validationErrors']);
            }
        }
    }

    /**
     * Clear the objects which failed validation
     *
     * @param objectType       The (string) object type
     *
     * @return nothing
     */
    public static function clearValidationFailedObjects($objectType = null)
    {
        if ($objectType) {
            if (isset($_SESSION['validationFailedObjects'][$objectType])) {
                unset($_SESSION['validationFailedObjects'][$objectType]);
            }
        } else {
            if (isset($_SESSION['validationFailedObjects'])) {
                unset($_SESSION['validationFailedObjects']);
            }
        }
    }

    /**
     * Get the validation errors
     *
     * @return The validation error array or null
     */
    public static function getValidationErrors()
    {
        static $ve = null;
        if (!$ve) {
            if (isset($_SESSION['validationErrors']) && is_array($_SESSION['validationErrors'])) {
                $ve = $_SESSION['validationErrors'];
                unset($_SESSION['validationErrors']);
            }
        }

        return $ve;
    }

    /**
     * Return the objects which failed validation
     *
     * @return The validation error array or null
     */
    public static function getFailedValidationObjects($objectType = null)
    {
        static $objects = null;
        if (!$objects) {
            if (isset($_SESSION['validationFailedObjects']) && is_array($_SESSION['validationFailedObjects'])) {
                if ($objectType && isset($_SESSION['validationFailedObjects'][$objectType])) {
                    $objects = $_SESSION['validationFailedObjects'][$objectType];
                    unset($_SESSION['validationFailedObjects'][$objectType]);
                } else {
                    $objects = $_SESSION['validationFailedObjects'];
                    unset($_SESSION['validationFailedObjects']);
                }
            }
        }

        return $objects;
    }

    /**
     * Return a boolean indicating whether or not the specified field failed validation
     *
     * @param objectType       The (string) object type
     * @param field            The fieldname
     *
     * @return A boolean indicating whether or not the specified field failed validation
     */
    public static function hasValidationErrors($objectType, $field = null)
    {
        if (!$objectType) {
            return pn_exit(__f('Empty %1$s passed to %2$s.', array('objectType', 'FormUtil::hasValidationErrors')));
        }

        if (!$field) {
            return pn_exit(__f('Empty %1$s passed to %2$s.', array('field', 'FormUtil::hasValidationErrors')));
        }

        $ve = self::getValidationErrors();
        if (isset($ve[$objectType][$field])) {
            return (boolean)$ve[$objectType][$field];
        } else {
            return false;
        }
    }

    /**
     * Get the required field marker (or nothing) for the specified field
     *
     * @param objectType       The (string) object type
     * @param field            The fieldname
     *
     * @return The validation error marker or an empty string
     */
    public static function getValidationFieldMarker($objectType, $field)
    {
        if (self::hasValidationErrors($objectType, $field)) {
            return _VALIDATION_MARKER;
        }

        return _MARKER_NONE;
    }

    /**
     * Get the validation error for the specified field
     *
     * @param objectType       The (string) object type
     * @param field            The fieldname to get the error for
     *
     * @return The validation error or an empty string
     */
    public static function getValidationError($objectType, $field)
    {
        if (!self::hasValidationErrors($objectType, $field)) {
            return '';
        }

        $ve = self::getValidationErrors();
        $error = $ve[$objectType][$field];
        if ($error) {
            $error = '&nbsp;' . $error;
        }

        return $error;
    }

    /**
     * Get the appropriate field marker
     *
     * @param objectType       The (string) object type
     * @param validationInfo   The plain (non-structured) validation array
     * @param field            The fieldname
     *
     * @return The a marker string or an 'nbsp';
     */
    public static function getFieldMarker($objectType, $validationInfo, $field)
    {
        if (self::hasValidationErrors($objectType, $field)) {
            return _VALIDATION_MARKER;
        } else if (self::isRequiredField($validationInfo, $field)) {
            return _REQUIRED_MARKER;
        }

        return _MARKER_NONE;
    }

    /**
     * Return a newly created pnFormRender instance with the given name
     *
     * @deprecated
     * @see FormUtil::newForm()
     * @return The newly created pnFormRender instance.
     */
    public static function newPNForm($name)
    {
        return self::Form($name);
    }
    
/**
     * Return a newly created pormRender instance with the given name
     *
     * @return The newly created FormRender instance.
     */
    public static function newForm($name)
    {
        return new Form($name);
    }
}

