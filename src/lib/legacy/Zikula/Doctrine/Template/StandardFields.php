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
 * This behavior takes care for adding the standard fields if desired.
 *
 * Usage:
 * <code>
 * // in a doctrine model
 * public function setUp() {
 *     $this->actAs('Zikula_Doctrine_Template_StandardFields', ['oldColumnPrefix' => 'z_']);
 * }
 * </code>
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Template_StandardFields extends Doctrine_Template
{
    /**
     * Constructor.
     *
     * Options:
     *   oldColumnPrefix: all columns will be prefixed this string e.g. z_
     *
     * @param array $options Options
     */
    public function __construct(array $options = [])
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        parent::__construct($options);
    }

    /**
     * Add the columns used by the standard fields.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        // historical prefix
        $oldPrefix = isset($this->_options['oldColumnPrefix']) ? $this->_options['oldColumnPrefix'] : '';

        $this->hasColumn($oldPrefix . 'obj_status as obj_status', 'string', 1, ['type' => 'string', 'length' => 1, 'notnull' => true, 'default' => 'A']);
        $this->hasColumn($oldPrefix . 'cr_date as cr_date', 'timestamp', null, ['type' => 'timestamp', 'notnull' => true, 'default' => '1970-01-01 00:00:00']);
        $this->hasColumn($oldPrefix . 'cr_uid as cr_uid', 'integer', 4, ['type' => 'integer', 'notnull' => true, 'default' => '0']);
        $this->hasColumn($oldPrefix . 'lu_date as lu_date', 'timestamp', null, ['type' => 'timestamp', 'notnull' => true, 'default' => '1970-01-01 00:00:00']);
        $this->hasColumn($oldPrefix . 'lu_uid as lu_uid', 'integer', 4, ['type' => 'integer', 'notnull' => true, 'default' => '0']);
    }

    /**
     * Add the standard fields listener to the record.
     *
     * @return void
     */
    public function setUp()
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        // take care for setting these values automatically
        $this->addListener(new Zikula_Doctrine_Template_Listener_StandardFields());
    }
}
