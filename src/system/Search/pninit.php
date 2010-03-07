<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Search
 * @license http://www.gnu.org/copyleft/gpl.html
 */

/**
 * initialise the Search module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function search_init()
{
    // Creating the table
    if (!DBUtil::createTable('search_stat')) {
        return false;
    }

    // Creating the table
    if (!DBUtil::createTable('search_result')) {
        return false;
    }

    // create module vars
    pnModSetVar('Search', 'itemsperpage', 10);
    pnModSetVar('Search', 'limitsummary', 255);

    // Initialisation successful
    return true;
}

/**
 * upgrade the Search module from an old version
 *
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function search_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch ($oldversion)
    {
        case '1.5':
            // future upgrade routines
    }

    // Update successful
    return true;
}

/**
 * Delete the Search module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function search_delete()
{
    if (DBUtil::dropTable('search_stat') != 2) {
        return false;
    }

    if (DBUtil::dropTable('search_result') != 2) {
        return false;
    }

    // Delete any module variables
    pnModDelVar('Search');

    // Deletion successful
    return true;
}
