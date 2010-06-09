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


$modversion['name']           = 'Modules';
// need to keep these two notice suppressions for the benefit of the installer
// this is only relevent for this module, please do not replicate elsewhere, refs #980- drak
@$modversion['displayname']    = __('Modules manager');
@$modversion['description']    = __('Provides support for modules, and incorporates an interface for adding, removing and administering core system modules and add-on modules.');
//! module name that appears in URL
$modversion['url']            = __('modules');
$modversion['version']        = '3.7.1';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Jim McDonald, Mark West';
$modversion['contact']        = 'http://www.zikula.org';

$modversion['securityschema'] = array('Modules::' => '::');
