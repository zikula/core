<?php
/**
 * Zikula Application Framework
 *
 * @copyright value4business GmbH
 * @link http://www.zikula.org
 * @version $Id: PNCategoryRegistryArray.class.php 20307 2006-10-14 21:06:59Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */


/**
 * PNCategoryRegistryArray
 *
 * @package Zikula_System_Modules
 * @subpackage Categories
 */
class PNCategoryRegistryArray extends DBObjectArray
{
    function PNCategoryRegistryArray($init=null, $where='')
    {
        $this->DBObjectArray ();

        $this->_objType       = 'categories_registry';
        $this->_objField      = 'id';
        $this->_objPath       = 'categories_registry_array';

        $this->_init($init, $where);
    }
}
