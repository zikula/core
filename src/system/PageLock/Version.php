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

class PageLock_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Page lock');
        $meta['description']    = $this->__('Provides the ability to lock pages when they are in use, for content and access control.');
        //! module name that appears in URL
        $meta['url']            = $this->__('pagelock');
        $meta['version']        = '1.1.1';

        $meta['securityschema'] = array('PageLock::' => '::');

        return $meta;
    }
}
