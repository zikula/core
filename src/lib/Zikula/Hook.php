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
 * Hook class.
 */
class Zikula_Hook extends Zikula_Event implements Zikula_HookInterface
{
    private $areaId;
    private $caller;

    public function __construct($name, $caller, $subject, $args, $data)
    {
        $this->caller = $caller;
        parent::__construct($name, $subject, $args, $data);
    }

    public function getCaller()
    {
        return $this->caller;
    }

    public function getAreaId()
    {
        return $this->areaId;
    }

    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;
        return $this;
    }
}
