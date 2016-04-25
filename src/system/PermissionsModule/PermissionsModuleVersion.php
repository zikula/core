<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule;

/**
 * Version information for the permissions module
 */
class PermissionsModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Permissions');
        $meta['description'] = $this->__('User permissions manager.');
        //! module name that appears in URL
        $meta['url'] = $this->__('permissions');
        $meta['version'] = '1.1.2';
        $meta['core_min'] = '1.4.0';
        $meta['securityschema'] = array('ZikulaPermissionsModule::' => '::');

        return $meta;
    }
}
