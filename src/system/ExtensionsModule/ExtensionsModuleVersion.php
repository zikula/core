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

namespace Zikula\ExtensionsModule;

/**
 * Version information for the extensions module
 */
class ExtensionsModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = array();
        // need to keep these two notice suppressions for the benefit of the installer
        // @ is only relevent for this module, please do not replicate elsewhere, refs #980- drak
        @$meta['displayname'] = $this->__('Extensions');
        @$meta['description'] = $this->__('Manage your modules and plugins.');
        //! module name that appears in URL
        $meta['url']  = $this->__('extensions');
        $meta['version'] = '3.7.12';
        $meta['core_min'] = '1.4.0';
        $meta['securityschema'] = array('ZikulaExtensionsModule::' => '::');

        return $meta;
    }
}
