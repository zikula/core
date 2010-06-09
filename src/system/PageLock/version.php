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


$modversion['name']           = 'PageLock';
$modversion['displayname']    = __('Page lock');
$modversion['description']    = __('Provides the ability to lock pages when they are in use, for content and access control.');
//! module name that appears in URL
$modversion['url']            = __('pagelock');
$modversion['version']        = '1.1';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Jorn Wildt';
$modversion['contact']        = 'http://www.elfisk.dk';

$modversion['securityschema'] = array('PageLock::' => '::');
