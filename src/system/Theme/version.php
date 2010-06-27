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


$modversion['name']           = 'Theme';
$modversion['oldnames']       = array('Xanthia');
$modversion['displayname']    = __('Themes manager');
$modversion['description']    = __("Provides the site's theming system, and an interface for managing themes, to control the site's presentation and appearance.");
//! module name that appears in URL
$modversion['url']            = __('theme');
$modversion['version']        = '3.4';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Mark West';
$modversion['contact']        = 'http://www.markwest.me.uk/';

$modversion['securityschema'] = array('Theme::' => 'Theme name::');
