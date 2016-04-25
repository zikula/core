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
        $meta = array();
        $meta['displayname']    = $this->__('Groups');
        $meta['description']    = $this->__('User group administration module.');
        //! module name that appears in URL
        $meta['url']            = $this->__('groups');
        $meta['version']        = '2.3.2';
        $meta['core_min'] = '1.4.0';
        $meta['securityschema'] = array('ZikulaGroupsModule::' => 'Group ID::');

        return $meta;
    }
}
