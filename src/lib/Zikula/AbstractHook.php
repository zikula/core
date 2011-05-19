<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
/**
 * AbstractHook class.
 */
class Zikula_AbstractHook implements Zikula_HookInterface
{
    /**
     * Name.
     *
     * @var string
     */
    protected $name;

    /**
     * Subscriber area id.
     *
     * @var integer
     */
    protected $id;

    /**
     * Subscriber area id.
     *
     * @var integer
     */
    protected $areaId;

    /**
     * Caller.
     *
     * @var string
     */
    protected $caller;

    /**
     * Stop notification flag.
     *
     * @var boolean
     */
    protected $stopped = false;

    /**
     * Get caller.
     *
     * @return string
     */
    public function getCaller()
    {
        return $this->caller;
    }

    /**
     * Set caller.
     *
     * @return Zikula_AbstractHook
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
        return $this;
    }

    /**
     * Get subscriber area id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get subscriber area id.
     *
     * @return integer
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * Set subscriber area id.
     *
     * @param type $areaId
     *
     * @return Zikula_DisplayHook
     */
    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;
        return $this;
    }

    /**
     * Stop futher notification.
     *
     * @return Zikula_AbstractHook
     */
    public function stop()
    {
        $this->stopped = true;
        return $this;
    }

    /**
     * Has event stopped.
     *
     * @return boolean
     */
    public function isStopped()
    {
        return $this->stopped;
    }

    /**
     * Get event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set event name.
     *
     * @param type $name Hook event Name
     *
     * @return Zikula_AbstractHook
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
