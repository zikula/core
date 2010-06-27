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


$modversion['name']           = 'SysInfo';
$modversion['displayname']    = __('System info');
$modversion['description']    = __('Provides detailed information reports about the system configuration and environment, for tracking and troubleshooting purposes.');
//! module name that appears in URL
$modversion['url']            = __('sysinfo');
$modversion['version']        = '1.1';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Simon Birtwistle';
$modversion['contact']        = 'hammerhead@zikula.org';

$modversion['securityschema'] = array('SysInfo::' => '::');
