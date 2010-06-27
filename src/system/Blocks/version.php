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


$modversion['name']           = 'Blocks';
$modversion['displayname']    = __('Blocks manager');
$modversion['description']    = __("Provides an interface for adding, removing and administering the site's side and center blocks.");
//! module name that appears in URL
$modversion['url']            = __('blocks');
$modversion['version']        = '3.7';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Jim McDonald, Mark West';
$modversion['contact']        = 'http://www.mcdee.net/, http://www.markwest.me.uk/';

$modversion['securityschema'] = array('Blocks::' => 'Block key:Block title:Block ID',
                                      'Blocks::position' => 'Position name::Position ID');
