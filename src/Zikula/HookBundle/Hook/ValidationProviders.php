<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Hook;

use ArrayObject;
use InvalidArgumentException;
use Zikula\Bundle\CoreBundle\Collection\Container;

/**
 * Hook validation collection
 */
class ValidationProviders extends Container
{
    public function __construct(string $name = 'validation', ArrayObject $collection = null)
    {
        parent::__construct($name, $collection);
    }

    /**
     * Set response.
     *
     * @param string $name Name
     * @param ValidationResponse $response Validation response
     */
    public function set($name, $response): void
    {
        if (!$response instanceof ValidationResponse) {
            throw new InvalidArgumentException('$response must be an instance of ValidationResponse');
        }
        $this->collection[$name] = $response;
    }

    /**
     * Check if there are any errors in any of the validation responses.
     */
    public function hasErrors(): bool
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
     */
    public function getErrors(): array
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
