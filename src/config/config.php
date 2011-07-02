<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPL version 3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

global $ZConfig;
$ZConfig['System']['installed'] = 0;        // installer will change this during installation
$ZConfig['System']['temp'] = 'ztemp';       // location of temporary folder
$ZConfig['System']['datadir'] = 'userdata';     // location of site data files
$ZConfig['System']['prefix'] = '';         // database prefix (deprecated).
$ZConfig['System']['development'] = 0;      // development mode 1/0 for on or off.  Disable in production mode.
$ZConfig['System']['legacy_prefilters'] = true; // enable legacy template prefilters
$ZConfig['System']['compat_layer'] = true;  // enable loading of compat layers
$ZConfig['System']['system.chmod_dir'] = 0777;  // The default chmod for new directories created by Zikula.

// ----------------------------------------------------------------------
// This is the definition for the default Zikula system database.
// It must be named 'default'
// ----------------------------------------------------------------------
$ZConfig['DBInfo']['databases']['default']['host'] = 'localhost';
$ZConfig['DBInfo']['databases']['default']['user'] = 'root';
$ZConfig['DBInfo']['databases']['default']['password'] = '';
$ZConfig['DBInfo']['databases']['default']['dbname'] = 'test';
$ZConfig['DBInfo']['databases']['default']['dbdriver'] = 'mysql';
$ZConfig['DBInfo']['databases']['default']['dbtabletype'] = 'myisam';
$ZConfig['DBInfo']['databases']['default']['charset'] = 'utf8';
$ZConfig['DBInfo']['databases']['default']['collate'] = 'utf8_general_ci';
// additional DB can be configured here as above external2, external3 etc...

// ----------------------------------------------------------------------
// Error Reporting
// ----------------------------------------------------------------------
// This level of reporting only affect PHP's own native handlers (if you enable them).
// These settings have no effect on Zikula's error handling and reporting.
$ZConfig['Debug']['error_reporting_development'] = E_ALL; // preconfigured level
$ZConfig['Debug']['error_reporting_production'] = E_ALL & ~E_NOTICE & ~E_WARNING; // preconfigured level
$ZConfig['Debug']['debug_key'] = ($ZConfig['System']['development'] ? 'error_reporting_development' : 'error_reporting_production');
error_reporting($ZConfig['Debug'][$ZConfig['Debug']['debug_key']]); // now set the appropriate level

// ----------------------------------------------------------------------
// Logging Settings
// ----------------------------------------------------------------------
$ZConfig['Log']['log_dest'] = 'FILE';   // the default logging destination. Can be "FILE", "PRINT", "EMAIL" or "DB".
$ZConfig['Log']['log_dir'] = $ZConfig['System']['temp'] . '/error_logs/';   // the directory containing all log files
$ZConfig['Log']['log_file'] = $ZConfig['Log']['log_dir'] . 'zikula-%s.log'; // %s is where todays date will go
$ZConfig['Log']['log_file_uid'] = 0;                                        // wether or not a separate log file is used for each user. The filename is derived from $ZConfig['Log']['log_file']
$ZConfig['Log']['log_file_date_format'] = 'Ymd';                            // dateformat to be used for the generated log filename
$ZConfig['Log']['log_date_format'] = "Y-m-d H:i:s";                         // 2011-09-15 12:24:56
$ZConfig['Log']['log_level_dest'] = array('DB' => 'PRINT');                 // array of level-specific log destinations
$ZConfig['Log']['log_level_files'] = array('DB' => $ZConfig['System']['temp'] . '/error_logs/zikula-sql-%s.log'); // array of level-specific log files (only used if destination=="FILE")

$ZConfig['Log']['log.apache_uname'] = 0;          // log username to apache logs: please see documentation.  Please check you country's local law covering the logging of personally identifiable user data before enabling.

$ZConfig['Log']['log.enabled'] = 1;                 // Enable to allow Zikula to handle errors, 0 passes everything to PHP directly.
$ZConfig['Log']['log.to_display'] = 1;              // Display errors.
$ZConfig['Log']['log.display_level'] = 4;           // 0 - EMERG, 1 - CRIT, 2 - ALERT, 3 - ERR, 4 - WARN, 5 - NOTICE, 6 - INFO, 7 - DEBUG
$ZConfig['Log']['log.display_ajax_level'] = 4;      // 0 - EMERG, 1 - CRIT, 2 - ALERT, 3 - ERR, 4 - WARN, 5 - NOTICE, 6 - INFO, 7 - DEBUG
$ZConfig['Log']['log.to_file'] = 0;                 // Log to file 1 yes, 0 no.
$ZConfig['Log']['log.file_level'] = 5;              // 0 - EMERG, 1 - CRIT, 2 - ALERT, 3 - ERR, 4 - WARN, 5 - NOTICE, 6 - INFO, 7 - DEBUG


$ZConfig['Log']['log.show_php_errorhandler'] = 0;   // Allow PHP error handlers to display additionally? Set this to 1 if you want to see PHP's error handler
                                                    // If you have XDebug installed, setting this will allow XDebug to output.
                                                    // If log.display_template set, PHP's handlers will not show!

$ZConfig['Log']['log.display_template'] = 0;        // Overrides PHP's output handler if activated by log.show_php_error_handler and gains full control of output.
                                                    // This setting is generally NOT desirable when developing/debugging.

$ZConfig['Log']['log.to_debug_toolbar'] = 0;        // 1 to show the debug toolbar (reqires development 1), 0 to disable
$ZConfig['Log']['log.to_debug_toolbar_output'] = 0; // Debug toolbar output type: 0 - normal toolbar, 1 - json output, 2 - both
$ZConfig['Log']['log.to_debug_toolbar_seckey'] = ''; // Security key for debug toolbar output of json type
                                                     // If defined - it's required that http request contains custom header
                                                     // "HTTP_X_ZIKULA_DEBUGTOOLBAR" equal to this key, otherwise no data is returned.
$ZConfig['Log']['log.sql.to_display'] = 0;          // Display sql queries.
$ZConfig['Log']['log.sql.to_file'] = 0;             // Log sql queries to file.

$ZConfig['Log']['debug.display_pagerendertime'] = 0;      // display page render time, 0 to disable

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
$ZConfig['System']['dbcache.enable'] = 1;             // 0 to disable, 1 to enable
$ZConfig['System']['dbcache.type'] = 'Array';         // Memcache, Apc, Array, Db, Xcache (todo: Memcached, File)

// CACHE_SERVERS valid for Memcache/d only.
// array of arrays: params according to the addServer methods at e.g.
// http://php.net/manual/memcached.addservers.php or
// http://php.net/manual/function.memcache-addserver.php
$ZConfig['System']['dbcache.servers'][] = array('host' => 'localhost', 'port' => '11211', 'weight' => 1);
$ZConfig['System']['dbcache.compression'] = true; // true/false valid for dbcache.type = Memcache/d

// For pure Doctrine Queries only. Effective only when dbcache.enable = true and dbcache.cache_result = 1
// http://www.doctrine-project.org/projects/orm/1.2/docs/manual/caching/en#query-cache-result-cache:result-cache
$ZConfig['System']['dbcache.cache_result'] = 0;      // 1 to enable or 0 to disable.
$ZConfig['System']['dbcache.cache_result_ttl'] = 30; // seconds, 3600 = 1 hour.

// Initialize multisites array
$ZConfig['Multisites'] = array();
$ZConfig['Multisites']['multisites.enabled'] = 0;
$ZConfig['Multisites']['protected.systemvars'] = array();
