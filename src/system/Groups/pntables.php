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
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in the information
 */
function Groups_pntables()
{
    $pntable = array();

    // get the db driver
    $dbdriver = DBConnectionStack::getConnectionDBDriver();

    $group_membership = DBUtil::getLimitedTablename('group_membership') ;
    $pntable['group_membership'] = $group_membership;
    $pntable['group_membership_column'] = array ('gid' => 'pn_gid',
                                                 'uid' => 'pn_uid');

    // column definitions
    $pntable['group_membership_column_def'] = array ('gid' => 'I NOTNULL DEFAULT 0',
                                                     'uid' => 'I NOTNULL DEFAULT 0');

    // addtitional indexes
    $pntable['group_membership_column_idx'] = array ('gid_uid' => array('uid', 'gid'));

    $groups = DBUtil::getLimitedTablename('groups') ;
    $pntable['groups'] = $groups;
    $pntable['groups_column'] = array ('gid'          => 'pn_gid',
                                       'name'         => 'pn_name',
                                       'gtype'        => 'pn_gtype',
                                       'description'  => 'pn_description',
                                       'prefix'       => 'pn_prefix',
                                       'state'        => 'pn_state',
                                       'nbuser'       => 'pn_nbuser',
                                       'nbumax'       => 'pn_nbumax',
                                       'link'         => 'pn_link',
                                       'uidmaster'    => 'pn_uidmaster');

    // column definitions
    $pntable['groups_column_def'] = array('gid'         => "I AUTO PRIMARY",
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
    $pntable['group_applications'] = $group_applications;
    $pntable['group_applications_column'] = array ('app_id'      => 'pn_app_id',
                                                   'uid'         => 'pn_uid',
                                                   'gid'         => 'pn_gid',
                                                   'application' => 'pn_application',
                                                   'status'      => 'pn_status');

    // column definition
    $pntable['group_applications_column_def'] = array ('app_id'      => 'I4 NOTNULL AUTO PRIMARY',
                                                       'uid'         => 'I4 NOTNULL DEFAULT 0',
                                                       'gid'         => 'I4 NOTNULL DEFAULT 0',
                                                       'application' => ($dbdriver=='oci8') ? 'XL NOTNULL' : 'B NOTNULL',
                                                       'status'      => 'I1 NOTNULL DEFAULT 0');

    return $pntable;
}
