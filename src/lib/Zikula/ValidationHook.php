<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Content validation hook.
 */
class Zikula_ValidationHook extends Zikula_AbstractHook
{
    /**
     * @var Zikula_Hook_ValidationProviders
     */
    private $validators;

    public function __construct($name, Zikula_Hook_ValidationProviders $validators)
    {
        $this->name = $name;
        $this->validators = $validators;
    }

    public function setValidator($name, Zikula_Hook_ValidationResponse $response)
    {
        $this->validators->set($name, $response);
    }

    public function getValidators()
    {
        return $this->validators;
    }
}
