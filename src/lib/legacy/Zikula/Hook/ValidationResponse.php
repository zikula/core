<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Provider
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Validation object for hooks.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Bundle\HookBundle\Hook\ValidationResponse
 */
class Zikula_Hook_ValidationResponse extends Zikula\Bundle\HookBundle\Hook\ValidationResponse
{
    public function __construct($key, $object)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', array(__CLASS__), E_USER_DEPRECATED));
        parent::__construct($key, $object);
    }
}
