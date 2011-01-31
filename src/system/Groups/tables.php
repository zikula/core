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
 * Internal Groups module function.
 *
 * This function is called internally by the core whenever the module is loaded.  It adds in the information.
 */
function Groups_tables()
{
    $dbtable = array();

    // get the db driver
    $dbdriver = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());

    $group_membership = DBUtil::getLimitedTablename('group_membership') ;
    $dbtable['group_membership'] = $group_membership;
    $dbtable['group_membership_column'] = array ('gid' => 'z_gid',
                                                 'uid' => 'z_uid');

    // column definitions
    $dbtable['group_membership_column_def'] = array ('gid' => 'I NOTNULL DEFAULT 0',
                                                     'uid' => 'I NOTNULL DEFAULT 0');

    // addtitional indexes
    $dbtable['group_membership_column_idx'] = array ('gid_uid' => array('uid', 'gid'));

    $groups = DBUtil::getLimitedTablename('groups') ;
    $dbtable['groups'] = $groups;
    $dbtable['groups_column'] = array ('gid'          => 'z_gid',
                                       'name'         => 'z_name',
                                       'gtype'        => 'z_gtype',
                                       'description'  => 'z_description',
                                       'prefix'       => 'z_prefix',
                                       'state'        => 'z_state',
                                       'nbuser'       => 'z_nbuser',
                                       'nbumax'       => 'z_nbumax',
                                       'link'         => 'z_link',
                                       'uidmaster'    => 'z_uidmaster');

    // column definitions
    $dbtable['groups_column_def'] = array('gid'         => "I AUTO PRIMARY",
                                          'name'        => "C(255) NOTNULL DEFAULT ''",
                                          'gtype'       => "I1 NOTNULL DEFAULT 0",
                                          'description' => "C(200) NOTNULL DEFAULT ''",
                                          'prefix'      => "C(25)  NOTNULL DEFAULT ''",
                                          'state'       => "I1 NOTNULL DEFAULT 0",
                                          'nbuser'      => "I4 NOTNULL DEFAULT 0",
                                          'nbumax'      => "I4 NOTNULL DEFAULT 0",
                                          'link'        => "I4 NOTNULL DEFAULT 0",
                                          'uidmaster'   => "I4 NOTNULL DEFAULT 0");

    // limit table name, see DBUtil::limitTablename() for more information about this
    $group_applications = DBUtil::getLimitedTablename('group_applications') ;
    $dbtable['group_applications'] = $group_applications;
    $dbtable['group_applications_column'] = array ('app_id'      => 'z_app_id',
                                                   'uid'         => 'z_uid',
                                                   'gid'         => 'z_gid',
                                                   'application' => 'z_application',
                                                   'status'      => 'z_status');

    // column definition
    $dbtable['group_applications_column_def'] = array ('app_id'      => 'I4 NOTNULL AUTO PRIMARY',
                                                       'uid'         => 'I4 NOTNULL DEFAULT 0',
                                                       'gid'         => 'I4 NOTNULL DEFAULT 0',
                                                       'application' => ($dbdriver=='oracle') ? 'XL NOTNULL' : 'B NOTNULL',
                                                       'status'      => 'I1 NOTNULL DEFAULT 0');

    return $dbtable;
}
