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

$modversion['name']           = 'Categories';
$modversion['displayname']    = __('Categories manager');
$modversion['description']    = __('Provides support for categorisation of content in other modules, and an interface for adding, removing and administering categories.');
//! module name that appears in URL
$modversion['url']            = __('categories');
$modversion['version']        = '1.2';

$modversion['official']       = 1;
$modversion['author']         = 'Robert Gasch';
$modversion['contact']        = 'rgasch@gmail.com';
$modversion['admin']          = 1;
$modversion['user']           = 0;

$modversion['securityschema'] = array('Categories::Category' => 'Category ID:Category Path:Category IPath');

$modversion['dependencies']   = array(
                                      array('modname'    => 'ObjectData',
                                            'minversion' => '1.0',
                                            'maxversion' => '',
                                            'status'     => ModUtil::DEPENDENCY_REQUIRED
                                           )
                                     );
