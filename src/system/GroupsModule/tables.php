<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Internal Groups module function.
 *
 * This function is called internally by the core whenever the module is loaded.  It adds in the information.
 *
 * @return array Return tables array.
 */
function ZikulaGroupsModule_tables()
{
    $dbtable = array();

    $group_membership = 'group_membership';
    $dbtable['group_membership'] = $group_membership;
    $dbtable['group_membership_column'] = array('gid' => 'gid',
                                                 'uid' => 'uid');

    // column definitions
    $dbtable['group_membership_column_def'] = array('gid' => 'I NOTNULL DEFAULT 0',
                                                     'uid' => 'I NOTNULL DEFAULT 0');

    // additional indexes
    $dbtable['group_membership_column_idx'] = array('gid_uid' => array('uid', 'gid'));

    $groups = 'groups';
    $dbtable['groups'] = $groups;
    $dbtable['groups_column'] = array('gid'          => 'gid',
                                       'name'         => 'name',
                                       'gtype'        => 'gtype',
                                       'description'  => 'description',
                                       'prefix'       => 'prefix',
                                       'state'        => 'state',
                                       'nbuser'       => 'nbuser',
                                       'nbumax'       => 'nbumax',
                                       'link'         => 'link',
                                       'uidmaster'    => 'uidmaster');

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

    $group_applications = 'group_applications';
    $dbtable['group_applications'] = $group_applications;
    $dbtable['group_applications_column'] = array('app_id'      => 'app_id',
                                                   'uid'         => 'uid',
                                                   'gid'         => 'gid',
                                                   'application' => 'application',
                                                   'status'      => 'status');

    // column definition
    $dbtable['group_applications_column_def'] = array('app_id'      => 'I4 NOTNULL AUTO PRIMARY',
                                                       'uid'         => 'I4 NOTNULL DEFAULT 0',
                                                       'gid'         => 'I4 NOTNULL DEFAULT 0',
                                                       'application' => 'XL NOTNULL',
                                                       'status'      => 'I1 NOTNULL DEFAULT 0');

    return $dbtable;
}
