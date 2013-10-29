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

namespace Zikula\Module\SearchModule;

class SearchModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Site search');
        $meta['description']    = $this->__('Site search module.');
        //! module name that appears in URL
        $meta['url']            = $this->__('search');
        $meta['version']        = '1.5.3';
        $meta['core_min'] = '1.3.7';

        $meta['securityschema'] = array('ZikulaSearchModule::' => 'Module name::');

        return $meta;
    }
}