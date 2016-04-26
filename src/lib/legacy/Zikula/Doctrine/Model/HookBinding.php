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
 * Zikula_Doctrine_Model_HookBinding model class.
 *
 * @deprecated since 1.4.0
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
            'type' => 'string',
            'length' => 4,
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

        $this->hasColumn('sortorder', 'integer', 2, [
            'type' => 'integer',
            'length' => 2,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'default' => 999,
            'autoincrement' => false
        ]);

        $this->index('sortidx', [
            'fields' => [
                'sareaid'
            ]
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
