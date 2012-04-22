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

/**
 * DisplayHook class.
 */
class Zikula_DisplayHook extends Zikula\Core\Hook\DisplayHook
{
    public function __construct($name, $id, ModUrl $url = null)
    {
        $this->setName($name);

        parent::__construct($id, $url);
    }
}
