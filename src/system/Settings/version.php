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


$modversion['name']           = 'Settings';
$modversion['displayname']    = __('General settings');
$modversion['description']    = __("Provides an interface for managing the site's general settings, i.e. site start page settings, multi-lingual settings, error reporting options and various other features that are not administered within other modules.");
//! module name that appears in URL
$modversion['url']            = __('settings');
$modversion['version']        = '2.9.3';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Simon Wunderlin';
$modversion['contact']        = '';

$modversion['securityschema'] = array('Settings::' => '::');
