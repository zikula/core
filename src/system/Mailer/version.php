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


$modversion['name']           = 'Mailer';
$modversion['displayname']    = __('Mailer');
$modversion['description']    = __("Provides mail-sending functionality for communication with the site's users, and an interface for managing the e-mail service settings used by the mailer.");
//! module name that appears in URL
$modversion['url']            = __('mailer');
$modversion['version']        = '1.3';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Mark West';
$modversion['contact']        = 'http://www.markwest.me.uk/';

$modversion['securityschema'] = array('Mailer::' => '::');
