<?php
/**
 * Zikula Application Framework
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Modules
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
