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
 * FormUtil.
 */
class FormUtil
{
    /**
     * Return the requested key from input in a safe way.
     *
     * This function is safe to use for recursive arrays and either
     * returns a non-empty string or the (optional) default.
     *
     * This method is based on FormUtil::getPassedValue but array-safe.
     *
     * @param string $key        The field to return.
     * @param mixed  $default    The value to return if the requested field is not found (optional) (default=false).
     * @param string $source     The source field to get a parameter from.
     * @param string $filter     The filter directive to apply.
     * @param array  $args       The filter processing args to apply.
     * @param string $objectType The object access path we're getting; used to assign validation errors .
     *
     * @deprecated since 1.3.0, use request object instead.
     *
     * @return mixed The requested input key or the specified default.
     */
    public static function getPassedValue($key, $default = null, $source = null, $filter = null, array $args = array(), $objectType=null)
    {
        if (!$key) {
            return z_exit(__f('Empty %1$s passed to %2$s.', array('key', 'FormUtil::getPassedValue')));
        }

        $source = strtoupper($source);
        if (!$filter) {
            $filter = FILTER_DEFAULT;
        }

        $args = array();
        $failed = null;

        switch (true) {
            case (isset($_REQUEST[$key]) && !isset($_FILES[$key]) && (!$source || $source == 'R' || $source == 'REQUEST')):
                if (is_array($_REQUEST[$key])) {
                    $args['flags'] = FILTER_REQUIRE_ARRAY;
                }
                $value = filter_var($_REQUEST[$key], $filter, $args);
                $failed = ($value === false) ? $_REQUEST : null;
                break;
            case isset($_GET[$key]) && (!$source || $source == 'G' || $source == 'GET'):
                if (is_array($_GET[$key])) {
                    $args['flags'] = FILTER_REQUIRE_ARRAY;
                }
                $value = filter_var($_GET[$key], $filter, $args);
                $failed = ($value === false) ? $_GET : null;
                break;
            case isset($_POST[$key]) && (!$source || $source == 'P' || $source == 'POST'):
                if (is_array($_POST[$key])) {
                    $args['flags'] = FILTER_REQUIRE_ARRAY;
                }
                $value = filter_var($_POST[$key], $filter, $args);
                $failed = ($value === false) ? $_POST : null;
                break;
            case isset($_COOKIE[$key]) && (!$source || $source == 'C' || $source == 'COOKIE'):
                if (is_array($_COOKIE[$key])) {
                    $args['flags'] = FILTER_REQUIRE_ARRAY;
                }
                $value = filter_var($_COOKIE[$key], $filter, $args);
                $failed = ($value === false) ? $_COOKIE : null;
                break;
            case isset($_FILES[$key]) && ($source == 'F' || $source == 'FILES'):
                if (is_array($_FILES[$key])) {
                    $args['flags'] = FILTER_REQUIRE_ARRAY;
                }
                $value = $_FILES[$key];
                $failed = ($value === false) ? $_COOKIE : null;
                break;
            case (isset($_GET[$key]) || isset($_POST[$key])) && ($source == 'GP' || $source == 'GETPOST'):
                if (isset($_GET[$key])) {
                    if (is_array($_GET[$key])) {
                        $args['flags'] = FILTER_REQUIRE_ARRAY;
                    }
                    $value = filter_var($_GET[$key], $filter, $args);
                    $failed = ($value === false) ? $_GET : null;
                }
                if (isset($_POST[$key])) {
                    if (is_array($_POST[$key])) {
                        $args['flags'] = FILTER_REQUIRE_ARRAY;
                    }
                    $value = filter_var($_POST[$key], $filter, $args);
                    $failed = ($value === false) ? $_POST : null;
                }
                break;
            default:
                if ($source) {
                    static $valid = array('R', 'REQUEST', 'G', 'GET', 'P', 'POST', 'C', 'COOKIE', 'F', 'FILES', 'GP', 'GETPOST');
                    if (!in_array($source, $valid)) {
                        z_exit(__f('Invalid input source [%s] received.', DataUtil::formatForDisplay($source)));
                        return $default;
                    }
                }
                $value = $default;
        }

        if ($failed && $objectType) {
            //SessionUtil::setVar ($key, $failed[$key], "/validationErrors/$objectType");
            SessionUtil::setVar($objectType, $failed[$key], '/validationFailedObjects');
        }

        return $value;
    }

    /**
     * Return a boolean indicating whether the specified field is required.
     *
     * @param array  $validationInfo The plain (non-structured) validation array.
     * @param string $field          The fieldname.
     *
     * @return boolean A boolean indicating whether or not the specified field is required.
     */
    public static function isRequiredField($validationInfo, $field)
    {
        if (!$validationInfo) {
            return z_exit(__f('Empty %1$s passed to %2$s.', array('validationInfo', 'FormUtil::isRequiredField')));
        }

        if (!$field) {
            return z_exit(__f('Empty %1$s passed to %2$s.', array('fieldname', 'FormUtil::isRequiredField')));
        }

        $rec = isset($validationInfo[$field]) ? $validationInfo[$field] : null;

        if (!$rec) {
            return false;
        }

        return $rec[1];
    }

    /**
     * Get the required field marker (or nothing) for the specified field.
     *
     * @param array  $validationInfo The plain (non-structured) validation array.
     * @param string $field          The fieldname.
     *
     * @return string The required field marker or an empty string.
     */
    public static function getRequiredFieldMarker($validationInfo, $field)
    {
        if (self::isRequiredField($validationInfo, $field)) {
            return HtmlUtil::REQUIRED_MARKER;
        }

        return HtmlUtil::MARKER_NONE;
    }

    /**
     * Clear the validation error array.
     *
     * @param string $objectType The (string) object type.
     *
     * @return void
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
     * Clear the objects which failed validation.
     *
     * @param string $objectType The (string) object type.
     *
     * @return void
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
     * Get the validation errors.
     *
     * @return array The validation error array or null.
     */
    public static function getValidationErrors()
    {
        static $ve = null;

        if ($ve === null) {
            if (isset($_SESSION['validationErrors']) && is_array($_SESSION['validationErrors'])) {
                $ve = $_SESSION['validationErrors'];
                unset($_SESSION['validationErrors']);
            } else {
                $ve = array();
            }
        }
        return $ve;
    }

    /**
     * Return the objects which failed validation.
     *
     * @param string $objectType The object type.
     *
     * @return array The validation error array or null.
     */
    public static function getFailedValidationObjects($objectType = null)
    {
        static $objects = array();
        if (!isset($objects[$objectType])) {
            if (isset($_SESSION['validationFailedObjects']) && is_array($_SESSION['validationFailedObjects'])) {
                if ($objectType && isset($_SESSION['validationFailedObjects'][$objectType])) {
                    $objects[$objectType] = $_SESSION['validationFailedObjects'][$objectType];
                    unset($_SESSION['validationFailedObjects'][$objectType]);
                } else {
                    $objects = $_SESSION['validationFailedObjects'];
                    unset($_SESSION['validationFailedObjects']);
                }
            }
        }

        if ($objectType && isset($objects[$objectType])) {
            return $objects[$objectType];
        }

        return $objects;
    }

    /**
     * Return a boolean indicating whether or not the specified field failed validation.
     *
     * @param string $objectType The (string) object type.
     * @param string $field      The fieldname.
     *
     * @return boolean A boolean indicating whether or not the specified field failed validation.
     */
    public static function hasValidationErrors($objectType, $field = null)
    {
        if (!$objectType) {
            return z_exit(__f('Empty %1$s passed to %2$s.', array('objectType', 'FormUtil::hasValidationErrors')));
        }

        if (!$field) {
            return z_exit(__f('Empty %1$s passed to %2$s.', array('field', 'FormUtil::hasValidationErrors')));
        }

        $ve = self::getValidationErrors();
        if (isset($ve[$objectType][$field])) {
            return (boolean)$ve[$objectType][$field];
        } else {
            return false;
        }
    }

    /**
     * Get the required field marker (or nothing) for the specified field.
     *
     * @param string $objectType The (string) object type.
     * @param string $field      The fieldname.
     *
     * @return string The validation error marker or an empty string.
     */
    public static function getValidationFieldMarker($objectType, $field)
    {
        if (self::hasValidationErrors($objectType, $field)) {
            return HtmlUtil::VALIDATION_MARKER;
        }

        return HtmlUtil::MARKER_NONE;
    }

    /**
     * Get the validation error for the specified field.
     *
     * @param string $objectType The (string) object type.
     * @param string $field      The fieldname to get the error for.
     *
     * @return string The validation error or an empty string.
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
     * Get the appropriate field marker.
     *
     * @param string $objectType     The (string) object type.
     * @param array  $validationInfo The plain (non-structured) validation array.
     * @param string $field          The fieldname.
     *
     * @return string The a marker string or an '&nbsp;'.
     */
    public static function getFieldMarker($objectType, $validationInfo, $field)
    {
        if (self::hasValidationErrors($objectType, $field)) {
            return HtmlUtil::VALIDATION_MARKER;
        } else if (self::isRequiredField($validationInfo, $field)) {
            return HtmlUtil::REQUIRED_MARKER;
        }

        return HtmlUtil::MARKER_NONE;
    }

    /**
     * Return a newly created pnFormRender instance with the given name
     *
     * @param string $name Module name.
     *
     * @deprecated
     * @see    FormUtil::newForm()
     * @return pnFormRender The newly created Form_Render instance.
     */
    public static function newPNForm($name)
    {
        // This MUST call new pnForm and cannot be chained to self::newForm()
        return new pnFormRender($name);
    }

    /**
     * Return a newly created pormRender instance with the given name.
     *
     * @param string                    $name       Module name.
     * @param Zikula_AbstractController $controller Controller.
     * @param string                    $className  Optionally instanciate a child of Zikula_Form_View.
     *
     * @return Form_View The newly created Form_View instance.
     */
    public static function newForm($name, Zikula_AbstractController $controller = null, $className=null)
    {
        $serviceManager = $controller->getServiceManager();
        if ($className && !class_exists($className)) {
            throw new RuntimeException(__f('%s does not exist', $className));
        }

        $form = $className ? new $className($serviceManager, $name) : new Zikula_Form_View($serviceManager, $name);
        if ($className && !$form instanceof Zikula_Form_View) {
            throw new RuntimeException(__f('%s is not an instance of Zikula_Form_View', $className));
        }

        $form->setEntityManager($controller->getEntityManager());

        if ($controller) {
            $form->setController($controller);
            $form->assign('controller', $controller);
        } else {
            LogUtil::log(__('FormUtil::newForm should also include the Zikula_AbstractController as the second argument to enable hooks to work.'), Zikula_AbstractErrorHandler::NOTICE);
        }

        return $form;
    }

}
