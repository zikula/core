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
 * Filter Hook class.
 */
class Zikula_FilterHook implements Zikula_HookInterface
{
    private $name;
    private $caller;
    private $subject;
    public $data;
    private $areaId;
    private $stopped;

    public function __construct($name, $caller, $subject, $data)
    {
        $this->name = $name;
        $this->caller = $caller;
        $this->subject = $subject;
        $this->data = $data;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAreaId()
    {
        return $this->areaId;
    }

    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;
    }

    public function getCaller()
    {
        return $this->caller;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function stop()
    {
        $this->stopped = true;
    }

    public function isStopped()
    {
        return (bool)$this->stopped;
    }
}
