<?php
/**
 * Copyright 2011 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Validates that a field's data is compatible with an integer numeric type.
 */
class Users_Controller_FormData_Validator_IntegerNumericType extends Users_Controller_FormData_Validator_AbstractValidator
{
    /**
     * Validates that the specified data is an integer or an integer numeric string.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is an integer or is an integer numeric string; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_int($data)) {
                $valid = true;
            } elseif (is_numeric($data)) {
                $valid = ((string)((int)$data) === $data);
            }
        }

        return $valid;
    }
}
