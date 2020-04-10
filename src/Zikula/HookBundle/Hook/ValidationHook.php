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
 * Content validation hook.
 */
class ValidationHook extends Hook
{
    /**
     * @var ValidationProviders
     */
    private $validators;

    public function __construct(ValidationProviders $validators = null)
    {
        $this->validators = $validators ?? new ValidationProviders();
    }

    /**
     * Sets the validation response.
     */
    public function setValidator(string $name, ValidationResponse $response): void
    {
        $this->validators->set($name, $response);
    }

    public function getValidators(): ValidationProviders
    {
        return $this->validators;
    }
}
