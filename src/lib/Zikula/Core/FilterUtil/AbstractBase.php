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
 * This is the base class for all FilterUtil classes.
 */
abstract class AbstractBase
{
    /**
     * Config object.
     *
     * @var FilterUtil_Config
     */
    protected $config;

    /**
     * Constructor.
     *
     * Sets the configuration object.
     *
     * @param Config $config FilterUtil configuration object.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get configuration.
     *
     * @return Config Configuration object.
     */
    public function getConfig()
    {
        return $this->config;
    }
}
