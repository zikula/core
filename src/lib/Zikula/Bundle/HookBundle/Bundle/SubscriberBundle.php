<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Bundle;

/**
 * Bundle for Hook Subscribers
 */
class SubscriberBundle
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
    private $events = [];

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
     * @param string $owner    Owner
     * @param string $area     Area ID, this should be a unique string
     * @param string $category Category
     * @param string $title    Title
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
     * @param string $subOwner
     *
     * @return SubscriberBundle
     */
    public function setSubOwner($subOwner)
    {
        $this->subOwner = $subOwner;

        return $this;
    }

    /**
     * Add a subscriber hook type event to this bundle.
     *
     * @param string $type      Hook type
     * @param string $eventName Event name
     *
     * @return SubscriberBundle
     */
    public function addEvent($type, $eventName)
    {
        $this->events[$type] = $eventName;

        return $this;
    }
}
