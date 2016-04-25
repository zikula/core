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
 * Validates a field against a minimum value, ensuring that the field value is greater than or equal to this minimum.
 */
class IntegerNumericMinimumValue extends AbstractValidator
{
    /**
     * The minimum valid value for the data in the field.
     *
     * @var integer
     */
    protected $value;

    /**
     * Constructs a new validator, initializing the minimum valid value.
     *
     * @param \Zikula_ServiceManager $serviceManager The current service manager instance.
     * @param integer                $value          The minimum valid value for the field data.
     * @param string                 $errorMessage   The error message to return if the field data is not valid.
     *
     * @throws \InvalidArgumentException If the minimum value specified is not an integer.
     */
    public function __construct(\Zikula_ServiceManager $serviceManager, $value, $errorMessage = null)
    {
        parent::__construct($serviceManager, $errorMessage);

        if (!isset($value) || !is_int($value) || ($value < 0)) {
            throw new \InvalidArgumentException($this->__('An invalid integer value was received.'));
        }

        $this->value = $value;
    }

    /**
     * Validates the specified data against the minimum valid value.
     *
     * @param mixed $data The data to validate.
     *
     * @return boolean True if the data is an integer or numeric that is greater than or equal to the minimum valid value; otherwise false.
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
                $valid = ($data >= $this->value);
            }
        }

        return $valid;
    }
}
