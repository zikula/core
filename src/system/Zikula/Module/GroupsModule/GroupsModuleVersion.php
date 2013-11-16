<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\GroupsModule;

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
        $meta['core_min'] = '1.3.6';
        $meta['securityschema'] = array('ZikulaGroupsModule::' => 'Group ID::');

        return $meta;
    }
}