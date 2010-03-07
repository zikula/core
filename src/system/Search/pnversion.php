<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Search
 */

$modversion['name']           = 'Search';
$modversion['displayname']    = __('Site search engine');
$modversion['description']    = __('Provides an engine for searching within the site, and an interface for managing search page settings.');
//! module name that appears in URL
$modversion['url']            = __('search');
$modversion['version']        = '1.5';

$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/install.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Patrick Kellum';
$modversion['contact']        = 'http://www.ctarl-ctarl.com';

$modversion['securityschema'] = array('Search::' => 'Module name::');
