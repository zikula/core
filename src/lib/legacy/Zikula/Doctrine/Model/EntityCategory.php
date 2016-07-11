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
 * Join Table for the many-to-many relationship categorisable entities -> category.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Model_EntityCategory extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('categories_mapobj');

        $this->hasColumn('tablename as table', 'string', 60, ['primary' => true]);
        $this->hasColumn('obj_id as obj_id', 'integer', 4, ['primary' => true]);
        $this->hasColumn('category_id as category_id', 'integer', 4, ['primary' => true]);
        $this->hasColumn('reg_id as reg_id', 'integer', 4, ['primary' => true]);

        $this->hasColumn('reg_property as reg_property', 'string', 60);
        $this->hasColumn('modname as module', 'string', 60);

        $this->setSubclasses(ModUtil::getVar('ZikulaCategoriesModule', 'EntityCategorySubclasses', []));
    }

    /**
     * Setup relationships.
     *
     * @return void
     */
    public function setUp()
    {
        $this->actAs('Zikula_Doctrine_Template_StandardFields');

        $this->hasOne('Zikula_Doctrine_Model_Registry as Registry', [
            'local' => 'reg_id',
            'foreign' => 'id'
        ]);

        $this->hasOne('Zikula_Doctrine_Model_Category as Category', [
            'local' => 'category_id',
            'foreign' => 'id'
        ]);
    }

    /**
     * Sets the value of the Registry Relation.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function preSave($event)
    {
        $subclasses = ModUtil::getVar('ZikulaCategoriesModule', 'EntityCategorySubclasses', []);

        // get the registry object
        $registry = Doctrine::getTable('Zikula_Doctrine_Model_Registry')
                        ->findOneByModuleAndTableAndProperty(
                            $subclasses[get_class($this)]['module'],
                            $subclasses[get_class($this)]['table'],
                            $this->reg_property
                        );

        $this['Registry'] = $registry;
    }
}
