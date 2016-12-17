<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Bundle\HookBundle\Hook\Hook;

/**
 * AbstractHook class.
 *
 * @deprecated
 */
class Zikula_AbstractHook extends Hook
{
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
     * Get caller.
     *
     * @return string
     */
    public function getCaller()
    {
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        return $this->caller;
    }

    /**
     * Set caller.
     *
     * @param string $caller Caller name
     *
     * @return Zikula_AbstractHook
     */
    public function setCaller($caller)
    {
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

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
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        return $this->id;
    }

    /**
     * Get subscriber area id.
     *
     * @return integer
     */
    public function getAreaId()
    {
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        return $this->areaId;
    }

    /**
     * Set subscriber area id.
     *
     * @param string $areaId ID of the area
     *
     * @return Zikula_DisplayHook
     */
    public function setAreaId($areaId)
    {
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

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
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        $this->stopPropagation();

        return $this;
    }

    /**
     * Has event stopped.
     *
     * @return boolean
     */
    public function isStopped()
    {
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        return $this->isPropagationStopped();
    }

    /**
     * Sets the EventManager property.
     *
     * @param Zikula_EventManagerInterface $eventManager
     *
     * @return void
     */
    public function setEventManager(Zikula_EventManagerInterface $eventManager)
    {
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        $this->setDispatcher($eventManager);
    }

    /**
     * Gets the EventManager.
     *
     * @return Zikula_EventManager
     */
    public function getEventManager()
    {
        @trigger_error('Zikula_AbstractHook is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        return $this->getDispatcher();
    }
}
