<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage PageLock
 */

/**
 * initialize the module
 */
function PageLock_init()
{
    if (!DBUtil::createTable('PageLock'))
        return false;

    return true;
}

/**
 * upgrades the module
 *
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function PageLock_upgrade($oldversion)
{
    return true;
}

/**
 * delete the module
 */
function PageLock_delete()
{
    DBUtil::dropTable('PageLock');

    return true;
}
