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
 * Validates a field to ensure that it contains a string whose letters are all lowercase (or are non-letter characters).
 */
class Users_Controller_FormData_Validator_StringLowerCase extends Users_Controller_FormData_Validator_AbstractValidator
{
    /**
     * Validates the specified data, looking for letters that are not lower case.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is a string value whose letter characters (if any) are all lowercase letters; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_string($data)) {
                $lowercaseData = mb_strtolower($data);
                $valid = ($data == $lowercaseData);
            }
        }

        return $valid;
    }
}
