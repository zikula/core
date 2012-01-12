<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * CategoryRegistryArray
 */
class Categories_DBObject_RegistryArray extends DBObjectArray
{
    public function __construct($init=null, $where='')
    {
        parent::__construct();

        $this->_objType = 'categories_registry';
        $this->_objField = 'id';
        $this->_objPath = 'categories_registry_array';

        $this->_init($init, $where);
    }

}
