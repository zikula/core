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
 * Join Table for the many-to-many relationship categorisable entities -> category.
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

        $this->hasColumn('tablename as table', 'string', 60, array('primary' => true));
        $this->hasColumn('obj_id as obj_id', 'integer', 4, array('primary' => true));
        $this->hasColumn('category_id as category_id', 'integer', 4, array('primary' => true));
        $this->hasColumn('reg_id as reg_id','integer', 4, array('primary' => true));

        $this->hasColumn('reg_property as reg_property', 'string', 60);
        $this->hasColumn('modname as module', 'string', 60);

        $this->setSubclasses(ModUtil::getVar('Categories', 'EntityCategorySubclasses', array()));
    }

    /**
     * Setup relationships.
     *
     * @return void
     */
    public function setUp()
    {
        $this->actAs('Zikula_Doctrine_Template_StandardFields');

        $this->hasOne('Zikula_Doctrine_Model_Registry as Registry', array(
            'local' => 'reg_id',
            'foreign' => 'id'
        ));

        $this->hasOne('Zikula_Doctrine_Model_Category as Category', array(
            'local' => 'category_id',
            'foreign' => 'id'
        ));
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
        $subclasses = ModUtil::getVar('Categories', 'EntityCategorySubclasses', array());

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
