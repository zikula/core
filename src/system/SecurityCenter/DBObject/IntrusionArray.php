<?php
/** ----------------------------------------------------------------------
 *  LICENSE
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License (GPL)
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WIthOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  To read the license please visit http://www.gnu.org/copyleft/gpl.html
 *  ----------------------------------------------------------------------
 *  Original Author of  OpenStar Module Generator
 *  Author Contact: r.gasch@chello.nl, robert.gasch@value4business.com
 *  Purpose of file:  object array class implementation
 *  Copyright: Value4Business GmbH
 *  ----------------------------------------------------------------------
 * @package Zikula_System_Modules
 * @subpackage SecurityCenter
 */


/**
 * PNIntrusionArray
 *
 * @package Zikula_System_Modules
 * @subpackage SecurityCenter
 */
class SecurityCenter_DBObject_IntrusionArray extends DBObjectArray
{
    function PNIntrusionArray($init = null, $where = '')
    {
        $this->PNObjectArray();

        $this->_objType       = 'sc_intrusion';
        $this->_objField      = 'id';
        $this->_objPath       = 'intrusion_array';

        $this->_objJoin[]     = array('join_table'          =>  'users',
                                      'join_field'          =>  'uname',
                                      'object_field_name'   =>  'username',
                                      'compare_field_table' =>  'uid',
                                      'compare_field_join'  =>  'uid');

        $this->_init($init, $where);
    }


    function genFilter($filter = array())
    {
        $wheres = array();
        $filterFields = array('name', 'tag', 'value', 'page', 'uid', 'username', 'ip', 'impact', 'date');

        foreach($filterFields as $fieldName) {
            if (isset($filter[$fieldName]) && $filter[$fieldName]) {
                $wheres[] = "ids_" . $fieldName . " = '" . $filter[$fieldName] . "'";
            }
        }

        $where = implode (' AND ', $wheres);
        return $where;
    }
}
