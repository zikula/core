<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Doctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This behavior manages logging changes of a record.
 */
class Zikula_Doctrine_Template_Logging extends Doctrine_Template
{
    /**
     * Adds as Zikula_Doctrine_Template_Listener_MetaData listener.
     *
     * @return void
     */
    public function setUp()
    {
        $this->addListener(new Zikula_Doctrine_Template_Listener_Logging());
    }
}

