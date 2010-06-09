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

$modversion['name']           = 'SecurityCenter';
$modversion['oldnames']       = array('AntiCracker');
$modversion['displayname']    = __('Security center');
$modversion['description']    = __('Provides the ability to manage site security. It logs attempted hacks and similar events, and incorporates a user interface for customising alerting and security settings.');
//! module name that appears in URL
$modversion['url']            = __('securitycenter');
$modversion['version']        = '1.4.1';

$modversion['official']       = 1;
$modversion['author']         = 'Mark West';
$modversion['contact']        = 'http://www.zikula.org';

$modversion['securityschema'] = array('SecurityCenter::' => 'hackid::hacktime');
