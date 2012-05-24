<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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
 * Zikula_Doctrine_Model_HookBinding model class.
 */
class Zikula_Doctrine_Model_HookBinding extends Doctrine_Record
{
    /**
     * Set table definitions.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('hook_binding');

        $this->hasColumn('id', 'integer', 4, array(
                'type' => 'integer',
                'length' => 4,
                'fixed' => false,
                'unsigned' => false,
                'primary' => true,
                'autoincrement' => true,
        ));

        $this->hasColumn('sowner', 'string', 40, array(
                'type' => 'string',
                'length' => 40,
                'fixed' => false,
                'unsigned' => false,
                'primary' => false,
                'notnull' => true,
                'autoincrement' => false,
        ));

        $this->hasColumn('subsowner', 'string', 40, array(
             'type' => 'string',
             'length' => 40,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));

        $this->hasColumn('powner', 'string', 40, array(
                'type' => 'string',
                'length' => 40,
                'fixed' => false,
                'unsigned' => false,
                'primary' => false,
                'notnull' => true,
                'autoincrement' => false,
        ));

        $this->hasColumn('subpowner', 'string', 40, array(
             'type' => 'string',
             'length' => 40,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));

        $this->hasColumn('sareaid', 'integer', 4, array(
                'type' => 'integer',
                'length' => 4,
                'fixed' => false,
                'unsigned' => false,
                'primary' => false,
                'notnull' => true,
                'autoincrement' => false,
        ));

        $this->hasColumn('pareaid', 'integer', 4, array(
                'type' => 'string',
                'length' => 4,
                'fixed' => false,
                'unsigned' => false,
                'primary' => false,
                'notnull' => true,
                'autoincrement' => false,
        ));

        $this->hasColumn('category', 'string', 20, array(
             'type' => 'string',
             'length' => 10,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));

        $this->hasColumn('sortorder', 'integer', 2, array(
                'type' => 'integer',
                'length' => 2,
                'fixed' => false,
                'unsigned' => false,
                'primary' => false,
                'notnull' => true,
                'default' => 999,
                'autoincrement' => false,
        ));

        $this->index('sortidx', array(
                'fields' => array(
                        'sareaid'),
                        ));
    }

    /**
     * Setup hook.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

}
