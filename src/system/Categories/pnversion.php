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
 * @subpackage Categories
 */

$modversion['name']           = 'Categories';
$modversion['displayname']    = __('Categories manager');
$modversion['description']    = __('Provides support for categorisation of content in other modules, and an interface for adding, removing and administering categories.');
//! module name that appears in URL
$modversion['url']            = __('categories');
$modversion['version']        = '1.1';

$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Robert Gasch';
$modversion['contact']        = 'rgasch@gmail.com';
$modversion['admin']          = 1;
$modversion['user']           = 0;

$modversion['securityschema'] = array('Categories::Category' => 'Category ID:Category Path:Category IPath');

$modversion['dependencies']   = array(
                                      array('modname'    => 'ObjectData',
                                            'minversion' => '1.0',
                                            'maxversion' => '',
                                            'status'     => PNMODULE_DEPENDENCY_REQUIRED
                                           )
                                     );
