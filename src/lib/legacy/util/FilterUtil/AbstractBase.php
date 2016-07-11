<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This is the base class for all FilterUtil classes.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
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
