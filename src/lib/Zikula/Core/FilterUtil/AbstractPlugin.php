<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula\Core\FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Core\FilterUtil;

/**
 * Base class of all FilterUtil plugins.
 */
class AbstractPlugin
{
    /**
     * Default handler.
     *
     * @var boolean
     */
    protected $default = false;

    /**
     * ID of the plugin.
     *
     * @var integer
     */
    protected $id;

    /**
     * Config object.
     *
     * @var Config
     */
    protected $config;

    /**
     * Constructor.
     *
     * Argument $config may contain
     *
     * @param Config $config Configuration.
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Sets the plugin id.
     *
     * @param int $id Plugin ID.
     *
     * @return void
     */
    public function setID($id)
    {
        $this->id = $id;
    }

    /**
     * get if this is the default plugin
     */
    public function getDefault()
    {
        return $this->default;
    }
}
