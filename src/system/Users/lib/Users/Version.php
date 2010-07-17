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

class Users_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Users manager');
        $meta['description'] = $this->__('Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.');
        //! module name that appears in URL
        $meta['url']  = $this->__('users');
        $meta['capabilities'] = array('authentication' => array('version' => '1.0'));

        // Be careful about version numbers. version_compare() is used to handle special situations.
        // 0.9 < 0.9.0 < 1 < 1.0 < 1.0.1 < 1.2 < 1.18 < 1.20 < 2.0 < 2.0.0 < 2.0.1
        // From this version forward, please use the major.minor.point format below.
        $meta['version'] = '2.1.0';
        $meta['securityschema'] = array('Users::' => 'Uname::User ID',
                                        'Users::MailUsers' => '::');

        return $meta;
    }
}