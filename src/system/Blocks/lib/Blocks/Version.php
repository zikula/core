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

class Blocks_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Blocks manager');
        $meta['description']    = $this->__("Provides an interface for adding, removing and administering the site's side and center blocks.");
        //! module name that appears in URL
        $meta['url']            = $this->__('blocks');
        $meta['version']        = '3.8.0';

        $meta['securityschema'] = array('Blocks::' => 'Block key:Block title:Block ID',
                'Blocks::position' => 'Position name::Position ID');
        return $meta;
    }

}