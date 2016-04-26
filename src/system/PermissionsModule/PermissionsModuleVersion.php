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
        $meta = [
            'displayname' => $this->__('Permissions'),
            'description' => $this->__('User permissions manager.'),
            //! module name that appears in URL
            'url' => $this->__('permissions'),
            'version' => '1.1.2',
            'core_min' => '1.4.0',
            'securityschema' => [
                'ZikulaPermissionsModule::' => '::'
            ]
        ];

        return $meta;
    }
}
