<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\FilterUtil;

/**
 * Base class of all FilterUtil plugins.
 *
 * @deprecated as of 1.5.0
 * @see \Doctrine\ORM\QueryBuilder
 */
abstract class AbstractPlugin
{
    /**
     * Default handler.
     *
     * @var boolean
     */
    private $default = false;

    /**
     * ID of the plugin.
     *
     * @var integer
     */
    private $id;

    /**
     * Config object.
     *
     * @var Config
     */
    protected $config;

    /**
     * Set Config
     *
     * Argument $config may contain
     *
     * @param Config $config Configuration
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Sets the plugin id.
     *
     * @param int $id Plugin ID
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * get if this is the default plugin
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param $boolean
     */
    public function setDefault($boolean)
    {
        $this->default = $boolean;
    }
}
