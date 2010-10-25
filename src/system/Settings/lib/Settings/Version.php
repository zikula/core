<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Settings_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['name']           = 'Settings';
        $meta['displayname']    = $this->__('General settings');
        $meta['description']    = $this->__("Provides an interface for managing the site's general settings, i.e. site start page settings, multi-lingual settings, error reporting options and various other features that are not administered within other modules.");
        //! module name that appears in URL
        $meta['url']            = $this->__('settings');
        $meta['version']        = '2.9.5';
        $meta['securityschema'] = array('Settings::' => '::');
        return $meta;
    }
}
