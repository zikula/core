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
 * The categories table registry.
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
        $this->setTableName('categories_registry');

        $this->hasColumn('id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('modname as module', 'string', 255);
        $this->hasColumn('tablename as table', 'string', 255);
        $this->hasColumn('property as property', 'string', 255);
        $this->hasColumn('category_id as categoryId', 'integer', 4);
    }
}

