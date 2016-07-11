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
 * CategoryRegistryArray
 *
 * @deprecated
 */
class Categories_DBObject_RegistryArray extends DBObjectArray
{
    public function __construct($init = null, $where = '')
    {
        parent::__construct();

        $this->_objType = 'categories_registry';
        $this->_objField = 'id';
        $this->_objPath = 'categories_registry_array';

        $this->_init($init, $where);
    }
}
