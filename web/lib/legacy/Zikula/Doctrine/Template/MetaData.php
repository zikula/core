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
 * This behavior adds record meta data to the record.
 */
class Zikula_Doctrine_Template_MetaData extends Doctrine_Template
{
    /**
     * Adds as Zikula_Doctrine_Template_Listener_MetaData listener.
     *
     * @return void
     */
    public function setUp()
    {
        $this->addListener(new Zikula_Doctrine_Template_Listener_MetaData());
        $this->_table->unshiftFilter(new Zikula_Doctrine_Template_Filter_MetaData());
    }

    /**
     * Setter to set the __META__ value.
     *
     * This setter is required to get Doctrine_Record->merge() work.
     * DO NOT RENAME THIS METHOD!
     *
     * @param array $value Value.
     *
     * @return void
     */
    public function set_META_($value)
    {
        $this->getInvoker()->__META__ = $value;
    }
}

