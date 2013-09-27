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
 * Base class of all FilterUtil plugins.
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
