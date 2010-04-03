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
$modversion['version']        = '1.16';

$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Xiaoyu Huang, Drak';
$modversion['contact']        = 'class007@sina.com, drak@zikula.org';

$modversion['securityschema'] = array('Users::'          => 'Uname::User ID',
                                      'Users::MailUsers' => '::');
