<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 */

$modversion['name']           = 'Users';
$modversion['oldnames']       = array('User');
$modversion['displayname']    = __('Users manager');
$modversion['description']    = __('Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.');
//! module name that appears in URL
$modversion['url']            = __('users');

// Be careful about version numbers. version_compare() is used to handle special situations.
// 0.9 < 0.9.0 < 1 < 1.0 < 1.0.1 < 1.2 < 1.18 < 1.20 < 2.0 < 2.0.0 < 2.0.1
// From this version forward, please use the major.minor.point format below.
$modversion['version']        = '2.0.0';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Xiaoyu Huang, Drak';
$modversion['contact']        = 'class007@sina.com, drak@zikula.org';

$modversion['securityschema'] = array('Users::'          => 'Uname::User ID',
                                      'Users::MailUsers' => '::');
