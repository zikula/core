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

class Errors_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Errors');
        $meta['description']    = $this->__('Provides the core system of the site with error-logging capability.');
        //! module name that appears in URL
        $meta['url']            = $this->__('errors');
        $meta['version']        = '1.1.1';
        $meta['securityschema'] = array('Errors::' => '::');
        return $meta;
    }
}