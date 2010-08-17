<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Models
 */

/**
 * An category.
 */
class Categories_Models_Category extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('categories_category');

        $this->hasColumn('cat_id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('cat_parent_id as parent_id', 'integer', 4);
        $this->hasColumn('cat_is_locked as is_locked', 'boolean');
        $this->hasColumn('cat_is_leaf as is_leaf', 'boolean');
        $this->hasColumn('cat_name as name', 'string', 255);
        $this->hasColumn('cat_value as value', 'string', 255);
        $this->hasColumn('cat_sort_value as sort_value', 'integer', 4);
        $this->hasColumn('cat_display_name as display_name', 'string', 4000);
        $this->hasColumn('cat_display_desc as display_desc', 'string', 4000);
        $this->hasColumn('cat_path as path', 'string', 4000);
        $this->hasColumn('cat_ipath as ipath', 'string', 255);
        $this->hasColumn('cat_status as status', 'integer', 1);
    }
}

