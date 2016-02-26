<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Provider
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
    protected $errors = array();

    /**
     * Constructor.
     *
     * @param string       $key    Key.
     * @param array|object $object Object to be validated.
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
     * @param string $field   Field/property name of validation object.
     * @param string $message Error message.
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
