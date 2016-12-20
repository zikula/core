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
 * The categories table registry.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Model_Registry extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $this->setTableName('categories_registry');

        $this->hasColumn('id as id', 'integer', 4, ['primary' => true, 'autoincrement' => true]);
        $this->hasColumn('modname as module', 'string', 255);
        $this->hasColumn('tablename as table', 'string', 255);
        $this->hasColumn('property as property', 'string', 255);
        $this->hasColumn('category_id as categoryId', 'integer', 4);
    }
}
