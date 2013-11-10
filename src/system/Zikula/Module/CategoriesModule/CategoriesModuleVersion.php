<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 * @package Zikula
 * @subpackage ZikulaCategoriesModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\CategoriesModule;

class CategoriesModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Categories');
        $meta['description']    = $this->__('Category administration.');
        //! module name that appears in URL
        $meta['url']            = $this->__('categories');
        $meta['version']        = '1.2.2';
        $meta['core_min'] = '1.3.7';
        $meta['securityschema'] = array('ZikulaCategoriesModule::Category' => 'Category ID:Category Path:Category IPath');

        return $meta;
    }
}
