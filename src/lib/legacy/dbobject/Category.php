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
 * Category
 *
 * @deprecated
 */
class Categories_DBObject_Category extends DBObject
{
    public function __construct($init = null, $key = 0)
    {
        parent::__construct();
        $this->_objType = 'categories_category';
        $this->_objPath = 'category';

        $this->_objPermissionFilter[] = array('component_left' => 'ZikulaCategoriesModule',
                'component_middle' => '',
                'component_right' => '',
                'instance_left' => 'id',
                'instance_middle' => 'ipath',
                'instance_right' => 'path',
                'level' => ACCESS_READ);

        $this->_objValidation['name'] = array('name', true, 'noop', '', __('Error! You did not enter a name.'), '');

        $this->_init($init, $key);
    }

    // checkbox has to be explicitly processed

    public function getDataFromInputPostProcess($data = null)
    {
        if (!$data) {
            $data = &$this->_objData;
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
                $data['display_name'][$lang] = $data['name'];
            }
        }

        $this->_objData = $data;

        return $data;
    }

    // the only reason we need all this stuff beflow is the because of the serialization

    public function selectPostProcess($data = null)
    {
        if (!$data) {
            $data = &$this->_objData;
        }
        if (!$data) {
            return $data;
        }

        $data['display_name'] = DataUtil::formatForDisplayHTML(unserialize($data['display_name']));
        $data['display_desc'] = DataUtil::formatForDisplayHTML(unserialize($data['display_desc']));

        $this->_objData = $data;

        return $data;
    }

    public function insertPreProcess($data = null)
    {
        if (!$data) {
            $data = &$this->_objData;
        }

        if (!$data) {
            return $data;
        }

        $data['display_name_org'] = $data['display_name'];
        $data['display_desc_org'] = $data['display_desc'];
        $data['display_name'] = serialize($data['display_name']);
        $data['display_desc'] = serialize($data['display_desc']);

        // encode slash
        $data['name'] = str_replace('/', '&#47;', $data['name']);

        // set defaults: necessary @since 1.4.0
        $data['obj_status'] = isset($data['obj_status']) ? $data['obj_status'] : 'A';
        $data['status'] = isset($data['status']) ? $data['status'] : 'A';
        $data['sort_value'] = isset($data['sort_value']) ? $data['sort_value'] : 2147483647;
        $data['value'] = isset($data['value']) ? $data['value'] : '';

        $this->_objData = $data;

        return $data;
    }

    public function insertPostProcess($data = null)
    {
        if (!$data) {
            $data = &$this->_objData;
        }

        if (!$data) {
            return $data;
        }

        $data['display_name'] = $data['display_name_org'];
        $data['display_desc'] = $data['display_desc_org'];
        unset($data['display_name_org']);
        unset($data['display_desc_org']);
        if (isset($_SESSION['Cache'])) {
            unset($_SESSION['Cache']);
        }

        $this->_objData = $data;

        return $data;
    }

    public function updatePreProcess($data = null)
    {
        if (!$data) {
            $data = &$this->_objData;
        }

        if (!$data) {
            return $data;
        }

        $pid = (int)$data['parent_id'];
        $parent = CategoryUtil::getCategoryByID($pid);

        $this->insertPreProcess();
        $data = $this->_objData;
        $data['path'] = "$parent[path]/$data[name]";
        $data['ipath'] = "$parent[ipath]/$data[id]";

        // encode slash
        $data['name'] = str_replace('/', '&#47;', $data['name']);

        $this->_objData = $data;

        return $data;
    }

    public function updatePostProcess($data = null)
    {
        if (!$data) {
            $data = &$this->_objData;
        }

        if (!$data) {
            return $data;
        }

        $data['display_name'] = $data['display_name_org'];
        $data['display_desc'] = $data['display_desc_org'];
        unset($data['display_name_org']);
        unset($data['display_desc_org']);
        if (isset($_SESSION['Cache'])) {
            unset($_SESSION['Cache']);
        }

        $this->_objData = $data;

        return $data;
    }

    public function validatePostProcess($type = 'user', $data = null)
    {
        if (!$data) {
            $data = $this->_objData;
        }

        if (!$data) {
            return false;
        }

        // ensure that the name we want to use doesn't exist already on this level
        $name = $data['name'];
        $cats = CategoryUtil::getCategoriesByParentID($data['parent_id'], '', false, '', 'name');

        if (isset($cats[$name]) && $cats[$name] && $cats[$name]['id'] != (isset($data['id']) ? $data['id'] : null)) {
            $_SESSION['validationErrors'][$this->_objPath]['name'] = __f(/*!%s is category name*/'Category %s must be unique under parent', $name);
            $_SESSION['validationFailedObjects'][$this->_objPath] = $data;

            return false;
        }

        return true;
    }

    public function deleteMoveSubcategories($newParentID)
    {
        return $this->delete(false, $newParentID);
    }

    public function delete($deleteSubcats = false, $newParentID = 0)
    {
        $data = $this->_objData;

        if (!$data) {
            return $data;
        }

        if ($deleteSubcats) {
            CategoryUtil::deleteCategoriesByPath($data['ipath']);
        } elseif ($newParentID) {
            CategoryUtil::moveSubCategoriesByPath($data['ipath'], $newParentID);
            CategoryUtil::deleteCategoryByID($data['id']);
        } else {
            exit(__('Can not delete category while preserving subcategories without specifying a new parent ID'));
        }
    }

    public function move($newParentID)
    {
        $data = $this->_objData;

        if (!$data) {
            return $data;
        }

        CategoryUtil::moveCategoriesByPath($data['ipath'], $newParentID);
    }

    public function copy($newParentID)
    {
        $data = $this->_objData;

        if (!$data) {
            return $data;
        }

        CategoryUtil::copyCategoriesByPath($data['ipath'], $newParentID);
    }
}
