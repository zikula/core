<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Groups
*/

/**
 * initialise the groups module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @author Mark West
 * @return bool true if initialisation succesful, false otherwise
 */
function Groups_init()
{
    // create the groups table
    if (!DBUtil::createTable('groups')) {
        return false;
    }

    // create the group membership table
    if (!DBUtil::createTable('group_membership')) {
        return false;
    }

    // create the groups applications table
    if (!DBUtil::createTable('group_applications')) {
        return false;
    }

    // set all our module vars
    ModUtil::setVar('Groups', 'itemsperpage', 25);
    ModUtil::setVar('Groups', 'defaultgroup', 1);
    ModUtil::setVar('Groups', 'mailwarning', 0);
    ModUtil::setVar('Groups', 'hideclosed', 0);

    // create the default data for the modules module
    groups_defaultdata();

    // Initialisation successful
    return true;
}

/**
 * upgrade the module from an old version
 *
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @author       Mark West
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function Groups_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch ($oldversion)
    {
        case '2.1':
            // change value of defaultgroup from name to gid
            $gid = DBUtil::selectObjectByID('groups', ModUtil::getVar('Groups', 'defaultgroup'), 'name');
            ModUtil::setVar('Groups', 'defaultgroup', $gid['gid']);

        case '2.2':
        case '2.3':
            // future upgrade routines
    }
    // Update successful
    return true;
}

/**
 * delete the groups module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @author Mark West
 * @return bool true if delete succesful, false otherwise */
function Groups_delete()
{
    // Deletion not allowed
    return false;
}

/**
 * create the default data for the groups module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @author       Mark West
 * @return       bool       false
 */
function groups_defaultdata()
{
    $records = array(array('name'        => __('Users'),
                           'description' => __('By default, all users are made members of this group.'),
                           'prefix'      => __('usr')),
                     array('name'        => __('Administrators'),
                           'description' => __('By default, all administrators are made members of this group.'),
                           'prefix'      => __('adm')));

    DBUtil::insertObjectArray($records, 'groups', 'gid');

    // Insert Anonymous and Admin users
    $records = array(array('gid' => '1',
                           'uid' => '1'),
                     array('gid' => '2',
                           'uid' => '2'));

    DBUtil::insertObjectArray($records, 'group_membership', 'gid', true);
}
