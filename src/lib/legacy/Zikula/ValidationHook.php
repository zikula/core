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

use Zikula\Core\Hook\ValidationProviders;

/**
 * Content validation hook.
 *
 * @deprecated since Core 1.3.6
 * @see Zikula\Core\Hook\DisplayHook
 */
class Zikula_ValidationHook extends Zikula\Core\Hook\ValidationHook
{
    /**
     * @param ValidationProviders $validators
     */
    public function __construct($name, ValidationProviders $validators)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', array(__CLASS__), E_USER_DEPRECATED));
        $this->setName($name);
        $this->validators = $validators;
    }
}
