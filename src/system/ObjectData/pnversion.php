<?php
/**
 * Zikula Application Framework
 *
 * @copyright Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 * @subpackage ObjectData
 */

$modversion['name']           = 'ObjectData';
$modversion['displayname']    = __('Object data');
$modversion['description']    = __('Provides a framework for implementing and managing object-model data items, for use by other modules and applications.');
//! module name that appears in URL
$modversion['url']            = __('objectdata');
$modversion['version']        = '1.03';

$modversion['credits']        = 'docs/credits.txt';
$modversion['help']           = 'docs/help.txt';
$modversion['changelog']      = 'docs/changelog.txt';
$modversion['license']        = 'docs/license.txt';
$modversion['official']       = 0;
$modversion['author']         = 'Robert Gasch';
$modversion['contact']        = 'rgasch@gmail.com';

$modversion['securityschema'] = array('ObjectData::' => '::');
