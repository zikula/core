<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule;

/**
 * Version information for the groups module
 */
class GroupsModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = [
            'displayname' => $this->__('Groups'),
            'description' => $this->__('User group administration module.'),
            //! module name that appears in URL
            'url' => $this->__('groups'),
            'version' => '2.3.2',
            'core_min' => '1.4.0',
            'securityschema' => [
                'ZikulaGroupsModule::' => 'Group ID::'
            ]
        ];

        return $meta;
    }
}
