<?php
/**
 * Zikula Application Framework
 *
 * @copyright value4business GmbH
 * @link http://www.zikula.org
 * @version $Id: PNCategoryRegistry.class.php 20307 2006-10-14 21:06:59Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */


/**
 * PNCategoryRegistry
 *
 * @package Zikula_System_Modules
 * @subpackage Categories
 */
class PNCategoryRegistry extends DBObject
{
    function PNCategoryRegistry($init=null, $key=0)
    {
        $this->DBObject();
        $this->_objType = 'categories_registry';
        $this->_objPath = 'category_registry';

        $this->_objValidation['modname']     = array ('modname',     true, 'noop', '', __('Error! You did not select a module.'));
        $this->_objValidation['table']       = array ('table',       true, 'noop', '', __('Error! You did not select a module table.'));
        $this->_objValidation['property']    = array ('property',    true, 'noop', '', __('Error! You did not enter a property name.'));
        $this->_objValidation['category_id'] = array ('category_id', true, 'noop', '', __('Error! You did not select a category.'));

        $this->_init($init, $key);
    }


    function deletePostProcess ($data=null)
    {
        // After delete, it should delete the references to this registry
        // in the categories mapobj table
        $where = "WHERE cmo_reg_id = '{$this->_objData[$this->_objField]}'";
        return DBUtil::deleteWhere('categories_mapobj', $where);
    }


    function validatePostProcess ($data=null)
    {
        $data = $this->_objData;
        if ($data['modname'] && $data['table'] && $data['property'] && !$data['id']) {
            $where = "WHERE crg_modname='$data[modname]' AND crg_table='$data[table]' AND crg_property='$data[property]'";
            $row = DBUtil::selectObject ($this->_objType, $where);
            if ($row) {
                $_SESSION['validationErrors'][$this->_objPath]['property'] = __('Error! There is already a property with this name in the specified module and table.');
                $_SESSION['validationFailedObjects'][$this->_objPath] = $this->_objData;
                return false;
            }
        }
        return true;
    }
}
