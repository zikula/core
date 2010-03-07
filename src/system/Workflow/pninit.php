<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2006, Zikula Software Foundation
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Workflow
 */

/**
 * Initialize the pnWorkflow module
 *
 * @return bool
 */
function Workflow_init()
{
    if (!DBUtil::createTable('workflows')) {
        return false;
    }

    return true;
}

/**
 * Upgrade the Workflow module
 *
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function Workflow_upgrade($oldVersion)
{
    return true;
}

/**
 * Delete the Workflow module
 *
 * @return bool
 */
function Workflow_delete()
{
    return DBUtil::dropTable('workflows');
}
