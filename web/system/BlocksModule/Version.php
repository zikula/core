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

namespace BlocksModule;

class Version extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Blocks');
        $meta['description']    = $this->__('Block administration module.');
        $meta['url']            = $this->__('blocks');
        $meta['version']        = '3.8.1';
        $meta['securityschema'] = array(
            'Blocks::' => 'Block key:Block title:Block ID',
            'Blocks::position' => 'Position name::Position ID',
            'Menutree:menutreeblock:' => 'Block ID:Link Name:Link ID',
            'ExtendedMenublock::' => 'Block ID:Link ID:');
        
        return $meta;
    }

}