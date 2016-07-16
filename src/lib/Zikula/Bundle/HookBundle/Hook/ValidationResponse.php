<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Hook;

/**
 * Validation object for hooks.
 */
class ValidationResponse
{
    /**
     * Object key.
     *
     * @var string
     */
    protected $key;

    /**
     * The object of validation.
     *
     * @var array|object
     */
    protected $object;

    /**
     * Errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor.
     *
     * @param string       $key    Key
     * @param array|object $object Object to be validated
     */
    public function __construct($key, $object)
    {
        $this->key = $key;
        $this->object = $object;
    }

    /**
     * Get key property.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get object property.
     *
     * @return array|object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get errors property.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add error.
     *
     * @param string $field   Field/property name of validation object
     * @param string $message Error message
     *
     * @return void
     */
    public function addError($field, $message)
    {
        $this->errors[$field] = $message;
    }

    /**
     * Has errors.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return (bool)$this->errors;
    }
}
