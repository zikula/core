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

/**
 * Hook validation collection
 */
class Zikula_Hook_ValidationProviders extends Zikula_Collection_Container
{
    /**
     * Construct a new Zikula_Hook_HookValidationProviders.
     *
     * @param string      $name       The name of the collection.
     * @param ArrayObject $collection The collection (optional).
     */
    public function __construct($name='validation', ArrayObject $collection = null)
    {
        parent::__construct($name, $collection);
    }

    /**
     * Set response.
     *
     * @param string                         $name     Name.
     * @param Zikula_Response_HookValidation $response Validation response.
     *
     * @throws InvalidArgumentException If $response is not an instance of Zikula_Provider_HookValidation
     *
     * @return void
     */
    public function set($name, $response)
    {
        if (!$response instanceof Zikula_Provider_HookValidation) {
            throw new InvalidArgumentException('Response must be an instance of Zikula_Provider_HookValidation');
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
            if ($response->hasErrors()) {
                return true;
            }
        }

        return false;
    }
}