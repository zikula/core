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
 * Validates a field's value, ensuring that its string value matches a PCRE regular expression.
 */
class StringRegularExpression extends AbstractValidator
{
    /**
     * The full PCRE-compatible regular expression against which to validate the data.
     *
     * @var string
     */
    protected $regularExpression;

    /**
     * Constructs the validator, initializing the regular expression.
     *
     * @param \Zikula_ServiceManager $serviceManager    The current service manager instance.
     * @param string                 $regularExpression The PCRE regular expression against which to validate the data.
     * @param string                 $errorMessage      The error message to return if the data does not match the expression.
     *
     * @throws \InvalidArgumentException Thrown if the regular expression is not valid.
     */
    public function __construct(\Zikula_ServiceManager $serviceManager, $regularExpression, $errorMessage = null)
    {
        parent::__construct($serviceManager, $errorMessage);

        if (!isset($regularExpression) || !is_string($regularExpression) || empty($regularExpression)) {
            throw new \InvalidArgumentException($this->__('An invalid regular expression was received.'));
        }

        $this->regularExpression = $regularExpression;
    }

    /**
     * Validates the specified data, ensuring that it is a string value that matches the regular expression pattern.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is a string value that matches the regular expression pattern; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_string($data)) {
                if (preg_match($this->regularExpression, $data)) {
                    $valid = true;
                }
            }
        }

        return $valid;
    }
}
