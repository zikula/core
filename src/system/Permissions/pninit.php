<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Permissions
 * @license http://www.gnu.org/copyleft/gpl.html
 */

/**
 * initialise the permissions module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance.
 * This function MUST exist in the pninit file for a module
 *
 * @return       bool       true on success, false otherwise
 */
function permissions_init()
{
    if (!DBUtil::createTable('group_perms')) {
        return false;
    }

    // Create any default for this module
    permissions_defaultdata();
    // Initialisation successful
    return true;
}

/**
 * upgrade the permissions module from an old version
 *
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function permissions_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch ($oldversion)
    {
        case '1.1':
            // future upgrade routines
    }

    // Update successful
    return true;
}

/**
 * delete the permissions module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * Since the permissions module should never be deleted we'all always return false here
 *
 * @return       bool       false
 */
function permissions_delete()
{
    // Deletion not allowed
    return false;
}

/**
 * create the default data for the permissions module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @return       bool       false
 */
function permissions_defaultdata()
{
    $record = array();
    $record['gid']       = '2';
    $record['sequence']  = '1';
    $record['realm']     = '0';
    $record['component'] = '.*';
    $record['instance']  = '.*';
    $record['level']     = '800';
    $record['bond']      = '0';
    DBUtil::insertObject($record, 'group_perms', 'pid');

    $record = array();
    $record['gid']       = '-1';
    $record['sequence']  = '2';
    $record['realm']     = '0';
    $record['component'] = 'ExtendedMenublock::';
    $record['instance']  = '1:1:';
    $record['level']     = '0';
    $record['bond']      = '0';
    DBUtil::insertObject($record, 'group_perms', 'pid');

    $record = array();
    $record['gid']       = '1';
    $record['sequence']  = '3';
    $record['realm']     = '0';
    $record['component'] = '.*';
    $record['instance']  = '.*';
    $record['level']     = '300';
    $record['bond']      = '0';
    DBUtil::insertObject($record, 'group_perms', 'pid');

    $record = array();
    $record['gid']       = '0';
    $record['sequence']  = '4';
    $record['realm']     = '0';
    $record['component'] = 'ExtendedMenublock::';
    $record['instance']  = '1:(1|2|3):';
    $record['level']     = '0';
    $record['bond']      = '0';
    DBUtil::insertObject($record, 'group_perms', 'pid');

    $record = array();
    $record['gid']       = '0';
    $record['sequence']  = '5';
    $record['realm']     = '0';
    $record['component'] = '.*';
    $record['instance']  = '.*';
    $record['level']     = '200';
    $record['bond']      = '0';
    DBUtil::insertObject($record, 'group_perms', 'pid');

    pnModSetVar('Permissions', 'filter', 1);
    pnModSetVar('Permissions', 'warnbar', 1);
    pnModSetVar('Permissions', 'rowview', 20);
    pnModSetVar('Permissions', 'rowedit', 20);
    pnModSetVar('Permissions', 'lockadmin', 1);
    pnModSetVar('Permissions', 'adminid', 1);
}
