<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CategoryRegistry
 *
 * @deprecated
 */
class Categories_DBObject_Registry extends DBObject
{
    public function __construct($init = null, $key = 0)
    {
        parent::__construct();
        $this->_objType = 'categories_registry';
        $this->_objPath = 'category_registry';

        $this->_objValidation['modname'] = ['modname', true, 'noop', '', __('Error! You did not select a module.')];
        $this->_objValidation['table'] = ['table', true, 'noop', '', __('Error! You did not select a module table.')];
        $this->_objValidation['property'] = ['property', true, 'noop', '', __('Error! You did not enter a property name.')];
        $this->_objValidation['category_id'] = ['category_id', true, 'noop', '', __('Error! You did not select a category.')];

        $this->_init($init, $key);
    }

    public function insertPreProcess($data = null)
    {
        if (!$data) {
            $data = $this->_objData;
        }

        if (!$data) {
            return $data;
        }

        // set defaults: necessary @since 1.4.0
        $data['obj_status'] = isset($data['obj_status']) ? $data['obj_status'] : 'A';

        $this->_objData = $data;

        return $data;
    }

    public function deletePostProcess($data = null)
    {
        // After delete, it should delete the references to this registry
        // in the categories mapobj table
        $where = "WHERE reg_id = '{$this->_objData[$this->_objField]}'";

        return DBUtil::deleteWhere('categories_mapobj', $where);
    }

    public function updatePostProcess($data = null)
    {
        // update property in categories_mapobj too
        Doctrine::getTable('Zikula_Doctrine_Model_EntityCategory')->createQuery()
                ->update()
                ->set('reg_property', '?', $this->_objData['property'])
                ->where('reg_id = ?', $this->_objData[$this->_objField])
                ->execute();

        return true;
    }

    public function validatePostProcess($type = 'user', $data = null)
    {
        $data = $this->_objData;
        if ($data['modname'] && $data['table'] && $data['property'] && (!isset($data['id']) || !$data['id'])) {
            $where = "WHERE modname='$data[modname]' AND entityname='$data[table]' AND property='$data[property]'";
            $row = DBUtil::selectObject($this->_objType, $where);
            if ($row) {
                $_SESSION['validationErrors'][$this->_objPath]['property'] = __('Error! There is already a property with this name in the specified module and table.');
                $_SESSION['validationFailedObjects'][$this->_objPath] = $this->_objData;

                return false;
            }
        }

        return true;
    }
}
