<?php
/**  ---------------------------------------------------------------------
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
 *  Purpose of file: class implementation
 *  Copyright: Value4Business GmbH
 *  ----------------------------------------------------------------------
 * @package Zikula_System_Modules
 * @subpackage SecurityCenter
 */


/**
 * PNLogEvent
 *
 * @package Zikula_System_Modules
 * @subpackage SecurityCenter
 */
class SecurityCenter_DBObject_LogEvent extends DBObject
{
    function __construct($init = null, $key = 0, $field = null)
    {

        $this->_objType       = 'sc_logevent';
        $this->_objField      = 'id';
        $this->_objPath       = 'logevent';

        $this->_objJoin[]     = array ('join_table'          =>  'users',
                                       'join_field'          =>  'uname',
                                       'object_field_name'   =>  'username',
                                       'compare_field_table' =>  'uid',
                                       'compare_field_join'  =>  'uid');

        $this->_init($init, $key);
    }
}
