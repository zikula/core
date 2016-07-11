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
 * HookProvider model class.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Model_HookProvider extends Doctrine_Record
{
    /**
     * Set table definitions.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('hook_provider');

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

        $this->hasColumn('pareaid', 'integer', 4, [
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
            'length' => 20,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('classname', 'string', 60, [
            'type' => 'string',
            'length' => 60,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('method', 'string', 60, [
            'type' => 'string',
            'length' => 60,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('serviceid', 'string', 60, [
            'type' => 'string',
            'length' => 60,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false
        ]);

        $this->index('nameidx', [
            'fields' => [
                'pareaid', 'hooktype',
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
