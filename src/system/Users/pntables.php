<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 */

/**
 * Populate pntables array for Users module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the pnModDBInfoLoad() API function.
 *
 * @return array The table information.
 */
function Users_pntables()
{
    // Initialise table array
    $pntable = array();

    // Get the name for the Users item table.  This is not necessary
    // but helps in the following statements and keeps them readable
    $users = DBUtil::getLimitedTablename('users');

    // Set the table name
    $pntable['users'] = $users;

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $pntable['users_column'] = array ('uid'             => 'pn_uid',
                                      'uname'           => 'pn_uname',
                                      'email'           => 'pn_email',
                                      'user_regdate'    => 'pn_user_regdate',
                                      'user_viewemail'  => 'pn_user_viewemail',
                                      'user_theme'      => 'pn_user_theme',
                                      'pass'            => 'pn_pass',
                                      'storynum'        => 'pn_storynum',
                                      'ublockon'        => 'pn_ublockon',
                                      'ublock'          => 'pn_ublock',
                                      'theme'           => 'pn_theme',
                                      'counter'         => 'pn_counter',
                                      'activated'       => 'pn_activated',
                                      'lastlogin'       => 'pn_lastlogin',
                                      'validfrom'       => 'pn_validfrom',
                                      'validuntil'      => 'pn_validuntil',
                                      'hash_method'     => 'pn_hash_method');

    $pntable['users_column_def'] = array('uid'             => "I4 PRIMARY AUTO",
                                         'uname'           => "C(25) NOTNULL DEFAULT ''",
                                         'email'           => "C(60) NOTNULL DEFAULT ''",
                                         'user_regdate'    => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
                                         'user_viewemail'  => "I2 DEFAULT 0",
                                         'user_theme'      => "C(64) DEFAULT ''",
                                         'pass'            => "C(128) NOTNULL DEFAULT ''",
                                         'storynum'        => "I(4) NOTNULL DEFAULT '10'",
                                         'ublockon'        => "I1 NOTNULL DEFAULT '0'",
                                         'ublock'          => "X NOTNULL DEFAULT ''",
                                         'theme'           => "C(255) NOTNULL DEFAULT ''",
                                         'counter'         => "I4 NOTNULL DEFAULT '0'",
                                         'activated'       => "I1 NOTNULL DEFAULT '0'",
                                         'lastlogin'       => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
                                         'validfrom'       => "I4 NOTNULL DEFAULT '0'",
                                         'validuntil'      => "I4 NOTNULL DEFAULT '0'",
                                         'hash_method'     => "I1 NOTNULL DEFAULT '8'");

    $pntable['users_column_idx'] = array('uname' => 'uname',
                                         'email' => 'email');

    // Turn on object attribution for the users table. This enables other modules to dynamically
    // add information for a user. These information are stored in the ObjectData modules
    // objectdata_attributes table
    $pntable['users_db_extra_enable_attribution'] = true;
    // needed for meta data? not sure....
    $pntable['users_primary_key_column'] = 'uid';

    // Temp Table - Moderation
    // Get the name for the Temporary item table.  This is not necessary
    // but helps in the following statements and keeps them readable
    $users_temp = DBUtil::getLimitedTablename('users_temp');

    // Set the table name
    $pntable['users_temp'] = $users_temp;

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $pntable['users_temp_column'] = array ('tid'          => 'pn_tid',
                                           'uname'        => 'pn_uname',
                                           'email'        => 'pn_email',
                                           'femail'       => 'pn_femail',
                                           'pass'         => 'pn_pass',
                                           'dynamics'     => 'pn_dynamics',
                                           'comment'      => 'pn_comment',
                                           'type'         => 'pn_type',
                                           'tag'          => 'pn_tag',
                                           'hash_method'  => 'pn_hash_method');

    $pntable['users_temp_column_def'] = array('tid'          => "I4 PRIMARY AUTO",
                                              'uname'        => "C(25) NOTNULL DEFAULT ''",
                                              'email'        => "C(60) NOTNULL DEFAULT ''",
                                              'femail'       => "I1 NOTNULL DEFAULT '0'",
                                              'pass'         => "C(128) NOTNULL DEFAULT ''",
                                              'dynamics'     => "XL NOTNULL",
                                              'comment'      => "C(254) NOTNULL DEFAULT ''",
                                              'type'         => "I1 NOTNULL DEFAULT '0'",
                                              'tag'          => "I1 NOTNULL DEFAULT '0'",
                                              'hash_method'  => "I1 NOTNULL DEFAULT '8'");

    // sessions
    // Get the name for the session item table.  This is not necessary
    // but helps in the following statements and keeps them readable
    $session_info = DBUtil::getLimitedTablename('session_info');

    // Set the table name
    $pntable['session_info'] = $session_info;

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $pntable['session_info_column'] = array ('sessid'    => 'pn_sessid',
                                             'ipaddr'    => 'pn_ipaddr',
                                             'lastused'  => 'pn_lastused',
                                             'uid'       => 'pn_uid',
                                             'remember'  => 'pn_remember',
                                             'vars'      => 'pn_vars');

    $pntable['session_info_column_def'] = array('sessid'    => "C(40) PRIMARY NOTNULL DEFAULT ''",
                                                'ipaddr'    => "C(32) NOTNULL DEFAULT ''",
                                                'lastused'  => "T DEFAULT '1970-01-01 00:00:00'",
                                                'uid'       => "I4 DEFAULT '0'",
                                                'remember'  => "I1 NOTNULL DEFAULT '0'",
                                                'vars'      => "XL NOTNULL" );

    // Return the table information
    return $pntable;
}
