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

namespace Zikula\PageLockModule;

/**
 * Version information for the pagelock module
 */
class PageLockModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Page lock');
        $meta['description']    = $this->__('Provides the ability to lock pages when they are in use, for content and access control.');
        //! module name that appears in URL
        $meta['url']            = $this->__('pagelock');
        $meta['version']        = '1.1.1';
        $meta['core_min'] = '1.4.0';

        $meta['securityschema'] = array('ZikulaPageLockModule::' => '::');

        return $meta;
    }
}
