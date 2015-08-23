<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Populate tables array for modules module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 *
 * @return  array The table information.
 */
function ZikulaExtensionsModule_tables()
{
    // Initialise table array
    $dbtable = array();

    // modules module
    $modules = 'modules';
    $dbtable['modules'] = $modules;
    $dbtable['modules_column'] = array(
            'id' => 'id',
            'name' => 'name',
            'type' => 'type',
            'displayname' => 'displayname',
            'url' => 'url',
            'description' => 'description',
            'directory' => 'directory',
            'version' => 'version',
            'capabilities' => 'capabilities',
            'state' => 'state',
            'securityschema' => 'securityschema',
            'core_min' => 'core_min',
            'core_max' => 'core_max',
    );

    // column definition
    $dbtable['modules_column_def'] = array(
            'id' => "I PRIMARY AUTO",
            'name' => "C(64) NOTNULL DEFAULT ''",
            'type' => "I1 NOTNULL DEFAULT 0",
            'displayname' => "C(64) NOTNULL DEFAULT ''",
            'url' => "C(64) NOTNULL DEFAULT ''",
            'description' => "C(255) NOTNULL DEFAULT ''",
            'directory' => "C(64) NOTNULL DEFAULT ''",
            'version' => "C(10) NOTNULL DEFAULT 0",
            'capabilities' => "X NOTNULL DEFAULT ''",
            'state' => "I2 NOTNULL DEFAULT 0",
            'securityschema' => "X NOTNULL DEFAULT ''",
            'core_min' => "C(9) NOTNULL DEFAULT ''",
            'core_max' => "C(9) NOTNULL DEFAULT ''",
    );

    // additional indexes
    $dbtable['modules_column_idx'] = array('state' => 'state',
            'mod_state' => array('name', 'state'));

    $module_vars = 'module_vars';
    $dbtable['module_vars'] = $module_vars;
    $dbtable['module_vars_column'] = array(
            'id' => 'id',
            'modname' => 'modname',
            'name' => 'name',
            'value' => 'value');

    // column definition
    $dbtable['module_vars_column_def'] = array(
            'id' => "I PRIMARY AUTO",
            'modname' => "C(64) NOTNULL DEFAULT ''",
            'name' => "C(64) NOTNULL DEFAULT ''",
            'value' => "XL");

    // additional indexes
    $dbtable['module_vars_column_idx'] = array('mod_var' => array('modname', 'name'));

    // dependencies table
    $module_deps = 'module_deps';
    $dbtable['module_deps'] = $module_deps;
    $dbtable['module_deps_column'] = array(
            'id' => 'id',
            'modid' => 'modid',
            'modname' => 'modname',
            'minversion' => 'minversion',
            'maxversion' => 'maxversion',
            'status' => 'status');

    // column definition
    $dbtable['module_deps_column_def'] = array(
            'id' => "I4 PRIMARY AUTO",
            'modid' => "I NOTNULL DEFAULT 0",
            'modname' => "C(64) NOTNULL DEFAULT ''",
            'minversion' => "C(10) NOTNULL DEFAULT ''",
            'maxversion' => "C(10) NOTNULL DEFAULT ''",
            'status' => "I1 NOTNULL DEFAULT 0");

    return $dbtable;
}
