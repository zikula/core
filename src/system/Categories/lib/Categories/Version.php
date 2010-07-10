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

class Categories_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['name']           = 'Categories';
        $meta['displayname']    = $this->__('Categories manager');
        $meta['description']    = $this->__('Provides support for categorisation of content in other modules, and an interface for adding, removing and administering categories.');
        //! module name that appears in URL
        $meta['url']            = $this->__('categories');
        $meta['version']        = '1.2.0';
        $meta['contact']        = 'http://zikula.org/';
        $meta['securityschema'] = array('Categories::Category' => 'Category ID:Category Path:Category IPath');
        return $meta;
    }
}