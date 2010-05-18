<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage SysInfo
 */

/**
 * initialise the template module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function sysinfo_init()
{
    // Initialisation successful
    return true;
}

/**
 * Upgrade the errors module from an old version
 * 
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function sysinfo_upgrade($oldversion)
{
    // Update successful
    return true;
}

/**
 * delete the errors module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function sysinfo_delete()
{
    // Deletion successful
    return true;
}
