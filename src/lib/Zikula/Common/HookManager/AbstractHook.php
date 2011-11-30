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

namespace Zikula\Common\HookManager;

use Zikula\Common\EventManager\EventManagerInterface;

/**
 * AbstractHook class.
 */
class AbstractHook implements HookInterface
{
    /**
     * Name.
     *
     * @var string
     */
    protected $name;

    /**
     * Subscriber object id.
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
     * EventManager instance.
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

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
     * @param string $caller Caller name.
     *
     * @return AbstractHook
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
        return $this;
    }

    /**
     * Get subscriber object id.
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
     * @param string $areaId ID of the area.
     *
     * @return DisplayHook
     */
    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;
        return $this;
    }

    /**
     * Stop futher notification.
     *
     * @return AbstractHook
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
     * @param string $name Hook event name.
     *
     * @return AbstractHook
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the EventManager property.
     *
     * @param EventManagerInterface $eventManager
     *
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Gets the EventManager.
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }
}
