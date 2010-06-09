<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


$modversion['name']           = 'Workflow';
$modversion['displayname']    = __('Workflow engine');
$modversion['description']    = __('Provides a workflow engine, and an interface for designing and administering workflows comprised of actions and events.');
//! module name that appears in URL
$modversion['url']            = __('workflow');
$modversion['version']        = '1.1';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Drak';
$modversion['contact']        = 'drak@hostnuke.com';

$modversion['securityschema'] = array('Workflow::' => '::');
