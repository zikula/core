<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage AntiCracker
 */

$modversion['name']           = 'SecurityCenter';
$modversion['oldnames']       = array('AntiCracker');
$modversion['displayname']    = __('Security center');
$modversion['description']    = __('Provides the ability to manage site security. It logs attempted hacks and similar events, and incorporates a user interface for customising alerting and security settings.');
//! module name that appears in URL
$modversion['url']            = __('securitycenter');
$modversion['version']        = '1.4.1';

$modversion['official']       = 1;
$modversion['author']         = 'Mark West';
$modversion['contact']        = 'http://www.zikula.org';

$modversion['securityschema'] = array('SecurityCenter::' => 'hackid::hacktime');
