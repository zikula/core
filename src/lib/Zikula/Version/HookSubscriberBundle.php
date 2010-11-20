<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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
 * Zikula Version HookBundle for Hook Subscribers
 */
class Zikula_Version_HookSubscriberBundle
{
    /**
     * Hook types.
     *
     * @var array
     */
    protected $hookTypes = array();

    /**
     * Title.
     *
     * @var string
     */
    protected $title;

    /**
     * Area ID.
     *
     * @var string
     */
    protected $area;

    /**
     * Constructor.
     *
     * @param string $area  Area ID, this should be a unique string.
     * @param string $title Title.
     */
    public function __construct($area, $title)
    {
        $this->title = $title;
        $this->area = $area;
    }

    /**
     * Get hooktypes property.
     *
     * @return array
     */
    public function getHookTypes()
    {
        return $this->hookTypes;
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
     * Add a subscriber to this bundle.
     *
     * @param string $type      Hook type.
     * @param string $eventName Event name.
     *
     * @return void
     */
    public function addType($type, $eventName)
    {
        $this->hookTypes[$type] = $eventName;
    }
}
