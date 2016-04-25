<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ValidationUtil
 *
 * @deprecated
 */
class ValidationUtil
{
    /**
     * Validate a specific field using the supplied control parameters
     *
     * @param string   $objectType The string object type.
     * @param array    $object     The object to validate.
     * @param string   $field      The field to validate.
     * @param boolean  $required   Whether or not the field is required.
     * @param string   $cmp_op     The compare operation to perform.
     * @param string   $cmp_value  The value to compare the supplied field value to. If the value starts with a ':', the argument is used as an object access key.
     * @param string   $err_msg    The error message to use if the validation fails.
     * @param callable $callback   Callback, any PHP callable.
     *
     * @return boolean A true/false value indicating whether the field validation passed or failed.
     */
    public static function validateField($objectType, $object, $field, $required, $cmp_op, $cmp_value, $err_msg, $callback = null)
    {
        if (!is_array($object)) {
            throw new \Exception(__f('%1s: %2s is not an array.', array('ValidationUtil::validateField', 'object')));
        }

        if (!$field) {
            throw new \Exception(__f('%1s: empty %2s supplied.', array('ValidationUtil::validateField', 'field')));
        }

        if (!$err_msg) {
            throw new \Exception(__f('%1s: empty %2s supplied.', array('ValidationUtil::validateField', 'error message')));
        }

        $rc = true;

        // if this field already has an error, don't perform further checks
        if (isset($_SESSION['validationErrors'][$objectType][$field])) {
            return false;
        }

        if ($required) {
            if (!isset($object[$field]) || $object[$field] === '') {
                $rc = false;
            }
        }

        if ($rc && $object[$field]) {
            $postval = $object[$field];
            $testval = $cmp_value;
            if (substr($testval, 0, 1) == ':') {
                // denotes an object access key
                $v2 = substr($testval, 1);
                $testval = $object[$v2];
            }

            if ($callback) {
                $postval = call_user_func($callback, $postval);
            }

            switch ($cmp_op) {
                case 'eq ': $rc = ($postval === $testval);
                    break;
                case 'neq': $rc = ($postval != $testval);
                    break;
                case 'gt': $rc = ($postval !== '' && is_numeric($postval) && $postval > $testval);
                    break;
                case 'gte': $rc = ($postval !== '' && is_numeric($postval) && $postval >= $testval);
                    break;
                case 'lt': $rc = ($postval !== '' && is_numeric($postval) && $postval < $testval);
                    break;
                case 'lte': $rc = ($postval !== '' && is_numeric($postval) && $postval <= $testval);
                    break;
                case 'in': $rc = ($postval !== '' && is_array($testval) && in_array($postval, $testval));
                    break;
                case 'notin': $rc = ($postval !== '' && is_array($testval) && !in_array($postval, $testval));
                    break;
                case 'regexp': $rc = ($postval !== '' && preg_match($testval, $postval));
                    break;
                case 'url': $rc = System::varValidate($postval, 'url');
                    break;
                case 'email': $rc = System::varValidate($postval, 'email');
                    break;
                case 'noop':
                case '': if (!$required) {
     throw new \Exception(__f('%1$s: invalid cmp_op [%2$s] supplied for non-required field [%3$s].', array('ValidationUtil::validateField', $cmp_op, $field)));
 }
                    $rc = true;
                    break;
                default: throw new \Exception(__f('%1$s: invalid cmp_op [%2$s] supplied for field [%3$s].', array('ValidationUtil::validateField', $cmp_op, $field)));
            }
        }

        if ($rc === false) {
            if (!isset($_SESSION['validationErrors'][$objectType][$field])) {
                $_SESSION['validationErrors'][$objectType][$field] = $err_msg;
            }
        }

        return $rc;
    }

    /**
     * Validate a specific field using the supplied control parameters.
     *
     * The expected structure for the validation array is as follows:
     * $validationControl[] = array ('field'         =>  $fieldname,
     *                               'required'      =>  true/false,
     *                               'cmp_op'        =>  eq/neq/lt/lte/gt/gte/url/email/valuearray/noop,
     *                               'cmp_value'     =>  $value
     *                               'err_msg'       =>  $errorMessage
     *                               'callback'      =>  $callback - any valid PHP callable);
     *
     * The noop value for the cmp_op field is only valid if the field is not required.
     *
     * @param array $object            The object to validate.
     * @param array $validationControl The structured validation control array.
     *
     * @return boolean A true/false value indicating whether the field validation passed or failed.
     */
    public static function validateFieldByArray($object, $validationControl)
    {
        $objType = $validationControl['objectType'];
        $field = $validationControl['field'];
        $req = $validationControl['required'];
        $cmp_op = $validationControl['cmp_op'];
        $cmp_val = $validationControl['cmp_value'];
        $err_msg = $validationControl['err_msg'];
        $callback = $validationControl['callback'];

        return self::validateField($objType, $object, $field, $req, $cmp_op, $cmp_val, $err_msg, $callback);
    }

    /**
     * Validate a specific field using the supplied control parameters.
     *
     * The expected structure for the validation array is as follows:
     * $validationControls[] = array ('field'         =>  $fieldname,
     *                                'required'      =>  true/false,
     *                                'cmp_op'        =>  eq/neq/lt/lte/gt/gte/noop,
     *                                'cmp_value'     =>  $value
     *                                'err_msg'       =>  $errorMessage
     *                                'callback'      =>  $callback - any valid PHP callable);
     *
     * The noop value for the cmp_op field is only valid if the field is not required.
     *
     * @param string $objectType         The string object type.
     * @param array  $object             The object to validate.
     * @param array  $validationControls The array of structured validation control arrays.
     *
     * @return boolean A true/false value indicating whether the object validation passed or resulted in errors.
     */
    public static function validateObject($objectType, $object, $validationControls)
    {
        $rc = true;

        foreach ($validationControls as $vc) {
            $t = self::validateFieldByArray($object, $vc);
            if ($t === false) {
                $rc = false;
            }
        }

        if (!$rc) {
            $_SESSION['validationFailedObjects'][$objectType] = $object;
        }

        return $rc;
    }

    /**
     * Validate a specific field using the supplied plain validation array.
     *
     * This function converts the plain validation array into a structured
     * validation array and then calls ValidationUtil::validateObject().
     *
     * The expected structure for the validation array is as follows:
     * $validationArray[] = array ($fieldname, true/false, eq/neq/lt/lte/gt/gte/noop, $value, $errorMessage);
     *
     * The noop value for the cmp_op field is only valid if the field is not required.
     *
     * @param string $objectType      The string object type.
     * @param array  $object          The object to validate.
     * @param array  $validationArray The plain (numerically indexed) validation array.
     *
     * @return boolean A true/false value indicating whether the object validation passed or failed.
     */
    public static function validateObjectPlain($objectType, $object, $validationArray)
    {
        $validationControls = array();

        $vc = array();
        foreach ($validationArray as $va) {
            $size = count($va);
            if ($size < 5) {
                throw new \Exception(__f('%1$s: invalid validationArray supplied: expected 5 fields but found %2$s.', array('ValidationUtil::validateObjectPlain', $size)));
            }

            $vc['objectType'] = $objectType;
            $vc['field'] = $va[0];
            $vc['required'] = $va[1];
            $vc['cmp_op'] = $va[2];
            $vc['cmp_value'] = $va[3];
            $vc['err_msg'] = $va[4];
            $vc['callback'] = $va[5];

            $validationControls[] = $vc;
        }

        return self::validateObject($objectType, $object, $validationControls);
    }
}
