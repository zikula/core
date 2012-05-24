<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_ServiceManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_ServiceManager_Argument container class.
 *
 * This class contains an argument id which references a stored parameter.
 */
class Zikula_ServiceManager_Argument
{
    /**
     * Argument Id.
     *
     * @var string
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param string $id Argument id.
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Get id property.
     *
     * @return string Argument id.
     */
    public function getId()
    {
        return $this->id;
    }
}
