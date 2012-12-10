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
class Zikula_FilterHook extends Zikula_AbstractHook
{
    private $data;

    public function __construct($data=null)
    {
        // BC handling for previous constructor
        $funcArgs = func_get_args();
        if (count($funcArgs) == 2) {
            $this->setName($funcArgs[0]);
            $data = $funcArgs[1];
        }

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
