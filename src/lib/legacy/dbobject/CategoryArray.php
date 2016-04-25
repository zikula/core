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
 * CategoryArray
 *
 * @deprecated
 */
class Categories_DBObject_CategoryArray extends DBObjectArray
{
    public function __construct($init = null, $where = '')
    {
        parent::__construct();

        $this->_objType = 'categories_category';
        $this->_objField = 'id';
        $this->_objPath = 'categories_category_array';
        $this->_objPermissionFilter[] = array('component_left' => 'ZikulaCategoriesModule',
                'component_middle' => '',
                'component_right' => '',
                'instance_left' => 'id',
                'instance_middle' => 'ipath',
                'instance_right' => 'path',
                'level' => ACCESS_READ);

        $this->_init($init, $where);
    }

    public function buildRelativePaths($rootCategory, $includeRoot = false)
    {
        CategoryUtil::buildRelativePaths($rootCategory, $this->_objData, $includeRoot);
    }

    // checkbox has to be explicitly processed

    public function getDataFromInputPostProcess($objArray = null)
    {
        if (!$objArray) {
            $objArray = &$this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            if (isset($obj['status'])) {
                $objArray[$k]['status'] = 'A';
            } else {
                $objArray[$k]['status'] = 'I';
            }
        }

        return $objArray;
    }

    // the only reason we need al this stuff beflow is the because of the serialization

    public function selectPostProcess($objArray = null)
    {
        if (!$objArray) {
            $objArray = &$this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            $objArray[$k]['display_name'] = DataUtil::formatForDisplayHTML(unserialize($obj['display_name']));
            $objArray[$k]['display_desc'] = DataUtil::formatForDisplayHTML(unserialize($obj['display_desc']));
        }

        return $objArray;
    }

    public function insertPreProcess($objArray = null)
    {
        if (!$objArray) {
            $objArray = &$this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            $objArray[$k]['display_name_org'] = $obj['display_name'];
            $objArray[$k]['display_desc_org'] = $obj['display_desc'];
            $objArray[$k]['display_name'] = serialize($obj['display_name']);
            $objArray[$k]['display_desc'] = serialize($obj['display_desc']);
        }

        return $objArray;
    }

    public function insertPostProcess($objArray = null)
    {
        if (!$objArray) {
            $objArray = &$this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            $objArray[$k]['display_name'] = $obj['display_name_org'];
            $objArray[$k]['display_desc'] = $obj['display_desc_org'];
            unset($objArray[$k]['display_name_org']);
            unset($objArray[$k]['display_desc_org']);
        }

        return $objArray;
    }

    public function updatePreProcess($objArray = null)
    {
        if (!$objArray) {
            $objArray = &$this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            $pid = $obj['parent_id'];
            $parent = CategoryUtil::getCategoryByID((int)$pid);

            $this->insertPreProcess();
            $objArray[$k]['path'] = "$parent[path]/$obj[name]";
            $objArray[$k]['ipath'] = "$parent[ipath]/$obj[id]";
        }

        return $objArray;
    }

    public function updatePostProcess($objArray = null)
    {
        if ($objArray) {
            return $this->insertPostProcess($objArray);
        }

        return $this->insertPostProcess();
    }

    public function delete($deleteSubcats = false, $newParentID = 0)
    {
        $objArray = $this->_objData;

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            if ($deleteSubcats) {
                CategoryUtil::deleteCategoriesByPath($obj['ipath']);
            } elseif ($newParentID) {
                CategoryUtil::moveSubCategoriesByPath($obj['ipath'], $newParentID);
                CategoryUtil::deleteCategoryByID($obj['id']);
            } else {
                exit('Can not delete category while preserving subcategories without specifying a new parent ID');
            }
        }
    }
}
