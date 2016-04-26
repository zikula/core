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
        $meta = [
            'displayname' => $this->__('Categories'),
            'description' => $this->__('Category administration.'),
            //! module name that appears in URL
            'url' => $this->__('categories'),
            'version' => '1.2.2',
            'core_min' => '1.4.0',
            'securityschema' => [
                'ZikulaCategoriesModule::' => '::',
                'ZikulaCategoriesModule::Category' => 'Category ID:Category Path:Category IPath'
            ]
        ];

        return $meta;
    }
}
