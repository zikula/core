<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage PageLock
 */

$modversion['name']           = 'PageLock';
$modversion['displayname']    = __('Page lock');
$modversion['description']    = __('Provides the ability to lock pages when they are in use, for content and access control.');
//! module name that appears in URL
$modversion['url']            = __('pagelock');
$modversion['version']        = '1.1';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Jorn Wildt';
$modversion['contact']        = 'http://www.elfisk.dk';

$modversion['securityschema'] = array('PageLock::' => '::');
