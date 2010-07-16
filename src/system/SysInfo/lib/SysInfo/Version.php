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

class SysInfo_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('System info');
        $meta['description']    = $this->__('Provides detailed information reports about the system configuration and environment, for tracking and troubleshooting purposes.');
        //! module name that appears in URL
        $meta['url']            = $this->__('sysinfo');
        $meta['version']        = '1.1.1';
        $meta['securityschema'] = array('SysInfo::' => '::');
        return $meta;
    }
}