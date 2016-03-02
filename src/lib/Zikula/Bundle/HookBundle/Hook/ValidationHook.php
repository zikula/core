<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookDispatcher
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
