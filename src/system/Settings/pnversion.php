<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Settings
 */

$modversion['name']           = 'Settings';
$modversion['displayname']    = __('General settings');
$modversion['description']    = __("Provides an interface for managing the site's general settings, i.e. site start page settings, multi-lingual settings, error reporting options and various other features that are not administered within other modules.");
//! module name that appears in URL
$modversion['url']            = __('settings');
$modversion['version']        = '2.9.2';

$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Simon Wunderlin';
$modversion['contact']        = '';

$modversion['securityschema'] = array('Settings::' => '::');
