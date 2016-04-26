<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HookSubscriber model class.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Model_HookSubscriber extends Doctrine_Record
{
    /**
     * Set table definitions.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('hook_subscriber');

        $this->hasColumn('id', 'integer', 4, [
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true
        ]);

        $this->hasColumn('owner', 'string', 40, [
            'type' => 'string',
            'length' => 40,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('subowner', 'string', 40, [
            'type' => 'string',
            'length' => 40,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false
        ]);

        $this->hasColumn('sareaid', 'integer', 4, [
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('hooktype', 'string', 20, [
            'type' => 'string',
            'length' => 20,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('category', 'string', 20, [
            'type' => 'string',
            'length' => 10,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('eventname', 'string', 100, [
            'type' => 'string',
            'length' => 100,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->index('myindex', [
            'fields' => [
                'eventname' => [
                    'sorting' => 'ASC',
                    'length'  => 100
                ]
            ],
            'type' => 'unique'
        ]);
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
