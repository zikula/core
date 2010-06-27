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


$modversion['name']           = 'Admin';
$modversion['displayname']    = __('Administration panel');
$modversion['description']    = __("Provides the site's administration panel, and the ability to configure and manage it.");
//! module name that appears in URL
$modversion['url']            = __('adminpanel');
$modversion['version']        = '1.8';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Mark West';
$modversion['contact']        = 'http://www.markwest.me.uk/';

$modversion['securityschema'] = array('Admin::' => 'Admin Category name::Admin Category ID');
