<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Mailer
 */

$modversion['name']           = 'Mailer';
$modversion['displayname']    = __('Mailer');
$modversion['description']    = __("Provides mail-sending functionality for communication with the site's users, and an interface for managing the e-mail service settings used by the mailer.");
//! module name that appears in URL
$modversion['url']            = __('mailer');
$modversion['version']        = '1.3';

$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Mark West';
$modversion['contact']        = 'http://www.markwest.me.uk/';

$modversion['securityschema'] = array('Mailer::' => '::');
