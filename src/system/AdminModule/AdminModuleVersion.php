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

namespace Zikula\AdminModule;

/**
 * Version information for the admin module
 */
class AdminModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Administration panel');
        $meta['description']    = $this->__('Backend administration interface.');
        $meta['url']            = $this->__('adminpanel');
        $meta['version']        = '1.9.2';
        $meta['core_min']       = '1.4.0';
        $meta['securityschema'] = array('ZikulaAdminModule::' => 'Admin Category name::Admin Category ID');

        return $meta;
    }
}
