<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Base class of all FilterUtil plugins.
 */
class FilterUtil_PluginCommon extends FilterUtil_Common
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
     * Sets parameters each Class could need.
     * array $config must hold:
     *   module: The module name.
     *   table: The table name.
     * It also may contain:
     *   join: The join array.
     *
     * @param array $config Arguments as listed above.
     */
    public function __construct($config)
    {
        parent::__construct($config);
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
}
