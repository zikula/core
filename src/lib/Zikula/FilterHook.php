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
 * Content filter hook.
 */
class Zikula_FilterHook extends Zikula\Core\Hook\FilterHook
{
    public function __construct($name, $data=null)
    {
        $this->setName($name);

        parent::__construct($data);
    }
}
