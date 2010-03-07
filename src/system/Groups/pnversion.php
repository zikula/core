<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2003, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Groups
 */

$modversion['name']           = 'Groups';
$modversion['displayname']    = __('Groups manager');
$modversion['description']    = __('Provides support for user groups, and incorporates an interface for adding, removing and administering them.');
//! module name that appears in URL
$modversion['url']            = __('groups');
$modversion['version']        = '2.3';

$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Mark West, Franky Chestnut, Michael Halbook';
$modversion['contact']        = 'http://www.markwest.me.uk/, http://dev.pnconcept.com, http://www.halbrooktech.com';

$modversion['securityschema'] = array('Groups::' => 'Group ID::');
