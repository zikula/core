<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $errors = [];
        /** @var $response ValidationResponse */
        foreach ($this->collection as $response) {
            if ($response->hasErrors()) {
                $errors = array_merge($errors, $response->getErrors());
            }
        }

        return $errors;
    }
}
