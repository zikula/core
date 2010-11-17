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
 * Zikula Version HookBundle
 */
class Zikula_Version_HookBundle
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
     * Constructor.
     *
     * @param string $title Title.
     */
    public function __construct($title)
    {
        $this->title = $title;
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
     * Add a subscriber to the bundle.
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
