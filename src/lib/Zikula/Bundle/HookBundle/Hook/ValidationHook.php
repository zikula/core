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

/**
 * Content validation hook.
 */
class ValidationHook extends Hook
{
    /**
     * @var ValidationProviders
     */
    private $validators;

    public function __construct(ValidationProviders $validators)
    {
        if (isset($validators)) {
            $this->validators = $validators;
        } else {
            $this->validators = new ValidationProviders();
        }
    }

    /**
     * Sets the validation response.
     *
     * @param $name
     * @param ValidationResponse $response
     */
    public function setValidator($name, ValidationResponse $response)
    {
        $this->validators->set($name, $response);
    }

    /**
     * Gets validation providers
     *
     * @return ValidationProviders
     */
    public function getValidators()
    {
        return $this->validators;
    }
}
