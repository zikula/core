<?php
/**
 * Zikula Application Framework
 *
 * @copyright value4business GmbH
 * @link http://www.zikula.org
 * @version $Id: PNCategoryArray.class.php 20307 2006-10-14 21:06:59Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */


/**
 * PNCategoryArray
 *
 * @package Zikula_System_Modules
 * @subpackage Categories
 */
class PNCategoryArray extends DBObjectArray
{
    function PNCategoryArray($init=null, $where='')
    {
        $this->DBObjectArray ();

        $this->_objType  = 'categories_category';
        $this->_objField = 'id';
        $this->_objPath  = 'categories_category_array';
        $this->_objPermissionFilter[] = array('component_left'   => 'Categories',
                                              'component_middle' => '',
                                              'component_right'  => '',
                                              'instance_left'    => 'id',
                                              'instance_middle'  => 'ipath',
                                              'instance_right'   => 'path',
                                              'level'            => ACCESS_READ);

        $this->_init($init, $where);
    }

    function buildRelativePaths ($rootCategory, $includeRoot=false)
    {
        Loader::loadClass ('CategoryUtil');
        CategoryUtil::buildRelativePaths ($rootCategory, $this->_objData, $includeRoot);
    }

    // checkbox has to be explicitly processed
    function getDataFromInputPostProcess ($objArray=null)
    {
        if (!$objArray) {
            $objArray =& $this->_objData;
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
    function selectPostProcess ($objArray=null)
    {
        if (!$objArray) {
            $objArray =& $this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj)
        {
            $objArray[$k]['display_name'] = DataUtil::formatForDisplayHTML(unserialize($obj['display_name']));
            $objArray[$k]['display_desc'] = DataUtil::formatForDisplayHTML(unserialize($obj['display_desc']));
        }

        return $objArray;
    }

    function insertPreProcess ($objArray=null)
    {
        if (!$objArray) {
            $objArray =& $this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj)
        {
            $objArray[$k]['display_name_org'] = $obj['display_name'];
            $objArray[$k]['display_desc_org'] = $obj['display_desc'];
            $objArray[$k]['display_name']     = serialize($obj['display_name']);
            $objArray[$k]['display_desc']     = serialize($obj['display_desc']);
        }

        return $objArray;
    }

    function insertPostProcess($objArray=null)
    {
        if (!$objArray) {
            $objArray =& $this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            $objArray[$k]['display_name'] = $obj['display_name_org'];
            $objArray[$k]['display_desc'] = $obj['display_desc_org'];
            unset ($objArray[$k]['display_name_org']);
            unset ($objArray[$k]['display_desc_org']);
        }

        return $objArray;
    }

    function updatePreProcess ($objArray=null)
    {
        if (!$objArray) {
            $objArray =& $this->_objData;
        }

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            $pid    =  $obj['parent_id'];
            $parent =  CategoryUtil::getCategoryByID ((int)$pid);

            $this->insertPreProcess ();
            $objArray[$k]['path']         = "$parent[path]/$obj[name]";
            $objArray[$k]['ipath']        = "$parent[ipath]/$obj[id]";
        }

        return $objArray;
    }

    function updatePostProcess ($objArray=null)
    {
        if ($objArray) {
            return $this->insertPostProcess ($objArray);
        }

        return $this->insertPostProcess ();
    }

    function delete ($deleteSubcats=false, $newParentID=0)
    {
        $objArray = $this->_objData;

        if (!$objArray) {
            return $objArray;
        }

        foreach ($objArray as $k => $obj) {
            if ($deleteSubcats) {
                CategoryUtil::deleteCategoriesByPath ($obj['ipath']);
            } elseif ($newParentID) {
                CategoryUtil::moveSubCategoriesByPath ($obj['ipath'], $newParentID);
                CategoryUtil::deleteCategoryByID ($obj['id']);
            } else {
                exit ('Can not delete category while preserving subcategories without specifying a new parent ID');
            }
        }
    }
}
