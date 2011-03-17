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
 * Reference class.
 */
class Zikula_ServiceManager_Reference
{
    /**
     * Service ID being referenced.
     *
     * @var string
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param string $id Service ID being referenced.
     */
    public function  __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Get service ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
