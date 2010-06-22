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
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @return array The table information.
 */
function Users_tables()
{
    // Initialise table array
    $pntable = array();

    // Main Users table
    // Stores core information about each user account.
    // DO NOT USE A FIELD FOR A PURPOSE OTHER THAN ITS DOCUMENTED INTENT!
    //
    // uid              - User ID: Primary user identifier
    // uname            - User Name: Primary log in identifier
    // email            - E-mail Address: Secondary log in identifier, user notifications
    // user_regdate     - Registration Date/Time: Date/time user was added to the users table (not the users_temp table)
    //                      NOTE: This is stored as an SQL datetime, which is highly dependent on both PHP's timezone setting,
    //                      and on the database server's timezone setting. If they do not match, then inconsistencies will propogate.
    //                      If Zikula is moved to a new database server with a different time zone configuration, then these
    //                      dates/times will be interpreted based on the new time zone, not the original one!
    // user_viewemail   - User's election to allow others to see his e-mail address (1 == true == others are allowed to see it)
    //                      Modules displaying e-mail addresses are supposed to check this, but most don't
    // user_theme       - DEPRECATED - DO NOT USE
    // pass             - Password: User's password for logging in. This value is salted and hashed. The salt is stored in this field,
    //                      delimited from the hash with a dollar sign character ($). The hash algorithm is stored as a numeric code
    //                      in the hash_method field.
    // storynum         - DEPRECATED - DO NOT USE
    // ublockon         - User-defined Block On?: Whether the custom user-defined block is displayed or not (1 == true == displayed)
    // ublock           - User-defined Block: Custom user-defined block content.
    // theme            - User's Theme: The name (identifier) of the per-user theme the user would like to use while viewing the site, when
    //                      user theme switching is enabled.
    // counter          - DEPRECATED - DO NOT USE
    // activated        - Account State (was Activated?): The user's current state, see UserUtil::ACTIVE_* for defined constants
    // lastlogin        - Last Login Date/Time: Date/time user last successfully logged into the site.
    //                      NOTE: This is stored as an SQL datetime, which is highly dependent on both PHP's timezone setting,
    //                      and on the database server's timezone setting. If they do not match, then inconsistencies will propogate.
    //                      If Zikula is moved to a new database server with a different time zone configuration, then these
    //                      dates/times will be interpreted based on the new time zone, not the original one!
    // validfrom        - (FUTURE USE) Account Valid From: The first date and time a user is permitted to log into this account, stored
    //                      as a UNIX timestamp.
    // validuntil       - (FUTURE USE) Account Valid Until: The one second past the last date and time a user is permitted to log into
    //                      this account (the first date and time the user can no longer log into the account), stored as a UNIX timestamp.
    // hash_method      - Password Hash Method: The hashing algorithm used to hash the password, stored as an integer identifier.
    //                      NOTE: This must always match the hashing method used to create the pass field! If the pass field is
    //                      updated, then the diligent programmer will ensure that this field is updated (if appropriate) as well!
    //                      Failure to maintain consistency between the pass field and this field will prevent users from being able
    //                      to successfully log in!
    $pntable['users'] = DBUtil::getLimitedTablename('users');
    $pntable['users_column'] = array (
        'uid'             => 'pn_uid',
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
        'hash_method'     => 'pn_hash_method'
    );

    $pntable['users_column_def'] = array(
        'uid'             => "I4 PRIMARY AUTO",
        'uname'           => "C(25) NOTNULL DEFAULT ''",
        'email'           => "C(60) NOTNULL DEFAULT ''",
        'user_regdate'    => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
        'user_viewemail'  => "I2 DEFAULT 0",
        'user_theme'      => "C(64) DEFAULT ''",
        'pass'            => "C(134) NOTNULL DEFAULT ''",
        'storynum'        => "I(4) NOTNULL DEFAULT '10'",
        'ublockon'        => "I1 NOTNULL DEFAULT '0'",
        'ublock'          => "X NOTNULL DEFAULT ''",
        'theme'           => "C(255) NOTNULL DEFAULT ''",
        'counter'         => "I4 NOTNULL DEFAULT '0'",
        'activated'       => "I1 NOTNULL DEFAULT '0'",
        'lastlogin'       => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
        'validfrom'       => "I4 NOTNULL DEFAULT '0'",
        'validuntil'      => "I4 NOTNULL DEFAULT '0'",
        'hash_method'     => "I1 NOTNULL DEFAULT '8'"
    );

    $pntable['users_column_idx'] = array('uname' => 'uname',
                                         'email' => 'email');

    $pntable['users_db_extra_enable_attribution'] = true;
    $pntable['users_primary_key_column'] = 'uid';

    // Shadow file for additional user-related security information
    //
    // id               - ID: Primary ID of the shadow record. Not related to the uid.
    // uid              - User ID: Primary ID of the user record to which this shadow record is related. Foreign key to users table.
    // code             - Confirmation Code: The confirmation code last sent to the user for password recovery, as a salted hash value.
    // code_hash_method - Code Hash Method: An integer code identifying the hashing method used to hash the code. Uses same set of integer codes
    //                      as does the hash_method field in users table.
    // code_expires     - Code Expiration Date/Time: One second past the last date and time the code is valid, stored as a UNIX timestamp (The
    //                      first date and time the code is invalid for use).
    $pntable['users_shadow'] = DBUtil::getLimitedTablename('users_shadow');;
    $pntable['users_shadow_column'] = array (
        'id'                => 'z_sid',
        'uid'               => 'z_uid',
        'code'              => 'z_code',
        'code_hash_method'  => 'z_hash_method',
        'code_expires'      => 'z_expires',
    );

    $pntable['users_shadow_column_def'] = array(
        'id'                => "I4 PRIMARY AUTO",
        'uid'               => "I4 NOTNULL DEFAULT 0",
        'code'              => "C(134) NOTNULL DEFAULT ''",
        'code_hash_method'  => "I1 NOTNULL DEFAULT 8",
        'code_expires'      => "I4 NOTNULL DEFAULT 0",
    );

    // Temporary user table.
    // Used for storing 1a) registrations that are pending administrator approval, 1b) registrations that are pending e-mail verification
    // (a.k.a. activation), 2) storing a new e-mail address pending e-mail verification for the change-of-e-mail process for existing active
    // users.
    // DO NOT USE A FIELD FOR A PURPOSE OTHER THAN ITS DOCUMENTED INTENT!
    // 
    // *** When storing registrations pending approval and/or verification (#1a and #1b above) ***
    // tid              - ID: Primary identifier for the record. (Unrelated to uid.)
    // uname            - User Name: The new user's user name.
    // email            - E-mail Address: The new user's e-mail address.
    // femail           - DEPRECATED - DO NOT USE (was Fake E-mail Address)
    // pass             - Password: The new user's password for logging in. This value is salted and hashed. The salt is stored in this field,
    //                      delimited from the hash with a dollar sign character ($). The hash algorithm is stored as a numeric code
    //                      in the hash_method field.
    // dynamics         - The new user's profile module properties, serialized using PHP's serialize() function. Used only if the profile module
    //                      system configuration variable is set, the module named in that variable is available, and the Users module configuration 
    //                      variable that controls whether this information is gathered is set to true (or 1).
    // comment          - FUTURE USE? (Should this really be stored as an attribute to the users_temp record?)
    // type             - An integer code describing the type of temporary record, in this case, 1.
    // tag              - DEPRECATED - DO NOT USE
    // hash_method      - Password Hash Method: The hashing algorithm used to hash the password, stored as an integer identifier.
    //                      NOTE: This must always match the hashing method used to create the pass field! If the pass field is
    //                      updated, then the diligent programmer will ensure that this field is updated (if appropriate) as well!
    //                      Failure to maintain consistency between the pass field and this field will prevent users from being able
    //                      to successfully log in!
    // 
    // *** When storing a new e-mail address pending verification for an existing active user (#2 above) ***
    // tid              - ID: Primary identifier for the record. (Unrelated to uid.)
    // uname            - User Name: User's user name, foreign key to the users table.
    // email            - E-mail Address: The user's new e-mail address.
    // femail           - NOT USED
    // pass             - NOT USED
    // dynamics         - Request Date/Time: The date and time the user requested a change in password, used to expire these requests
    //                      after a set amount of time.
    // comment          - Confirmation Code: The confirmation code the user must use to verify the new e-mail address before the users
    //                      table's email field will be updated.
    // type             - An integer code describing the type of temporary record, in this case, 2.
    // tag              - NOT USED
    // hash_method      - NOT USED
    $pntable['users_temp'] = DBUtil::getLimitedTablename('users_temp');
    $pntable['users_temp_column'] = array (
        'tid'          => 'pn_tid',
        'uname'        => 'pn_uname',
        'email'        => 'pn_email',
        'femail'       => 'pn_femail',
        'pass'         => 'pn_pass',
        'dynamics'     => 'pn_dynamics',
        'comment'      => 'pn_comment',
        'type'         => 'pn_type',
        'tag'          => 'pn_tag',
        'hash_method'  => 'pn_hash_method'
    );

    $pntable['users_temp_column_def'] = array(
        'tid'          => "I4 PRIMARY AUTO",
        'uname'        => "C(25) NOTNULL DEFAULT ''",
        'email'        => "C(60) NOTNULL DEFAULT ''",
        'femail'       => "I1 NOTNULL DEFAULT '0'",
        'pass'         => "C(134) NOTNULL DEFAULT ''",
        'dynamics'     => "XL NOTNULL",
        'comment'      => "C(254) NOTNULL DEFAULT ''",
        'type'         => "I1 NOTNULL DEFAULT '0'",
        'tag'          => "I1 NOTNULL DEFAULT '0'",
        'hash_method'  => "I1 NOTNULL DEFAULT '8'"
    );

    $pntable['users_temp_db_extra_enable_attribution'] = true;
    $pntable['users_temp_primary_key_column'] = 'tid';

    // Sessions Table
    // Stores per-user session information for users who are logged in. (Note: Users who use the "remember me" option when logging in
    // remain logged in across multiple visits for a defined period of time, therefore their session record remains active.)
    // DO NOT USE A FIELD FOR A PURPOSE OTHER THAN ITS DOCUMENTED INTENT!
    //
    // sessid           - Session ID: Primary identifier
    // ipaddr           - IP Address: The user's IP address for the session.
    // lastused         - Last Used Date/Time: Date/time this session record was last used for the user.
    //                      NOTE: This is stored as an SQL datetime, which is highly dependent on both PHP's timezone setting,
    //                      and on the database server's timezone setting. If they do not match, then inconsistencies will propogate.
    //                      If Zikula is moved to a new database server with a different time zone configuration, then these
    //                      dates/times will be interpreted based on the new time zone, not the original one!
    // uid              - User ID: Primary ID of the user record to which this session record is related. Foreign key to users table.
    // remember         - Remember Me?: Whether the last successful login by the user (which creted this session record) used the "remember
    //                      me" option to remain logged in between visits.
    // vars             - Session Variables: Per-user/per-session variables. (Serialized)
    //
    $pntable['session_info'] = DBUtil::getLimitedTablename('session_info');
    $pntable['session_info_column'] = array (
        'sessid'    => 'pn_sessid',
        'ipaddr'    => 'pn_ipaddr',
        'lastused'  => 'pn_lastused',
        'uid'       => 'pn_uid',
        'remember'  => 'pn_remember',
        'vars'      => 'pn_vars'
    );

    $pntable['session_info_column_def'] = array(
        'sessid'    => "C(40) PRIMARY NOTNULL DEFAULT ''",
        'ipaddr'    => "C(32) NOTNULL DEFAULT ''",
        'lastused'  => "T DEFAULT '1970-01-01 00:00:00'",
        'uid'       => "I4 DEFAULT '0'",
        'remember'  => "I1 NOTNULL DEFAULT '0'",
        'vars'      => "XL NOTNULL"
    );

    // Return the table information
    return $pntable;
}
