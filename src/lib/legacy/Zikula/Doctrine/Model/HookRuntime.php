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
 * Zikula_Doctrine_Model_HookRumtime model class.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Model_HookRuntime extends Doctrine_Record
{
    /**
     * Set table definitions.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $this->setTableName('hook_runtime');

        $this->hasColumn('id', 'integer', 4, [
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true
        ]);

        $this->hasColumn('sowner', 'string', 40, [
            'type' => 'string',
            'length' => 40,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('subsowner', 'string', 40, [
            'type' => 'string',
            'length' => 40,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false
        ]);

        $this->hasColumn('powner', 'string', 40, [
            'type' => 'string',
            'length' => 40,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);

        $this->hasColumn('subpowner', 'string', 40, [
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

        $this->hasColumn('pareaid', 'integer', 4, [
            'type' => 'integer',
            'length' => 4,
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

        $this->hasColumn('priority', 'integer', 4, [
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false
        ]);
    }

    /**
     * Setup hook.
     *
     * @return void
     */
    public function setUp()
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        parent::setUp();
    }
}
