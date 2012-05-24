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
 * Ensures that the field contains data compatible with a boolean type.
 */
class Users_Controller_FormData_Validator_BooleanType extends Users_Controller_FormData_Validator_AbstractValidator
{
    /**
     * Validate the data for compatibility with the boolean type.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is compatible with the boolean type; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_bool($data)) {
                $valid = true;
            } elseif (is_int($data)) {
                $valid = (($data === 0) || ($data === 1));
            } elseif (is_numeric($data)) {
                // To support radio buttons
                $valid = (($data === '0') || ($data === '1'));
            } elseif (is_string($data)) {
                // To support unchecked check boxes
                $valid = ($data == '');
            }
        }

        return $valid;
    }
}
