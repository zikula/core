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
 * The categories table registry.
 */
class Categories_Models_Registry extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('categories_registry');

        $this->hasColumn('crg_id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('crg_modname as module', 'string', 255);
        $this->hasColumn('crg_table as table', 'string', 255);
        $this->hasColumn('crg_property as property', 'string', 255);
        $this->hasColumn('crg_category_id as categoryId', 'integer', 4);
    }
}

