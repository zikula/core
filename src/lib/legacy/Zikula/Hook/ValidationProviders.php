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

use LogUtil;

/**
 * Hook validation collection
 *
 * @deprecated since Core 1.3.6
 * @see Zikula\Core\Hook\ValidationProviders
 */
class Zikula_Hook_ValidationProviders extends Zikula\Core\Hook\ValidationProviders
{
    function __construct($name='validation', ArrayObject $collection = null)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', array(__CLASS__), E_USER_DEPRECATED));
        parent::__construct($name, $collection);
    }
}
