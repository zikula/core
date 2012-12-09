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
 *
 * @deprecated from 1.4
 * @use \Symfony\Component\DependencyInjection\Reference
 */
class Zikula_ServiceManager_Reference extends \Symfony\Component\DependencyInjection\Reference
{
    /**
     * Get service ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->__toString();
    }
}
