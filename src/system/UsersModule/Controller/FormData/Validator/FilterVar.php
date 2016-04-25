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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Validates a field's value, by filtering with a specified filter.
 */
class FilterVar extends AbstractValidator
{
    /**
     * The ID of the filter to apply.
     *
     * @link http://www.php.net/manual/en/filter.filters.php
     * @var const
     */
    protected $filter;

    /**
     * Associative array of options or bitwise disjunction of flags. If filter accepts options,
     * flags can be provided in "flags" field of array. For the "callback" filter, callable type
     * should be passed. The callback must accept one argument, the value to be filtered,
     * and return the value after filtering/sanitizing it.
     *
     * @link http://www.php.net/manual/en/filter.filters.php
     * @var const|array
     */
    protected $options;

    /**
     * Constructs the validator, initializing the filter.
     *
     * @param ContainerInterface $serviceManager The current service manager instance.
     * @param const $filter The ID of the filter to apply.
     * @param const|array $options Associative array of options or bitwise disjunction of flags. If filter accepts options, flags can be provided in "flags" field of array. For the "callback" filter, callable type should be passed. The callback must accept one argument, the value to be filtered, and return the value after filtering/sanitizing it.
     * @param string $errorMessage The error message to return if the data does not match the expression.
     *
     * @throws \InvalidArgumentException Thrown if the filter is not valid.
     */
    public function __construct(ContainerInterface $serviceManager, $filter = FILTER_DEFAULT, $options = null, $allow_empty_value = false, $errorMessage = null)
    {
        parent::__construct($serviceManager, $errorMessage);

        if ((!isset($filter)) || (!is_int($filter)) || (empty($filter))) {
            throw new \InvalidArgumentException($this->__('Error! An invalid filter was received.'));
        }

        $this->filter = $filter;
        $this->options = $options;
        $this->allow_empty_value = (bool)$allow_empty_value;
    }

    /**
     * Validates the specified data, ensuring that it is a string value that matches the filter.
     *
     * @param mixed $data The data to be validated.
     * @return boolean TRUE if the data is a string value that matches the filter. Otherwise, FALSE.
     */
    public function isValid($data)
    {
        if (($this->allow_empty_value) && (empty($data))) {
            $valid = true;
        } else {
            $valid = false;

            if (isset($data)) {
                if (is_string($data)) {
                    if (!is_null($this->options)) {
                        if (filter_var($data, $this->filter, $this->options)) {
                            $valid = true;
                        }
                    } else {
                        if (filter_var($data, $this->filter)) {
                            $valid = true;
                        }
                    }
                }
            }
        }

        return $valid;
    }
}
