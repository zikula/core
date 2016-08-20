<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Populate tables array for Users module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @param string $forVersion The module version number for which db information should be returned
 *
 * @return array The table information
 */
function ZikulaUsersModule_tables($forVersion = null)
{
    if (!isset($forVersion)) {
        if (isset($GLOBALS['_ZikulaUpgrader']['_ZikulaUpgradeFrom12x']) && $GLOBALS['_ZikulaUpgrader']['_ZikulaUpgradeFrom12x']) {
            // This check comes before System::isInstalling().
            return Users_tables_for_113();
        }

        if (System::isInstalling()) {
            // new installs
            return Users_tables_for_220();
        }

        // Remaining cases - this should be deleted.
        $usersModInfo = ModUtil::getInfoFromName('ZikulaUsersModule');
        $forVersion = $usersModInfo['version'];
    }

    if (version_compare($forVersion, '2.2.0') >= 0) {
        return Users_tables_for_220();
    } else {
        return Users_tables_for_113();
    }
}

/**
 * Populate pntables array for Users module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @return array The table information
 */
function Users_tables_for_220()
{
    // Initialise table array
    $dbinfo = [];

    // Main Users table
    // Version 2.2.0 through current(inclusive)
    // Stores core information about each user account.
    // DO NOT USE A FIELD FOR A PURPOSE OTHER THAN ITS DOCUMENTED INTENT!
    //
    // uid              - User ID: Primary user identifier
    // uname            - User Name: Primary user display name, primary log in identifier.
    // email            - E-mail Address: Secondary log in identifier, user notifications. For pending registrations awaiting e-mail
    //                      address verification, this will be an empty string, and the email address for the account will be found
    //                      in the users_verifychg table. ("Regular" user accounts may also have e-mail addresses pending verification
    //                      in the users_verifychg table, however those are the result of a request to change the account's address.)
    // pass             - Password: User's password for logging in. This value is salted and hashed. The salt is stored in this field,
    //                      delimited from the hash with a dollar sign character ($). The hash algorithm is stored as a numeric code
    //                      in the hash_method field. This field may be blank in instances where the user registered with an
    //                      alternative authentication module (e.g., OpenID) and did not also establish a password for his web site account.
    // passreminder     - Password reminder: Set during registration or password changes, to remind the user what his password is.
    //                      This field may be blank if pass is blank.
    // activated        - Account State: The user's current state, see Users_Constant::ACTIVE_* for defined constants. A state
    //                      represented by a negative integer means that the user's account is in a pending state, and
    //                      should not yet be considered a "real" user account. For example, user
    //                      accounts pending the completion of the registration process (because either moderation, e-mail
    //                      verification, or both are in use) will have a negative integer representing their state. If
    //                      the user's registration request expires before it the process is completed, or if the
    //                      administrator denies the request for an new account, the user account record will be deleted.
    //                      When this deletion happens, it will be assumed by the system that no external module has
    //                      yet interacted with the user account record, because its state never progressed beyond its
    //                      pending state, and therefore normal hooks/events may not be triggered (although it is possible
    //                      that events regarding the pending account may be triggered).
    // approved_date    - Account Approved Date/Time: The date and time the user's registration request was approved through
    //                      the moderation process. If the moderation process was not in effect at the time the user
    //                      made a registration request, then this will be the date and time of the registration request.
    //                      NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER
    //                      server local time nor user local time (unless one or the other happens to be UTC).
    //                      WARNING: The date and time related functions available in SQL on many RDBMS servers are
    //                      highly dependent on the database server's timezone setting. All parameters to these functions
    //                      are treated as if the dates and times they represent are in the time zone that is set
    //                      in the database server's settings. Use of date/time functions in SQL queries should be
    //                      avoided if at all possible. PHP functions using UTC as the base time zone should be used
    //                      instead. If SQL date/time functions must be used, then care should be taken to ensure that
    //                      either the function is time zone neutral, or that the function and its relationship to
    //                      time zone settings is completely understood.
    // approved_by      - The uid of the user account that approved the request to register a new account. If this is
    //                      the same as the user account's uid, then moderation was not in use at the time the request
    //                      for a new account was made. If this is -1, the the user account that approved the request
    //                      has since been deleted. If this is 0, the user account has not yet been approved.
    // user_regdate     - Registration Date/Time: Date/time the user account was registered. For users not pending the
    //                      completion of the registration process, this is the date and time the user
    //                      account completed the process. For example, if registrations are moderated, then this is
    //                      the date and time the registration request was approved. If registration e-mail addresses must
    //                      be verified, then this is the date and time the user completed the verification process. If both
    //                      moderation and verification are in use, then this is the later of those two dates. If neither
    //                      is in use, then this is simply the date and time the user's registration request was made.
    //                      If the user account's activated state is "pending registration" (implying that either moderation,
    //                      verification, or both are in use) then this will be the date and time the user made the
    //                      registration request UNTIL the registration process is complete, and then it is updated as above.
    //                      NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER
    //                      server local time nor user local time. SEE WARNING under approved_date, above.
    // lastlogin        - Last Login Date/Time: Date/time user last successfully logged into the site.
    //                      NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER
    //                      server local time nor user local time. SEE WARNING under approved_date, above.
    // theme            - User's Theme: The name (identifier) of the per-user theme the user would like to use while viewing the site, when
    //                      user theme switching is enabled.
    // ublockon         - User-defined Block On?: Whether the custom user-defined block is displayed or not (1 == true == displayed)
    // ublock           - User-defined Block: Custom user-defined block content.
    // tz               - User's timezone, as supported by PHP (listed at http://us2.php.net/manual/en/timezones.php), and as expressed by
    //                      the Olson tz database. Optional, if blank then the system default timezone should be used. [FUTURE USE]
    // locale           - The user's chosen locale for i18n purposes, as defined by gettext, POSIX, and the Common Locale Data Repository;
    //                      optional, if blank then the system default locale should be used. [FUTURE USE]
    //
    $dbinfo['users'] = 'users';
    $dbinfo['users_column'] = [
        'uid'           => 'uid',
        'uname'         => 'uname',
        'email'         => 'email',
        'pass'          => 'pass',
        'passreminder'  => 'passreminder',
        'activated'     => 'activated',
        'approved_date' => 'approved_date',
        'approved_by'   => 'approved_by',
        'user_regdate'  => 'user_regdate',
        'lastlogin'     => 'lastlogin',
        'theme'         => 'theme',
        'ublockon'      => 'ublockon',
        'ublock'        => 'ublock',
        'tz'            => 'tz',
        'locale'        => 'locale',
    ];
    $dbinfo['users_column_def'] = [
        'uid'           => "I PRIMARY AUTO",
        'uname'         => "C(25) NOTNULL DEFAULT ''",
        'email'         => "C(60) NOTNULL DEFAULT ''",
        'pass'          => "C(138) NOTNULL DEFAULT ''",
        'passreminder'  => "C(255) NOTNULL DEFAULT ''",
        'activated'     => "I2 NOTNULL DEFAULT 0",
        'approved_date' => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
        'approved_by'   => "I4 NOTNULL DEFAULT 0",
        'user_regdate'  => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
        'lastlogin'     => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
        'theme'         => "C(255) NOTNULL DEFAULT ''",
        'ublockon'      => "I1 NOTNULL DEFAULT 0",
        'ublock'        => "X NOTNULL DEFAULT ''",
        'tz'            => "C(30) NOTNULL DEFAULT ''",
        'locale'        => "C(5) NOTNULL DEFAULT ''",
    ];

    $dbinfo['users_column_idx'] = [
        'uname' => 'uname',
        'email' => 'email'
    ];

    $dbinfo['users_db_extra_enable_attribution'] = true;
    $dbinfo['users_primary_key_column'] = 'uid';

    // Account-change verification table
    // Version 2.2.0 through current(inclusive)
    // Holds a one-time use, expirable verification code used when a user needs to changs his email address,
    // reset his password and has not answered any security questions, or when a new user is registering with
    // the site for the first time.
    //
    // id               - ID: Primary ID of the verification record. Not related to the uid.
    // changetype       - Change type: a code indicating what type of change action created this record.
    // uid              - User ID: Primary ID of the user record to which this verification record is related. Foreign key to users table.
    // newemail         - New e-mail address: If the change type indicates that this verification record was created
    //                      as a result of a user changing his e-mail address, then this field holds the new address
    //                      temporarily until the verification is complete. Only after the verification code is received
    //                      back from the user (thus, verifying the new e-mail address) is the new e-mail address saved
    //                      to the user's account record.
    // verifycode       - Verification Code: The verification code last sent to the user to verify the requested action,
    //                      as a salted hash of the value sent.
    // created_dt       - Date/Time created: The date and time the verification record was created, as a UTC date/time,
    //                      used to expire the record.
    $dbinfo['users_verifychg'] = 'users_verifychg';

    $dbinfo['users_verifychg_column'] = [
        'id'            => 'id',
        'changetype'    => 'changetype',
        'uid'           => 'uid',
        'newemail'      => 'newemail',
        'verifycode'    => 'verifycode',
        'created_dt'    => 'created_dt',
    ];

    $dbinfo['users_verifychg_column_def'] = [
        'id'            => "I PRIMARY AUTO",
        'changetype'    => "I1 NOTNULL DEFAULT '0'",
        'uid'           => "I NOTNULL DEFAULT 0",
        'newemail'      => "C(60) NOTNULL DEFAULT ''",
        'verifycode'    => "C(138) NOTNULL DEFAULT ''",
        'created_dt'    => "T DEFAULT NULL",
    ];

    // Sessions Table
    // Version 1.11 through current(inclusive)
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
    $dbinfo['session_info'] = 'session_info';
    $dbinfo['session_info_column'] = [
        'sessid'    => 'sessid',
        'ipaddr'    => 'ipaddr',
        'lastused'  => 'lastused',
        'uid'       => 'uid',
        'remember'  => 'remember',
        'vars'      => 'vars'
    ];

    $dbinfo['session_info_column_def'] = [
        'sessid'    => "C(60) PRIMARY NOTNULL DEFAULT ''",
        'ipaddr'    => "C(40) NOTNULL DEFAULT ''",
        'lastused'  => "T DEFAULT '1970-01-01 00:00:00'",
        'uid'       => "I DEFAULT '0'",
        'remember'  => "I1 NOTNULL DEFAULT '0'",
        'vars'      => "XL NOTNULL"
    ];

    // Return the table information
    return $dbinfo;
}

/**
 * Populate pntables array for Users module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @return array The table information
 */
function Users_tables_for_113()
{
    // Initialise table array
    $dbinfo = [];

    // Main Users table
    // Version 1.11 through 1.13 (inclusive)
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
    // activated        - Account State (was Activated?): The user's current state, see Users_Constant::ACTIVE_* for defined constants
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
    $dbinfo['users'] = 'users';
    $dbinfo['users_column'] = [
        'uid'             => 'uid',
        'uname'           => 'uname',
        'email'           => 'email',
        'user_regdate'    => 'user_regdate',
        'user_viewemail'  => 'user_viewemail',
        'user_theme'      => 'user_theme',
        'pass'            => 'pass',
        'storynum'        => 'storynum',
        'ublockon'        => 'ublockon',
        'ublock'          => 'ublock',
        'theme'           => 'theme',
        'counter'         => 'counter',
        'activated'       => 'activated',
        'lastlogin'       => 'lastlogin',
        'validfrom'       => 'validfrom',
        'validuntil'      => 'validuntil',
        'hash_method'     => 'hash_method',
    ];
    $dbinfo['users_column_def'] = [
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
        'hash_method'     => "I1 NOTNULL DEFAULT '8'",
    ];

    $dbinfo['users_column_idx'] = [
        'uname' => 'uname',
        'email' => 'email'
    ];

    $dbinfo['users_db_extra_enable_attribution'] = true;
    $dbinfo['users_primary_key_column'] = 'uid';

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

    // Version 1.11 through 1.13 (inclusive)
    $dbinfo['users_temp'] = 'users_temp';
    $dbinfo['users_temp_column'] = [
        'tid'          => 'tid',
        'uname'        => 'uname',
        'email'        => 'email',
        'femail'       => 'femail',
        'pass'         => 'pass',
        'dynamics'     => 'dynamics',
        'comment'      => 'comment',
        'type'         => 'type',
        'tag'          => 'tag',
        'hash_method'  => 'hash_method'
    ];
    $dbinfo['users_temp_column_def'] = [
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
    ];
    $dbinfo['users_temp_db_extra_enable_attribution'] = true;
    $dbinfo['users_temp_primary_key_column'] = 'tid';

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
    $dbinfo['session_info'] = 'session_info';
    $dbinfo['session_info_column'] = [
        'sessid'    => 'sessid',
        'ipaddr'    => 'ipaddr',
        'lastused'  => 'lastused',
        'uid'       => 'uid',
        'remember'  => 'remember',
        'vars'      => 'vars'
    ];

    $dbinfo['session_info_column_def'] = [
        'sessid'    => "C(40) PRIMARY NOTNULL DEFAULT ''",
        'ipaddr'    => "C(32) NOTNULL DEFAULT ''",
        'lastused'  => "T DEFAULT '1970-01-01 00:00:00'",
        'uid'       => "I DEFAULT '0'",
        'remember'  => "I1 NOTNULL DEFAULT '0'",
        'vars'      => "XL NOTNULL"
    ];

    // Return the table information
    return $dbinfo;
}
