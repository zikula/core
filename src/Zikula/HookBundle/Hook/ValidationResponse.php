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
     * @param array|object $object Object to be validated
     */
    public function __construct(string $key, $object)
    {
        $this->key = $key;
        $this->object = $object;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return array|object
     */
    public function getObject()
    {
        return $this->object;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function hasErrors(): bool
    {
        return (bool) $this->errors;
    }
}
