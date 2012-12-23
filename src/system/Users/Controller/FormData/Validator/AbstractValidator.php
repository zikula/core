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
 * Validates a field's data against specified criteria.
 */
abstract class Users_Controller_FormData_Validator_AbstractValidator extends Zikula_AbstractBase
{
    /**
     * An error message that describes why the data is not valid.
     *
     * @var string
     */
    protected $errorMessage;

    /**
     * Constructs a new validator instance, initializing the error message.
     *
     * @param Zikula_ServiceManager $serviceManager The current service manager instance.
     * @param string                $errorMessage   The error message to report if the field's data does not validate.
     *
     * @throws InvalidArgumentException Thrown if the error message is not a string or is empty.
     */
    public function __construct(Zikula_ServiceManager $serviceManager, $errorMessage = null)
    {
        parent::__construct($serviceManager);

        if (isset($errorMessage)) {
            if (is_string($errorMessage) && !empty($errorMessage)) {
                $this->errorMessage = $errorMessage;
            } else {
                throw new InvalidArgumentException($this->__('An invalid error message was supplied.'));
            }
        } else {
            $this->errorMessage($this->__('The value supplied was not valid.'));
        }
    }

    /**
     * Validates the specified data against the validator's criteria.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the field's data meets the specified criteria; otherwise false.
     */
    abstract public function isValid($data);

    /**
     * Retrieve the validator's error message.
     *
     * @return string The error message.
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
