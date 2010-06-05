<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

// retrieve gettext domain for this module
$dom = ZLanguage::getModuleDomain('ExampleDoctrine');

// define the name of the module
$modversion['name']           = 'ExampleDoctrine';
// define the displayed name of the module
$modversion['displayname']    = __('ExampleDoctrine', $dom);
// define the module description
$modversion['description']    = __('ExampleDoctrine module.', $dom);
// url version of name
$modversion['url']            = __('exampledoctrine', $dom);
// define the current module version
$modversion['version']        = '1.0';

// this is no official core / system module
$modversion['official']       = 0;
// the module author
$modversion['author']         = 'Drak';
// module homepage
$modversion['contact']        = 'drak@zikula.org';

// we do have an admin area
$modversion['admin']          = 1;
// we do not have a user area
$modversion['user']           = 1;

// permission schema
$modversion['securityschema'] = array('ExampleDoctrine::' => '::',
'ExampleDoctrine:User:' => 'UserName::');



