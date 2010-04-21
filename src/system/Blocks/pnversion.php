<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

$modversion['name']           = 'Blocks';
$modversion['displayname']    = __('Blocks manager');
$modversion['description']    = __("Provides an interface for adding, removing and administering the site's side and center blocks.");
//! module name that appears in URL
$modversion['url']            = __('blocks');
$modversion['version']        = '3.6';

$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'Jim McDonald, Mark West';
$modversion['contact']        = 'http://www.mcdee.net/, http://www.markwest.me.uk/';

$modversion['securityschema'] = array('Blocks::' => 'Block key:Block title:Block ID',
                                      'Blocks::position' => 'Position name::Position ID');
