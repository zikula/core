<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule;

/**
 * Version information for the categories module
 */
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
        $meta['core_min'] = '1.4.0';
        $meta['securityschema'] = array('ZikulaCategoriesModule::' => '::',
                                        'ZikulaCategoriesModule::Category' => 'Category ID:Category Path:Category IPath');

        return $meta;
    }
}
