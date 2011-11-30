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
 * This behavior takes care for adding the standard fields if desired.
 *
 * Usage:
 * <code>
 * // in a doctrine model
 * public function setUp() {
 *     $this->actAs('Zikula_Doctrine_Template_StandardFields', array('oldColumnPrefix' => 'z_'));
 * }
 * </code>
 */
class Zikula_Doctrine_Template_StandardFields extends Doctrine_Template
{
    /**
     * Constructor.
     *
     * Options:
     *   oldColumnPrefix: all columns will be prefixed this string e.g. z_
     *
     * @param array $options Options.
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }

    /**
     * Add the columns used by the standard fields.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        // historical prefix
        $oldPrefix = isset($this->_options['oldColumnPrefix'])? $this->_options['oldColumnPrefix'] : '';

        $this->hasColumn($oldPrefix . 'obj_status as obj_status', 'string', 1, array('type' => 'string', 'length' => 1, 'notnull' => true, 'default' => 'A'));
        $this->hasColumn($oldPrefix . 'cr_date as cr_date', 'timestamp', null, array('type' => 'timestamp', 'notnull' => true, 'default' => '1970-01-01 00:00:00'));
        $this->hasColumn($oldPrefix . 'cr_uid as cr_uid', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'default' => '0'));
        $this->hasColumn($oldPrefix . 'lu_date as lu_date', 'timestamp', null, array('type' => 'timestamp', 'notnull' => true, 'default' => '1970-01-01 00:00:00'));
        $this->hasColumn($oldPrefix . 'lu_uid as lu_uid', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'default' => '0'));
    }

    /**
     * Add the standard fields listener to the record.
     *
     * @return void
     */
    public function setUp()
    {
        // take care for setting these values automatically
        $this->addListener(new Zikula_Doctrine_Template_Listener_StandardFields());
    }
}

