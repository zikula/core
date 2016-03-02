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

namespace Zikula\Bundle\HookBundle\Hook;

use Symfony\Component\EventDispatcher\Event;

/**
 * Hook class.
 */
class Hook extends Event
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
        return $this->caller;
    }

    /**
     * Set caller.
     *
     * @param string $caller Caller name.
     *
     * @return Hook
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
     * @return Hook
     */
    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;

        return $this;
    }
}
