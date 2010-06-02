<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


// ----------------------------------------------------------------------
// NOTICE
// Zikula includes an install script which can populate the database
// and write this config file automatically.  There is normally no need
// to manually edit this file!
// ----------------------------------------------------------------------

// ----------------------------------------------------------------------
// The following define some global settings for the application
// ----------------------------------------------------------------------
global $ZConfig;
$ZConfig['System']['installed'] = 0;        // installer will change this during installation
$ZConfig['System']['temp'] = 'ztemp';       // installer will change this during installation
$ZConfig['System']['prefix'] = 'z';         // installer will change this during installation
$ZConfig['System']['development'] = 0;      // should be set to 0/false when cutting a release for production use
$ZConfig['System']['default_timezone'] = 'GMT'; // TZ timezone
$ZConfig['System']['legacy_prefilters'] = true; // enable legacy template prefilters
$ZConfig['System']['compat_layer'] = true;  // enable loading of compat layers

//  Uncomment this line temporarily if you crash the HTMLPurifier configuration
//$ZConfig['System']['outputfilter'] = 0;
//  Uncomment this line temporarily if you crash the PHPIDS configuration
//$ZConfig['System']['useids'] = 0;

// ----------------------------------------------------------------------
// Database & System Config
//      dsn:          Connection details for database in the form
//                    engine://user:pass@localhost/database
//      engine types: mysql, mysqli, pgsql, and oci
//      dbtabletype:  type of table for MySQL database: MYISAM, INNODB
// ----------------------------------------------------------------------

// ----------------------------------------------------------------------
// This is the definition for the default Zikula system database.
// It must be named 'default'
// ----------------------------------------------------------------------
$ZConfig['DBInfo']['default']['dsn'] = 'mysql://user:password@localhost/test';
$ZConfig['DBInfo']['default']['dbtabletype'] = 'myisam';
$ZConfig['DBInfo']['default']['dbcharset'] = 'utf8';
$ZConfig['DBInfo']['default']['dbcollate'] = 'utf8_general_ci';


// ----------------------------------------------------------------------
// The following define the list of databases the system can access. You
// can define as many as you like provided you give each one a unique
// name (the key value following the DBInfo array element)
// ----------------------------------------------------------------------
$ZConfig['DBInfo']['external1']['dsn'] = 'mysql://user:password@localhost/test2';
$ZConfig['DBInfo']['external1']['dbtabletype'] = 'innodb';
$ZConfig['DBInfo']['external1']['dbcharset'] = 'utf8';
$ZConfig['DBInfo']['external1']['dbcollate'] = 'utf8_general_ci';

// additional DB can be configured here as above external2, external3 etc...

// ----------------------------------------------------------------------
// Debugging/Tracing settings
// ----------------------------------------------------------------------
$ZConfig['Debug']['debug'] = 0;             //
$ZConfig['Debug']['pagerendertime'] = 0;    // display page render time, 0 to disable
$ZConfig['Debug']['sql_verbose'] = 0;       // sql debug flag, generates lots of print output
$ZConfig['Debug']['sql_count'] = 0;         // count sql statements, 0 to disable
$ZConfig['Debug']['sql_time'] = 0;          // time sql statements, 0 to disable
$ZConfig['Debug']['sql_detail'] = 0;        // collect executed sql statements, 0 to disable
$ZConfig['Debug']['sql_data'] = 0;          // collect selected data, 0 to disable
$ZConfig['Debug']['sql_user'] = 0;          // user filter, 0 for all, any other number is a user-id, can also be an array
$ZConfig['Debug']['cache_time'] = 0;        // object cache: time cache access, 0 to disable
$ZConfig['Debug']['cache_detail'] = 0;      // object cache: collect executed cache hits, 0 to disable
$ZConfig['Debug']['cache_user'] = 0;        // object cache: user filter, 0 for all, any other number is a user-id, can also be an array


// ----------------------------------------------------------------------
// Error Reporting
// ----------------------------------------------------------------------
$ZConfig['Debug']['error_reporting_development'] = E_ALL; // preconfigured level
$ZConfig['Debug']['error_reporting_production'] = E_ALL & ~E_NOTICE & ~E_WARNING; // preconfigured level
$ZConfig['Debug']['debug_key'] = ($ZConfig['System']['development'] ? 'error_reporting_development' : 'error_reporting_production');
error_reporting($ZConfig['Debug'][$ZConfig['Debug']['debug_key']]); // now set the appropriate level


// ----------------------------------------------------------------------
// Logging Settings
// ----------------------------------------------------------------------
$ZConfig['Log']['log_enabled'] = 0;     // global logging to on/off switch for 'log_dest' (0=off, 1=on)
$ZConfig['Log']['log_dest'] = 'FILE';   // the default logging destination. Can be "FILE", "PRINT", "EMAIL" or "DB".
$ZConfig['Log']['log_dir'] = $ZConfig['System']['temp'] . '/error_logs/';   // the directory containing all log files
$ZConfig['Log']['log_file'] = $ZConfig['Log']['log_dir'] . 'zikula-%s.log'; // %s is where todays date will go
$ZConfig['Log']['log_file_uid'] = 0;                                        // wether or not a separate log file is used for each user. The filename is derived from $ZConfig['Log']['log_file']
$ZConfig['Log']['log_file_date_format'] = 'Ymd';                            // dateformat to be used for the generated log filename
$ZConfig['Log']['log_maxsize'] = 1.0;                                       // value in MB. Decimal is OK. (Use 0 for no limit)
$ZConfig['Log']['log_user'] = 0;                                            // user filter for logging, 0 for all, can also be an array
$ZConfig['Log']['log_levels'] = array('CORE', 'DB', 'DEFAULT', 'WARNING', 'FATAL', 'STRICT'); // User defined. To get everything use: $log_level = array("All");
$ZConfig['Log']['log_show_errors'] = true;                                  // Show php logging errors on screen (Use while developing only)
$ZConfig['Log']['log_date_format'] = "Y-m-d H:i:s";                         // 2006-07-19 18:41:50
$ZConfig['Log']['log_level_dest'] = array('DB' => 'PRINT');                 // array of level-specific log destinations
$ZConfig['Log']['log_level_files'] = array('DB' => $ZConfig['System']['temp'] . '/error_logs/zikula-sql-%s.log'); // array of level-specific log files (only used if destination=="FILE")
$ZConfig['Log']['log_keep_days'] = 30;                                      // amount of days to keep log files for (older files will be erased)
$ZConfig['Log']['log_apache_uname'] = 0;                                    // log username to apache logs: please see documentation.  Please check you country's local law covering the logging of personally identifiable user data before enabling.

// ----------------------------------------------------------------------
// The following define some data layer settings
// ----------------------------------------------------------------------
$ZConfig['System']['Z_CONFIG_USE_OBJECT_ATTRIBUTION'] = 0;     // enable universal attribution layer, 0 to turn off
$ZConfig['System']['Z_CONFIG_USE_OBJECT_CATEGORIZATION'] = 1;  // categorization/filtering services, 0 to turn off
$ZConfig['System']['Z_CONFIG_USE_OBJECT_LOGGING'] = 0;         // object audit trail logging, 0 to turn off
$ZConfig['System']['Z_CONFIG_USE_OBJECT_META'] = 0;            // meta-data services, 0 to turn off
$ZConfig['System']['Z_CONFIG_USE_TRANSACTIONS'] = 0;           // run request as a transaction, 0 to turn off

// ----------------------------------------------------------------------
// Database cache settings
// ----------------------------------------------------------------------
$ZConfig['System']['OBJECT_CACHE_ENABLE'] = 0;          // 0 to disable, 1 to enable
$ZConfig['System']['OBJECT_CACHE_TYPE'] = 'Memcache';   // Memcache, Apc, Array, Db, Xcache (todo: Memcached, File)
// array of arrays: params according to the addServer methods at e.g.
// http://php.net/manual/memcached.addservers.php or
// http://php.net/manual/function.memcache-addserver.php
$ZConfig['System']['OBJECT_CACHE_SERVERS'][] = array('host' => 'localhost', 'port' => '11211', 'weight' => 1); // APC required no servers

// ----------------------------------------------------------------------
// Initialize runtime variables to sane defaults
// ----------------------------------------------------------------------
global $ZRuntime;
$ZRuntime['sql'] = array();
$ZRuntime['sql_count_request'] = 0;
$ZRuntime['sql_time_request'] = 0;
$ZRuntime['cache_time_request'] = 0;

// * NOTE  If you copy this file to personal_config.php, remove the includes below
if (!strpos(__FILE__, 'personal_config.php')) {
    // Multisites configuration
    if (is_readable('config/multisites_config.php')) {
        include 'config/multisites_config.php';
    }

    // personal configuration
    if (is_readable('config/personal_config.php')) {
        include 'config/personal_config.php';
    }
}