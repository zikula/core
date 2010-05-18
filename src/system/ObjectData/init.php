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
 */

/**
 * initialise the ObjectData module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function ObjectData_init()
{
    if (!DBUtil::createTable('objectdata_attributes')) {
        return false;
    }

    if (!DBUtil::createTable('objectdata_log')) {
        return false;
    }

    ObjectData_createTables_101 ();

    return true;
}

/**
 * upgrade the module from an old version
 *
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function ObjectData_upgrade($oldversion)
{
    switch ($oldversion)
    {
        case '1.00':
            ObjectData_createTables_101();
            objectdata_upgrade('1.01');

        case '1.01':
        case '1.02':
            if (!DBUtil::changeTable('objectdata_attributes')) {
                LogUtil::registerError(__('Error! Could not save the attributes table.'));
                return '1.02';
            }

        case '1.03':
            // future upgrade routines
    }

    return true;
}

/**
 * delete the ObjectData module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function ObjectData_delete()
{
    // cannot disable this module because it's required for core functions
    return false;
}

function ObjectData_createTables_101()
{
    if (!DBUtil::createTable('objectdata_meta')) {
        return false;
    }
}
