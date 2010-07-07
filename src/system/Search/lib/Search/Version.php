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

class Search_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Site search engine');
        $meta['description']    = $this->__('Provides an engine for searching within the site, and an interface for managing search page settings.');
        //! module name that appears in URL
        $meta['url']            = $this->__('search');
        $meta['version']        = '1.5.1';
        $meta['contact']        = 'http://zikula.org/';

        $meta['securityschema'] = array('Search::' => 'Module name::');
        return $meta;
    }
}