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
 * Base class of all FilterUtil plugins.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
 */
class FilterUtil_AbstractPlugin extends FilterUtil_AbstractBase
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
     * Constructor.
     *
     * @param array $config Array with the config key.
     */
    public function __construct($config)
    {
        parent::__construct($config['config']);
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
     * Returns empty Sql code.
     *
     * Fallback for build plugins without SQL capabilities.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return string emtpy.
     */
    public function getSQL($field, $op, $value)
    {
        return '';
    }

    /**
     * Returns empty Dql code.
     *
     * Fallback for build plugins without DQL capabilities.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return string empty.
     */
    public function getDql($field, $op, $value)
    {
        return '';
    }
}
