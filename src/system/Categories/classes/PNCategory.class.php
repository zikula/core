<?php
/**
 * Zikula Application Framework
 *
 * @copyright value4business GmbH
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */


/**
 * PNCategory
 *
 * @package Zikula_System_Modules
 * @subpackage Categories
 */
class PNCategory extends DBObject
{
    function PNCategory($init=null, $key=0)
    {
        $this->DBObject();
        $this->_objType       = 'categories_category';
        $this->_objPath       = 'category';

        $this->_objPermissionFilter[] = array('component_left'   => 'Categories',
                                              'component_middle' => '',
                                              'component_right'  => '',
                                              'instance_left'    => 'id',
                                              'instance_middle'  => 'ipath',
                                              'instance_right'   => 'path',
                                              'level'            => ACCESS_READ);

        $this->_objValidation['name']  = array ('name', true, 'noop', '', __('Error! You did not enter a name.'));

        $this->_init($init, $key);
    }

    // checkbox has to be explicitly processed
    function getDataFromInputPostProcess ($data=null)
    {
        if (!$data) {
            $data =& $this->_objData;
        }
        if (!$data) {
            return $data;
        }

        if (isset($data['status'])) {
            $data['status'] = 'A';
        } else {
            $data['status'] = 'I';
        }

        if (!isset($data['is_locked'])) {
            $data['is_locked'] = 0;
        }

        if (!isset($data['is_leaf'])) {
            $data['is_leaf'] = 0;
        }

        $languages = ZLanguage::getInstalledLanguages();
        foreach ($languages as $lang) {
            if (!isset($data['display_name'][$lang]) || !$data['display_name'][$lang]) {
                $data['display_name'][$lang] = __('Error! The localised name has not been defined.');
            }
        }

        $this->_objData = $data;
        return $data;
    }

    // the only reason we need all this stuff beflow is the because of the serialization
    function selectPostProcess ($data=null)
    {
        if (!$data) {
            $data =& $this->_objData;
        }
        if (!$data) {
            return $data;
        }

        $data['display_name'] = DataUtil::formatForDisplayHTML(unserialize($data['display_name']));
        $data['display_desc'] = DataUtil::formatForDisplayHTML(unserialize($data['display_desc']));

        $this->_objData = $data;
        return $data;
    }

    function insertPreProcess ($data=null)
    {
        if (!$data) {
            $data =& $this->_objData;
        }

        if (!$data) {
            return $data;
        }

        $data['display_name_org'] = $data['display_name'];
        $data['display_desc_org'] = $data['display_desc'];
        $data['display_name']     = serialize($data['display_name']);
        $data['display_desc']     = serialize($data['display_desc']);

        // encode slash
        $data['name'] = str_replace ('/', '&#47;', $data['name']);

        $this->_objData = $data;
        return $data;
    }

    function insertPostProcess($data=null)
    {
        if (!$data) {
            $data =& $this->_objData;
        }

        if (!$data) {
            return $data;
        }

        $data['display_name'] = $data['display_name_org'];
        $data['display_desc'] = $data['display_desc_org'];
        unset ($data['display_name_org']);
        unset ($data['display_desc_org']);
        if (isset($_SESSION['Cache'])) {
            unset($_SESSION['Cache']);
        }

        $this->_objData = $data;
        return $data;
    }

    function updatePreProcess ($data=null)
    {
        if (!$data) {
            $data =& $this->_objData;
        }

        if (!$data) {
            return $data;
        }

        Loader::loadClass('CategoryUtil');

        $pid    = (int)$data['parent_id'];
        $parent = CategoryUtil::getCategoryByID ($pid);

        $this->insertPreProcess ();
        $data = $this->_objData;
        $data['path']  = "$parent[path]/$data[name]";
        $data['ipath'] = "$parent[ipath]/$data[id]";

        // encode slash
        $data['name'] = str_replace ('/', '&#47;', $data['name']);

        $this->_objData = $data;
        return $data;
    }

    function updatePostProcess ($data=null)
    {
        if (!$data) {
            $data =& $this->_objData;
        }

        if (!$data) {
            return $data;
        }

        $data['display_name'] = $data['display_name_org'];
        $data['display_desc'] = $data['display_desc_org'];
        unset ($data['display_name_org']);
        unset ($data['display_desc_org']);
        if (isset($_SESSION['Cache'])) {
            unset($_SESSION['Cache']);
        }

        $this->_objData = $data;
        return $data;
    }

    function validatePostProcess ($data=null)
    {
        if (!$data) {
            $data = $this->_objData;
        }

        if (!$data) {
            return false;
        }

        Loader::loadClass('CategoryUtil');

        // ensure that the name we want to use doesn't exist already on this level
        $name = $data['name'];
        $cats = CategoryUtil::getCategoriesByParentID ($data['parent_id'], '', false, '', '', '', 'name');

        if (isset($cats[$name]) && $cats[$name] && $cats[$name]['id'] != $data['id']) {
            $_SESSION['validationErrors'][$this->_objPath]['name'] = "Category $name must be unique under parent";
            $_SESSION['validationFailedObjects'][$this->_objPath] = $data;
            return false;
        }

        return true;
    }

    function deleteMoveSubcategories ($newParentID)
    {
        return $this->delete (false, $newParentID);
    }

    function delete ($deleteSubcats=false, $newParentID=0)
    {
        $data = $this->_objData;

        if (!$data) {
            return $data;
        }

        Loader::loadClass('CategoryUtil');

        if ($deleteSubcats) {
            CategoryUtil::deleteCategoriesByPath ($data['ipath']);
        }
        elseif ($newParentID) {
            CategoryUtil::moveSubCategoriesByPath ($data['ipath'], $newParentID);
            CategoryUtil::deleteCategoryByID ($data['id']);
        }
        else {
            exit ('Can not delete category while preserving subcategories without specifying a new parent ID');
        }
    }

    function move ($newParentID)
    {
        $data = $this->_objData;

        if (!$data) {
            return $data;
        }

        Loader::loadClass('CategoryUtil');

        CategoryUtil::moveCategoriesByPath ($data['ipath'], $newParentID);
    }

    function copy ($newParentID)
    {
        $data = $this->_objData;

        if (!$data) {
            return $data;
        }

        Loader::loadClass('CategoryUtil');

        CategoryUtil::copyCategoriesByPath ($data['ipath'], $newParentID);
    }
}
