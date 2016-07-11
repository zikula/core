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
 * This behavior adds record meta data to the record.
 *
 * @deprecated since 1.4.0
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
