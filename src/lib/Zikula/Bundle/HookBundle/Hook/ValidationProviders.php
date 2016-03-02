<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Collection
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\HookBundle\Hook;

use Zikula\Common\Collection\Container;

/**
 * Hook validation collection
 */
class ValidationProviders extends Container
{
    /**
     * Constructor.
     *
     * @param string       $name       The name of the collection.
     * @param \ArrayObject $collection The collection (optional).
     */
    public function __construct($name = 'validation', \ArrayObject $collection = null)
    {
        parent::__construct($name, $collection);
    }

    /**
     * Set response.
     *
     * @param string             $name     Name.
     * @param ValidationResponse $response Validation response.
     *
     * @return void
     */
    public function set($name, $response)
    {
        if (!$response instanceof ValidationResponse) {
            throw new \InvalidArgumentException('$response must be an instance of ValidationResponse');
        }
        $this->collection[$name] = $response;
    }

    /**
     * Check if there are any errors in any of the validation responses.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        foreach ($this->collection as $response) {
            /** @var $response ValidationResponse */
            if ($response->hasErrors()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch all the errors thrown in the validation check
     *
     * @return array
     */
    public function getErrors()
    {
        $errors = array();
        /** @var $response ValidationResponse */
        foreach ($this->collection as $response) {
            if ($response->hasErrors()) {
                $errors = array_merge($errors, $response->getErrors());
            }
        }

        return $errors;
    }
}
