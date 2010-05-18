<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Modules
 */


/**
 * Populate pntables array for modules module
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @author       Frank Schummertz
 * @return       array       The table information.
 */
function Modules_pntables()
{
    // Initialise table array
    $pntable = array();
    $prefix = System::getVar('prefix');

    // modules module
    $hooks = $prefix . '_hooks';
    $pntable['hooks'] = $hooks;
    $pntable['hooks_column'] = array ('id'        => 'pn_id',
                                      'object'    => 'pn_object',
                                      'action'    => 'pn_action',
                                      'smodule'   => 'pn_smodule',
                                      'stype'     => 'pn_stype',
                                      'tarea'     => 'pn_tarea',
                                      'tmodule'   => 'pn_tmodule',
                                      'ttype'     => 'pn_ttype',
                                      'tfunc'     => 'pn_tfunc',
                                      'sequence'  => 'pn_sequence');

    // column definition
    $pntable['hooks_column_def'] = array('id'        => 'I AUTO PRIMARY',
                                         'object'    => "C(64) NOTNULL DEFAULT ''",
                                         'action'    => "C(64) NOTNULL DEFAULT ''",
                                         'smodule'   => "C(64) NOTNULL DEFAULT ''",
                                         'stype'     => "C(64) NOTNULL DEFAULT ''",
                                         'tarea'     => "C(64) NOTNULL DEFAULT ''",
                                         'tmodule'   => "C(64) NOTNULL DEFAULT ''",
                                         'ttype'     => "C(64) NOTNULL DEFAULT ''",
                                         'tfunc'     => "C(64) NOTNULL DEFAULT ''",
                                         'sequence'  => "I NOTNULL DEFAULT 0");

    // additional indexes
    $pntable['hooks_column_idx'] = array ('smodule'         => 'smodule',
                                          'smodule_tmodule' => array('smodule', 'tmodule'));

    // A bit of magic to handle upgrades from 0.76x to 0.8x
    $modules = $prefix . '_modules';
    $pntable['modules'] = $modules;
    $pntable['modules_column'] = Modules_pntables_detectversion();

    // column definition
    $pntable['modules_column_def'] = array ('id'             => "I PRIMARY AUTO",
                                            'name'           => "C(64) NOTNULL DEFAULT ''",
                                            'type'           => "I1 NOTNULL DEFAULT 0",
                                            'displayname'    => "C(64) NOTNULL DEFAULT ''",
                                            'url'            => "C(64) NOTNULL DEFAULT ''",
                                            'description'    => "C(255) NOTNULL DEFAULT ''",
                                            'regid'          => "I NOTNULL DEFAULT 0",
                                            'directory'      => "C(64) NOTNULL DEFAULT ''",
                                            'version'        => "C(10) NOTNULL DEFAULT 0",
                                            'official'       => "I1 NOTNULL DEFAULT 0",
                                            'author'         => "C(255) NOTNULL DEFAULT ''",
                                            'contact'        => "C(255) NOTNULL DEFAULT ''",
                                            'admin_capable'  => "I1 NOTNULL DEFAULT 0",
                                            'user_capable'   => "I1 NOTNULL DEFAULT 0",
                                            'profile_capable'=> "I1 NOTNULL DEFAULT 0",
                                            'message_capable'=> "I1 NOTNULL DEFAULT 0",
                                            'state'          => "I2 NOTNULL DEFAULT 0",
                                            'credits'        => "C(255) NOTNULL DEFAULT ''",
                                            'changelog'      => "C(255) NOTNULL DEFAULT ''",
                                            'help'           => "C(255) NOTNULL DEFAULT ''",
                                            'license'        => "C(255) NOTNULL DEFAULT ''",
                                            'securityschema' => "X NOTNULL DEFAULT ''");

    // additional indexes
    $pntable['modules_column_idx'] = array ('state'        => 'state',
                                            'mod_state'    => array('name', 'state'));

    $module_vars = $prefix . '_module_vars';
    $pntable['module_vars'] = $module_vars;
    $pntable['module_vars_column'] = array ('id'      => 'pn_id',
                                            'modname' => 'pn_modname',
                                            'name'    => 'pn_name',
                                            'value'   => 'pn_value');

    // column definition
    $pntable['module_vars_column_def'] = array('id'      => "I PRIMARY AUTO",
                                               'modname' => "C(64) NOTNULL DEFAULT ''",
                                               'name'    => "C(64) NOTNULL DEFAULT ''",
                                               'value'   => "XL");

    // additional indexes
    $pntable['module_vars_column_idx'] = array ('mod_var' => array('modname', 'name'));


    //$module_dependencies = DBUtil::getLimitedTablename('module_deps');
    $module_deps = $prefix . '_module_deps';
    $pntable['module_deps'] = $module_deps;
    $pntable['module_deps_column'] = array ('id'          => 'pn_id',
                                            'modid'       => 'pn_modid',
                                            'modname'     => 'pn_modname',
                                            'minversion'  => 'pn_minversion',
                                            'maxversion'  => 'pn_maxversion',
                                            'status'      => 'pn_status');

    // column definition
    $pntable['module_deps_column_def'] = array('id'         => "I4 PRIMARY AUTO",
                                               'modid'      => "I NOTNULL DEFAULT 0",
                                               'modname'    => "C(64) NOTNULL DEFAULT ''",
                                               'minversion' => "C(10) NOTNULL DEFAULT ''",
                                               'maxversion' => "C(10) NOTNULL DEFAULT ''",
                                               'status'     => "I1 NOTNULL DEFAULT 0");

    return $pntable;
}


function Modules_pntables_detectversion()
{
    if (isset($_SESSION['_ZUpgrader']['_ZUpgradeFrom76x'])) {
        return array ('id'             => 'pn_id',
                      'name'           => 'pn_name',
                      'type'           => 'pn_type',
                      'displayname'    => 'pn_displayname',
                      'description'    => 'pn_description',
                      'regid'          => 'pn_regid',
                      'directory'      => 'pn_directory',
                      'version'        => 'pn_version',
                      'admin_capable'  => 'pn_admin_capable',
                      'user_capable'   => 'pn_user_capable',
                      'state'          => 'pn_state');
    } else if (isset($_SESSION['_ZikulaUpgrader']['_ZikulaUpgradeFrom10x'])) {
        return array ('id'             => 'pn_id',
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
                      'admin_capable'  => 'pn_admin_capable',
                      'user_capable'   => 'pn_user_capable',
                      'state'          => 'pn_state',
                      'credits'        => 'pn_credits',
                      'changelog'      => 'pn_changelog',
                      'help'           => 'pn_help',
                      'license'        => 'pn_license',
                      'securityschema' => 'pn_securityschema');
    } else if (isset($_SESSION['_ZikulaUpgrader']['_ZikulaUpgradeFrom110'])) {
        return array ('id'             => 'pn_id',
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
                      'admin_capable'  => 'pn_admin_capable',
                      'user_capable'   => 'pn_user_capable',
                      'profile_capable'=> 'pn_profile_capable',
                      'message_capable'=> 'pn_message_capable',
                      'state'          => 'pn_state',
                      'credits'        => 'pn_credits',
                      'changelog'      => 'pn_changelog',
                      'help'           => 'pn_help',
                      'license'        => 'pn_license',
                      'securityschema' => 'pn_securityschema');
    } else {
        return array ('id'             => 'pn_id',
                      'name'           => 'pn_name',
                      'type'           => 'pn_type',
                      'displayname'    => 'pn_displayname',
                      'url'            => 'pn_url',
                      'description'    => 'pn_description',
                      'regid'          => 'pn_regid',
                      'directory'      => 'pn_directory',
                      'version'        => 'pn_version',
                      'official'       => 'pn_official',
                      'author'         => 'pn_author',
                      'contact'        => 'pn_contact',
                      'admin_capable'  => 'pn_admin_capable',
                      'user_capable'   => 'pn_user_capable',
                      'profile_capable'=> 'pn_profile_capable',
                      'message_capable'=> 'pn_message_capable',
                      'state'          => 'pn_state',
                      'credits'        => 'pn_credits',
                      'changelog'      => 'pn_changelog',
                      'help'           => 'pn_help',
                      'license'        => 'pn_license',
                      'securityschema' => 'pn_securityschema');
    }
}

