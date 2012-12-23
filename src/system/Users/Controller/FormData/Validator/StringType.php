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
 * Validates a field's value, ensuring that is is a string value.
 */
class Users_Controller_FormData_Validator_StringType extends Users_Controller_FormData_Validator_AbstractValidator
{
    /**
     * Validates the specified data, ensuring that the data is a string.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is a string; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_string($data)) {
                $valid = true;
            }
        }

        return $valid;
    }
}
