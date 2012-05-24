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

class Admin_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Administration panel');
        $meta['description']    = $this->__('Backend administration interface.');
        $meta['url']            = $this->__('adminpanel');
        $meta['version']        = '1.9.1';
        $meta['securityschema'] = array('Admin::' => 'Admin Category name::Admin Category ID');

        return $meta;
    }
}
