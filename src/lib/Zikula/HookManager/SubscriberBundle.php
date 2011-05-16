<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Bundle for Hook Subscribers
 */
class Zikula_HookManager_SubscriberBundle
{
    /**
     * Owner.
     *
     * @var string
     */
    private $owner;

    /**
     * Sub owner.
     *
     * @var string
     */
    private $subOwner;

    /**
     * Hook events.
     *
     * @var array
     */
    private $events = array();

    /**
     * Title.
     *
     * @var string
     */
    private $title;

    /**
     * Area ID.
     *
     * @var string
     */
    private $area;

    /**
     * Category.
     *
     * @var string
     */
    private $category;

    /**
     * Constructor.
     *
     * @param string $owner    Owner.
     * @param string $area     Area ID, this should be a unique string.
     * @param string $category Category.
     * @param string $title    Title.
     */
    public function __construct($owner, $area, $category, $title)
    {
        $this->owner = $owner;
        $this->area = $area;
        $this->category = $category;
        $this->title = $title;
    }

    /**
     * Get events.
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Get title property.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get area property.
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Get category property.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get owner property.
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get subOwner property.
     *
     * @return string
     */
    public function getSubOwner()
    {
        return $this->subOwner;
    }

    /**
     * Set subOwner property.
     *
     * @param type $subOwner
     *
     * @return Zikula_HookManager_SubscriberBundle
     */
    public function setSubOwner($subOwner)
    {
        $this->subOwner = $subOwner;
        return $this;
    }

    /**
     * Add a subscriber hook type event to this bundle.
     *
     * @param string $type      Hook type.
     * @param string $eventName Event name.
     *
     * @return Zikula_HookManager_SubscriberBundle
     */
    public function addEvent($type, $eventName)
    {
        $this->events[$type] = $eventName;
        return $this;
    }
}
