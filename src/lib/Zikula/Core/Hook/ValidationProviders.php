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

namespace Zikula\Core\Hook;

/**
 * Hook validation collection
 */
class ValidationProviders extends \Zikula\Common\Collection\Container
{
    /**
     * Constructor.
     *
     * @param string      $name       The name of the collection.
     * @param \ArrayObject $collection The collection (optional).
     */
    public function __construct($name='validation', \ArrayObject $collection = null)
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
            throw new \InvalidArgumentException('$response must be an instance of Zikula\Core\Hook\ValidationResponse');
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
        /* @var ValidationResponse $response */
        foreach ($this->collection as $response) {
            if ($response->hasErrors()) {
                return true;
            }
        }

        return false;
    }
}
