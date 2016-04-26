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
 * An category.
 *
 * @deprecated since 1.4.0
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

        $this->hasColumn('id as id', 'integer', 4, ['primary' => true, 'autoincrement' => true]);
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
