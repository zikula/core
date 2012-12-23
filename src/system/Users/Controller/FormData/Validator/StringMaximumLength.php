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
 * Validates a field's data, ensuring that its string value has a length that is less than or equal to a maximum length.
 */
class Users_Controller_FormData_Validator_StringMaximumLength extends Users_Controller_FormData_Validator_AbstractValidator
{
    /**
     * The maximum valid string length.
     *
     * @var integer
     */
    protected $length;

    /**
     * Constructs a new validator, initializing the maximum valid string length value.
     *
     * @param Zikula_ServiceManager $serviceManager The current service manager instance.
     * @param integer               $length         The maximum valid length for the string value.
     * @param string                $errorMessage   The error message to return if the string data exceeds the maximum length.
     *
     * @throws InvalidArgumentException Thrown if the maximum string length value is not an integer or is less than zero.
     */
    public function __construct(Zikula_ServiceManager $serviceManager, $length, $errorMessage = null)
    {
        parent::__construct($serviceManager, $errorMessage);

        if (!isset($length) || !is_int($length) || ($length < 0)) {
            throw new InvalidArgumentException($this->__('An invalid string length was received.'));
        }

        $this->length = $length;
    }

    /**
     * Validate the specified data against the maximum valid string length.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is a string value whose length is less than or equal to the maximum allowed length.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_string($data)) {
                $valid = (mb_strlen($data) <= $this->length);
            }
        }

        return $valid;
    }
}
