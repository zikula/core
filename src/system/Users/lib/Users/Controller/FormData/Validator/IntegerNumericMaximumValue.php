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
 * Validates a field against a maximum value, ensuring that the field value is less than or equal to this maximum.
 */
class Users_Controller_FormData_Validator_IntegerNumericMaximumValue extends Users_Controller_FormData_Validator_AbstractValidator
{
    /**
     * The maximum valid value for the data in the field.
     *
     * @var integer
     */
    protected $value;

    /**
     * Constructs a new validator, initializing the maximum valid value.
     *
     * @param Zikula_ServiceManager $serviceManager The current service manager instance.
     * @param integer               $value          The maximum valid value for the field data.
     * @param string                $errorMessage   The error message to return if the field data is not valid.
     *
     * @throws InvalidArgumentException If the maximum value specified is not an integer.
     */
    public function __construct(Zikula_ServiceManager $serviceManager, $value, $errorMessage = null)
    {
        parent::__construct($serviceManager, $errorMessage);

        if (!isset($value) || !is_int($value) || ($value < 0)) {
            throw new InvalidArgumentException($this->__('An invalid integer value was received.'));
        }

        $this->value = $value;
    }

    /**
     * Validates the specified data against the maximum valid value.
     *
     * @param mixed $data The data to validate.
     *
     * @return boolean True if the data is an integer or numeric that is less than or equal to the maximum valid value; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (!is_int($data)) {
                if (is_numeric($data) && ((string)((int)$data) == $data)) {
                    $data = (int)$data;
                }
            }

            if (is_int($data)) {
                $valid = ($data <= $this->value);
            }
        }

        return $valid;
    }
}
