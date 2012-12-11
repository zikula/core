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

class Settings_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['name']           = 'Settings';
        $meta['displayname']    = $this->__('General settings');
        $meta['description']    = $this->__('General site configuration interface.');
        //! module name that appears in URL
        $meta['url']            = $this->__('settings');
        $meta['version']        = '2.9.7';
        $meta['securityschema'] = array('Settings::' => '::');

        return $meta;
    }
}
