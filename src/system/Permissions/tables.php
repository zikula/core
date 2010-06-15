<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * Permissions Module
 *
 * Purpose of file:  Table information for Permissions module --
 *                   This file contains all information on database
 *                   tables for the module
 *
 * @package Zikula_System_Modules
 * @subpackage   Permissions
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
function Permissions_tables()
{
    // Initialise table array
    $pntable = array();

    // Get the name for the Permissions item table.
    $group_perms = DBUtil::getLimitedTablename('group_perms');

    // Set the table name
    $pntable['group_perms'] = $group_perms;

    // Set the column names.
    $pntable['group_perms_column'] = array ('pid'       => 'pn_pid',
                                            'gid'       => 'pn_gid',
                                            'sequence'  => 'pn_sequence',
                                            'realm'     => 'pn_realm',
                                            'component' => 'pn_component',
                                            'instance'  => 'pn_instance',
                                            'level'     => 'pn_level',
                                            'bond'      => 'pn_bond');

    // column definitions
    $pntable['group_perms_column_def'] = array('pid'       => "I AUTO PRIMARY",
                                               'gid'       => "I NOTNULL DEFAULT 0",
                                               'sequence'  => "I NOTNULL DEFAULT 0",
                                               'realm'     => "I NOTNULL DEFAULT 0",
                                               'component' => "C(255) NOTNULL DEFAULT ''",
                                               'instance'  => "C(255) NOTNULL DEFAULT ''",
                                               'level'     => "I NOTNULL DEFAULT 0",
                                               'bond'      => "I NOTNULL DEFAULT 0");

    // Return the table information
    return $pntable;
}
