<?php
/**
 * Copyright Zikula Foundation 2002 - Zikula Application Framework
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
 * Populate pntables array for Permissions module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @return       array       The table information.
 */
function ZikulaPermissionsModule_tables()
{
    // Initialise table array
    $dbtable = array();

    // Get the name for the Permissions item table.
    $group_perms = 'group_perms';

    // Set the table name
    $dbtable['group_perms'] = $group_perms;

    // Set the column names.
    $dbtable['group_perms_column'] = array ('pid'       => 'pid',
                                            'gid'       => 'gid',
                                            'sequence'  => 'sequence',
                                            'realm'     => 'realm',
                                            'component' => 'component',
                                            'instance'  => 'instance',
                                            'level'     => 'level',
                                            'bond'      => 'bond');

    // column definitions
    $dbtable['group_perms_column_def'] = array('pid'       => "I AUTO PRIMARY",
                                               'gid'       => "I NOTNULL DEFAULT 0",
                                               'sequence'  => "I NOTNULL DEFAULT 0",
                                               'realm'     => "I NOTNULL DEFAULT 0",
                                               'component' => "C(255) NOTNULL DEFAULT ''",
                                               'instance'  => "C(255) NOTNULL DEFAULT ''",
                                               'level'     => "I NOTNULL DEFAULT 0",
                                               'bond'      => "I NOTNULL DEFAULT 0");

    // Return the table information
    return $dbtable;
}
