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

class SecurityCenter_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Security Center');
        $meta['description']    = $this->__('Manage site security and settings.');
        //! module name that appears in URL
        $meta['url']            = $this->__('securitycenter');
        $meta['version']        = '1.4.4';
        $meta['securityschema'] = array('SecurityCenter::' => '::');

        return $meta;
    }
}
