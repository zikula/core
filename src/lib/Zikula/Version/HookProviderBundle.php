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
 * Zikula Version HookBundle for Hook Providers
 */
class Zikula_Version_HookProviderBundle
{
    /**
     * Hook handlers.
     *
     * @var array
     */
    protected $hooks = array();

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
     * Get hookhandlers property.
     *
     * @return array
     */
    public function getHooks()
    {
        return $this->hooks;
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
     * Add a hook handler with this bundle.
     *
     * @param string  $name      Name of the hook handler.
     * @param string  $type      Hook type.
     * @param string  $className Class.
     * @param string  $method    Method name.
     * @param string  $serviceId Service ID if this is NOT a static class method.
     * @param integer $weight    Default weighting.
     *
     * @return void
     */
    public function addHook($name, $type, $className, $method, $serviceId=null, $weight=10)
    {
        $this->hooks[$name] = array(
                'type' => $type,
                'classname' => $className,
                'method' => $method,
                'serviceid' => $serviceId,
                'weight' => $weight,
                );
    }
}
