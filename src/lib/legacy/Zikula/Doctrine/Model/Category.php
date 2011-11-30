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
 * An category.
 */
class Zikula_Doctrine_Model_Category extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('categories_category');

        $this->hasColumn('id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('parent_id as parent_id', 'integer', 4);
        $this->hasColumn('is_locked as is_locked', 'boolean');
        $this->hasColumn('is_leaf as is_leaf', 'boolean');
        $this->hasColumn('name as name', 'string', 255);
        $this->hasColumn('value as value', 'string', 255);
        $this->hasColumn('sort_value as sort_value', 'integer', 4);
        $this->hasColumn('display_name as display_name', 'array');
        $this->hasColumn('display_desc as display_desc', 'array');
        $this->hasColumn('path as path', 'string', 4000);
        $this->hasColumn('ipath as ipath', 'string', 255);
        $this->hasColumn('status as status', 'integer', 1);
    }
}

