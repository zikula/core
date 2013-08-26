<?php
/**
 * Copyright 2009 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula\Core\ModUrl;

/**
 * Process Hook.
 *
 * @deprecated since Core 1.3.6
 * @see Zikula\Core\Hook\DisplayHook
 */
class Zikula_ProcessHook extends Zikula\Core\Hook\ProcessHook
{
    function __construct($name, $id, ModUrl $url=null)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', array(__CLASS__), E_USER_DEPRECATED));
        parent::__construct($name, $id, $url);
    }
}
