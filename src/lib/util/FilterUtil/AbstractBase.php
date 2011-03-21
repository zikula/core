<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This is the base class for all FilterUtil classes.
 */
abstract class FilterUtil_AbstractBase
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
     * @param FilterUtil_Config $config FilterUtil configuration object.
     */
    public function __construct(FilterUtil_Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get configuration.
     *
     * @return FilterUtil_Config Configuration object.
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Field exists checker.
     *
     * @param string $field Field name.
     *
     * @return bool True if the field exists, false if not.
     */
    protected function fieldExists($field)
    {
        $name = $this->getConfig()->getColumn($field);
        if (!$name || empty($name)) {
            return false;
        }

        return true;
    }

    /**
     * Get field by alias.
     *
     * @param string $alias Field alias.
     *
     * @return string Field name.
     */
    protected function getColumn($alias)
    {
        return $this->getConfig()->getColumn($alias);
    }

    /**
     * Adds common config variables to config array.
     *
     * @param array &$config Config array.
     *
     * @return void
     */
    protected function addCommon(&$config)
    {
        $config['config']  = $this->getConfig();
    }
}
