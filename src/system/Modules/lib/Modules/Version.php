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

class Modules_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['name'] = 'Modules';
        // need to keep these two notice suppressions for the benefit of the installer
        // @ is only relevent for this module, please do not replicate elsewhere, refs #980- drak
        @$meta['displayname'] = $this->__('Modules manager');
        @$meta['description'] = $this->__('Provides support for modules, and incorporates an interface for adding, removing and administering core system modules and add-on modules.');
        //! module name that appears in URL
        $meta['url']  = $this->__('modules');
        $meta['version'] = '3.7.3';
        $meta['securityschema'] = array('Modules::' => '::');
        return $meta;
    }
}
