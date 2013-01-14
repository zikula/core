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

namespace Theme;

class ThemeVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Themes');
        $meta['description']    = $this->__('Themes module to manage site layout, render and cache settings.');
        //! module name that appears in URL
        $meta['url']            = $this->__('theme');
        $meta['version']        = '3.4.3';
        $meta['securityschema'] = array('Theme::' => 'Theme name::');

        return $meta;
    }
}