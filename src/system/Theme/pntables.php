<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Modules
*/


/**
 * Populate pntables array for modules module
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the pnModDBInfoLoad() API function.
 *
 * @return       array       The table information.
 */
function theme_pntables()
{
    // Initialise table array
    $pntable = array();

    $prefix = pnConfigGetVar('prefix');

    // note: we can't share the themes table since a module function can be overriden in the theme
    // so pnModFuncExec gets the current active theme. This needs the theme table info so we've a
    // catch 22....
    $themes = $prefix . '_themes';
    $pntable['themes'] = $themes;
    $pntable['themes_column'] = array ('id'             => 'pn_id',
                                       'name'           => 'pn_name',
                                       'type'           => 'pn_type',
                                       'displayname'    => 'pn_displayname',
                                       'description'    => 'pn_description',
                                       'regid'          => 'pn_regid',
                                       'directory'      => 'pn_directory',
                                       'version'        => 'pn_version',
                                       'official'       => 'pn_official',
                                       'author'         => 'pn_author',
                                       'contact'        => 'pn_contact',
                                       'admin'          => 'pn_admin',
                                       'user'           => 'pn_user',
                                       'system'         => 'pn_system',
                                       'state'          => 'pn_state',
                                       'credits'        => 'pn_credits',
                                       'changelog'      => 'pn_changelog',
                                       'help'           => 'pn_help',
                                       'license'        => 'pn_license',
                                       'xhtml'          => 'pn_xhtml');

    $pntable['themes_column_def'] = array('id'          => "I PRIMARY AUTO",
                                          'name'        => "C(64) NOTNULL DEFAULT ''",
                                          'type'        => "I1 NOTNULL DEFAULT 0",
                                          'displayname' => "C(64) NOTNULL DEFAULT ''",
                                          'description' => "C(255) NOTNULL DEFAULT ''",
                                          'regid'       => "I NOTNULL DEFAULT 0",
                                          'directory'   => "C(64) NOTNULL DEFAULT ''",
                                          'version'     => "C(10) NOTNULL DEFAULT 0",
                                          'official'    => "I1 NOTNULL DEFAULT 0",
                                          'author'      => "C(255) NOTNULL DEFAULT ''",
                                          'contact'     => "C(255) NOTNULL DEFAULT ''",
                                          'admin'       => "I1 NOTNULL DEFAULT 0",
                                          'user'        => "I1 NOTNULL DEFAULT 0",
                                          'system'      => "I1 NOTNULL DEFAULT 0",
                                          'state'       => "I1 NOTNULL DEFAULT 0",
                                          'credits'     => "C(255) NOTNULL DEFAULT ''",
                                          'help'        => "C(255) NOTNULL DEFAULT ''",
                                          'changelog'   => "C(255) NOTNULL DEFAULT ''",
                                          'license'     => "C(255) NOTNULL DEFAULT ''",
                                          'xhtml'       => "I1 NOTNULL DEFAULT 1");

    // legacy tables for upgrade
    // like the themes table these cannot defined using DBUtil::getLimitedTableName
    $pntable['theme_config'] = $prefix . '_theme_config';
    $pntable['theme_layout'] = $prefix . '_theme_layout';
    $pntable['theme_skins'] = $prefix . '_theme_skins';
    $pntable['theme_palette'] = $prefix . '_theme_palette';
    $pntable['theme_zones'] = $prefix . '_theme_zones';
    $pntable['theme_cache'] = $prefix . '_theme_cache';
    $pntable['theme_blcontrol'] = $prefix . '_theme_blcontrol';
    $pntable['theme_addons'] = $prefix . '_theme_addons';
    $pntable['theme_tplfile'] = $prefix . '_theme_tplfile';
    $pntable['theme_tplsource'] = $prefix . '_theme_tplsource';

    return $pntable;
}
