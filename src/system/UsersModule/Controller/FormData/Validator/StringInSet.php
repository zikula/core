<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller\FormData\Validator;

/**
 * Validates a field's data against a list of valid string values.
 */
class StringInSet extends AbstractValidator
{
    /**
     * List of valid strings.
     *
     * @var array
     */
    protected $validStrings;

    /**
     * Creates a new validator, initializing the set of valid string values.
     *
     * @param \Zikula_ServiceManager $serviceManager The current service manager instance.
     * @param array                  $validStrings   An array containing valid string values.
     * @param string                 $errorMessage   The error message to return if the data is not valid.
     *
     * @throws \InvalidArgumentException Thrown if the list of valid string values is not valid, or if it contains an invalid value.
     */
    public function __construct(\Zikula_ServiceManager $serviceManager, array $validStrings, $errorMessage = null)
    {
        parent::__construct($serviceManager, $errorMessage);

        if (empty($validStrings)) {
            throw new \InvalidArgumentException($this->__('An invalid list of valid strings was received.'));
        }

        foreach ($validStrings as $validString) {
            if (isset($validString) && is_string($validString)) {
                $this->validStrings[$validString] = $validString;
            } else {
                throw new \InvalidArgumentException($this->__('An invalid value was received in the list of valid strings.'));
            }
        }
    }

    /**
     * Validates the specified data against the list of allowable string values.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is a string value that appears in the list of allowable string values; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_string($data)) {
                if (isset($this->validStrings[$data])) {
                    $valid = true;
                }
            }
        }

        return $valid;
    }
}
