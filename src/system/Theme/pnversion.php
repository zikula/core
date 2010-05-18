<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Theme
 */

$modversion['name']           = 'Theme';
$modversion['oldnames']       = array('Xanthia');
$modversion['displayname']    = __('Themes manager');
$modversion['description']    = __("Provides the site's theming system, and an interface for managing themes, to control the site's presentation and appearance.");
//! module name that appears in URL
$modversion['url']            = __('theme');
$modversion['version']        = '3.4';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Mark West';
$modversion['contact']        = 'http://www.markwest.me.uk/';

$modversion['securityschema'] = array('Theme::' => 'Theme name::');
