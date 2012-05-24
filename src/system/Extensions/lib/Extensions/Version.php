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

class Extensions_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        // need to keep these two notice suppressions for the benefit of the installer
        // @ is only relevent for this module, please do not replicate elsewhere, refs #980- drak
        @$meta['displayname'] = $this->__('Extensions');
        @$meta['description'] = $this->__('Manage your modules and plugins.');
        //! module name that appears in URL
        $meta['url']  = $this->__('extensions');
        $meta['version'] = '3.7.10';
        $meta['securityschema'] = array('Extensions::' => '::');

        return $meta;
    }
}
