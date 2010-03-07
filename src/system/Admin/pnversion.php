<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Admin
 */

$modversion['name']           = 'Admin';
$modversion['displayname']    = __('Admin panel manager');
$modversion['description']    = __("Provides the site's administration panel, and the ability to configure and manage it.");
//! module name that appears in URL
$modversion['url']            = __('adminpanel');
$modversion['version']        = '1.8';

$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Mark West';
$modversion['contact']        = 'http://www.markwest.me.uk/';

$modversion['securityschema'] = array('Admin::' => 'Admin Category name::Admin Category ID');
