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

namespace Zikula\SettingsModule;

/**
 * Version information for the settings module
 */
class SettingsModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('General settings');
        $meta['description'] = $this->__('General site configuration interface.');
        //! module name that appears in URL
        $meta['url'] = $this->__('settings');
        $meta['version'] = '2.9.11';
        $meta['core_min'] = '1.4.2';
        $meta['securityschema'] = array('ZikulaSettingsModule::' => '::');

        return $meta;
    }
}
