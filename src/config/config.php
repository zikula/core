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
$ZConfig['System']['temp'] = 'ztemp';       // location of temporary folder (this should remain as 'ztemp' since 1.3.6)
$ZConfig['System']['datadir'] = 'userdata';     // location of site data files
$ZConfig['System']['prefix'] = '';         // database prefix (deprecated).
$ZConfig['System']['development'] = 0;      // development mode 1/0 for on or off.  Disable in production mode.
$ZConfig['System']['compat_layer'] = true;  // boolean or version string: enables loading of compat code
                                            // greater than or equal to this version.
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
$ZConfig['Debug']['error_reporting_production'] = E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT; // preconfigured level
$ZConfig['Debug']['debug_key'] = ($ZConfig['System']['development'] ? 'error_reporting_development' : 'error_reporting_production');
error_reporting($ZConfig['Debug'][$ZConfig['Debug']['debug_key']]); // now set the appropriate level

// ----------------------------------------------------------------------
// Logging Settings
// ----------------------------------------------------------------------
$ZConfig['Log']['log.apache_uname'] = 0;          // log username to apache logs: please see documentation.  Please check you country's local law covering the logging of personally identifiable user data before enabling.

$ZConfig['Log']['log.display_template'] = 0;        // Overrides PHP's output handler if activated by log.show_php_error_handler and gains full control of output.
                                                    // This setting is generally NOT desirable when developing/debugging.

$ZConfig['Log']['debug.display_pagerendertime'] = 0;      // display page render time, 0 to disable

// ----------------------------------------------------------------------
// The following define some data layer settings
// ----------------------------------------------------------------------
$ZConfig['System']['Z_CONFIG_USE_OBJECT_ATTRIBUTION'] = 0;     // enable universal attribution layer, 0 to turn off
$ZConfig['System']['Z_CONFIG_USE_OBJECT_CATEGORIZATION'] = 1;  // categorization/filtering services, 0 to turn off
$ZConfig['System']['Z_CONFIG_USE_OBJECT_LOGGING'] = 0;         // object audit trail logging, 0 to turn off
$ZConfig['System']['Z_CONFIG_USE_OBJECT_META'] = 0;            // meta-data services, 0 to turn off

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
// http://docs.doctrine-project.org/projects/doctrine1/en/latest/en/manual/caching.html#result-cache
$ZConfig['System']['dbcache.cache_result'] = 0;      // 1 to enable or 0 to disable.
$ZConfig['System']['dbcache.cache_result_ttl'] = 30; // seconds, 3600 = 1 hour.

// Initialize multisites array
$ZConfig['Multisites'] = array();
$ZConfig['Multisites']['multisites.enabled'] = 0;
$ZConfig['Multisites']['protected.systemvars'] = array();
