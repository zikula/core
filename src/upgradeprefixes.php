<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Installer
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');
ini_set('memory_limit', '64M');
ini_set('max_execution_time', 86400);

include 'lib/bootstrap.php';
ZLoader::addAutoloader('Users', 'system/Users/lib', '_');

$eventManager = $core->getEventManager();
$eventManager->attach('core.init', 'upgrade_suppressErrors');

// load zikula core
define('_ZINSTALLVER', Zikula_Core::VERSION_NUM);
define('_Z_MINUPGVER', '1.2.0');

// Signal that upgrade is running.
$GLOBALS['_ZikulaUpgrader'] = array();

// include config file for retrieving name of temporary directory
$GLOBALS['ZConfig']['System']['multilingual'] = true;

$GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_ATTRIBUTION'] = false;
$GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_LOGGING'] = false;
$GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_META'] = false;

// Lazy load DB connection to avoid testing DSNs that are not yet valid (e.g. no DB created yet)
$dbEvent = new Zikula_Event('doctrine.init_connection', null, array('lazy' => true));
$connection = $eventManager->notify($dbEvent)->getData();

// check if core tables needs 1.3-dev update
$columns = upgrade_getColumnsForTable($connection, 'modules');

if (in_array('z_id', (array)array_keys($columns))) {
    upgrade_columns($connection);
}

$core->init(Zikula_Core::STAGE_ALL);

$prefix = FormUtil::getPassedValue('prefix', $GLOBALS['ZConfig']['System']['prefix'], 'GETPOST');

$tables = $connection->import->listTables();

$commands = array();
foreach ($tables as $table) {
    if (strpos($table, "{$prefix}_") === 0) {
        $newname = substr($table, strlen("{$prefix}_"));
        $commands[] = "RENAME TABLE {$table} TO {$newname}";
    }
}

foreach ($commands as $sql) {
    $stmt = $connection->prepare($sql);
    $stmt->execute();
}

echo "<h1>Prefix '$prefix' removed successfully!</h1>";

/**
 * Suppress errors event listener.
 *
 * @param Zikula_Event $event Event.
 *
 * @return void
 */
function upgrade_suppressErrors(Zikula_Event $event)
{
    if (!$event['stage'] == Zikula_Core::STAGE_CONFIG) {
        return;
    }

    error_reporting(~E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
    $GLOBALS['ZConfig']['System']['development'] = 0;
}

/**
 * Get tables in database from current connection.
 *
 * @param object $connection PDO connection.
 * @param string $tableName  The name of the table.
 *
 * @return array
 */
function upgrade_getColumnsForTable($connection, $tableName)
{
    $tables = $connection->import->listTables();
    if (!$tables) {
        die(__('FATAL ERROR: Cannot start, unable access database.'));
    }

    if (in_array($GLOBALS['ZConfig']['System']['prefix'] . "_$tableName", $tables)) {
        try {
            return $connection->import->listTableColumns($GLOBALS['ZConfig']['System']['prefix'] . "_$tableName");
        } catch (Exception $e) {
            // TODO - do something with the exception here?
        }
    }

    return array();
}

/**
 * Standardise table columns.
 *
 * @param PDOConnection $connection The PDO connection instance.
 *
 * @return void
 */
function upgrade_columns($connection)
{
    $prefix = $GLOBALS['ZConfig']['System']['prefix'];
    $commands = array();
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE z_cid cid INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE z_name name VARCHAR(32) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE z_description description VARCHAR(254) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE z_order sortorder INT(11) NOT NULL DEFAULT  '0'";
    $commands[] = "RENAME TABLE {$prefix}_admin_category TO admin_category";

    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE z_amid amid INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE z_mid mid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE z_cid cid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE z_order sortorder INT(11) NOT NULL DEFAULT  '0'";
    $commands[] = "RENAME TABLE {$prefix}_admin_module TO admin_module";

    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_bid bid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_bkey bkey VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_title title VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_content content LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_description description LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_url url LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_mid mid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_filter filter LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_active active TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_collapsable collapsable INT(11) DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_defaultstate defaultstate INT(11) DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_refresh refresh INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_last_update last_update DATETIME NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE z_language language VARCHAR(30) NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_blocks TO blocks";

    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE z_pid pid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE z_bid bid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE z_order sortorder INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_block_placements TO block_placements";

    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE z_pid pid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE z_name name VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE z_description description VARCHAR(255) NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_block_positions TO block_positions";

    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE z_uid uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE z_bid bid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE z_active active TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE z_last_update last_update DATETIME";
    $commands[] = "RENAME TABLE {$prefix}_userblocks TO userblocks";

    $commands[] = "ALTER TABLE {$prefix}_group_membership CHANGE z_gid gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_membership CHANGE z_uid uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_group_membership TO group_membership";

    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_gid gid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_name name VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_gtype gtype TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_description description VARCHAR(200) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_prefix prefix VARCHAR(25) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_state state TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_nbuser nbuser INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_nbumax nbumax INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_link link INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE z_uidmaster uidmaster INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_groups TO groups";

    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE z_app_id app_id INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE z_uid uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE z_gid gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE z_application application LONGBLOB NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE z_status status TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_group_applications TO group_applications";

    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_id id BIGINT AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_object object VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_action action VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_smodule smodule VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_stype stype VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_tarea tarea VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_tmodule tmodule VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_ttype ttype VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_tfunc tfunc VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE z_sequence sequence INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_hooks TO hooks";

    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_name name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_type type TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_displayname displayname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_url url VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_description description VARCHAR(255) NOT NULL";

    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_directory directory VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_version version VARCHAR(10) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_capabilities capabilities LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_state state SMALLINT(6) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_securityschema securityschema LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_core_min core_min VARCHAR(9) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE z_core_max core_max VARCHAR(9) NOT NULL";
    $commands[] = "UPDATE {$prefix}_modules SET name = 'Extensions', displayname = 'Extensions manager', url = 'extensions', description = 'Manage your modules and plugins.', directory =  'Extensions', securityschema = 'a:1:{s:9:\"Extensions::\";s:2:\"::\";}' WHERE {$prefix}_modules.name = 'Modules'";
    $commands[] = "RENAME TABLE {$prefix}_modules TO modules";

    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE z_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE z_modname modname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE z_name name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE z_value value LONGTEXT";
    $commands[] = "UPDATE {$prefix}_module_vars SET modname='Extensions' WHERE modname='Modules'";
    $commands[] = "RENAME TABLE {$prefix}_module_vars TO module_vars";

    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE z_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE z_modid modid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE z_modname modname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE z_minversion minversion VARCHAR(10) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE z_maxversion maxversion VARCHAR(10) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE z_status status TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_module_deps TO module_deps";

    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE z_pid pid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE z_gid gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE z_sequence sequence INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE z_realm realm INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE z_component component VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE z_instance instance VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE z_level level INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE z_bond bond INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_group_perms TO group_perms";

    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE z_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE z_search search VARCHAR(50) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE z_count scount INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE z_date date DATE";
    $commands[] = "RENAME TABLE {$prefix}_search_stat TO search_stat";

    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE z_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE z_title title VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE z_text text LONGTEXT";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE z_module module VARCHAR(100)";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE z_extra extra VARCHAR(100)";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE z_created created DATETIME";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE z_found found DATETIME";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE z_sesid sesid VARCHAR(50)";
    $commands[] = "RENAME TABLE {$prefix}_search_result TO search_result";

    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_name name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_type type TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_displayname displayname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_description description VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_directory directory VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_version version VARCHAR(10) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_contact contact VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_admin admin TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_user user TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_system system TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_state state TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE z_xhtml xhtml TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_themes TO themes";

    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_uid uid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_uname uname VARCHAR(25) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_email email VARCHAR(60) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_pass pass VARCHAR(128) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_passreminder passreminder VARCHAR(138) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_activated activated TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_approved_date approved_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00'";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_approved_by approved_by INT( 11 ) NOT NULL DEFAULT '0'";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_user_regdate user_regdate DATETIME DEFAULT '1970-01-01 00:00:00' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_lastlogin lastlogin DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00'";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_theme theme VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_ublockon ublockon TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_ublock ublock LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_tz tz VARCHAR(30) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE z_locale locale VARCHAR(5) NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_users TO users";

    $commands[] = "ALTER TABLE {$prefix}_users_verifychg CHANGE z_id id INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_users_verifychg CHANGE z_changetype changetype TINYINT(4) NOT NULL DEFAULT '0'";
    $commands[] = "ALTER TABLE {$prefix}_users_verifychg CHANGE z_uid uid INT(11) NOT NULL DEFAULT '0'";
    $commands[] = "ALTER TABLE {$prefix}_users_verifychg CHANGE z_newemail newemail VARCHAR(60) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_verifychg CHANGE z_verifycode verifycode VARCHAR(138) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_verifychg CHANGE z_created_dt created_dt DATETIME NULL DEFAULT NULL";
    $commands[] = "RENAME TABLE {$prefix}_users_verifychg TO users_verifychg";

    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE z_sessid sessid VARCHAR(40) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE z_ipaddr ipaddr VARCHAR(32) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE z_lastused lastused DATETIME DEFAULT '1970-01-01 00:00:00'";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE z_uid uid INT(11) DEFAULT '0'";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE z_remember remember TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE z_vars vars LONGTEXT NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_session_info TO session_info";

    $commands[] = "RENAME TABLE {$prefix}_categories_category TO categories_category";
    $commands[] = "ALTER TABLE categories_category
                        CHANGE cat_id id INT(11) NOT NULL AUTO_INCREMENT,
                        CHANGE cat_parent_id parent_id INT(11) NOT NULL DEFAULT '1',
                        CHANGE cat_is_locked is_locked TINYINT(4) NOT NULL DEFAULT '0',
                        CHANGE cat_is_leaf is_leaf TINYINT(4) NOT NULL DEFAULT '0',
                        CHANGE cat_name name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
                        CHANGE cat_value value VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
                        CHANGE cat_sort_value sort_value INT(11) NOT NULL DEFAULT '0',
                        CHANGE cat_display_name display_name LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE cat_display_desc display_desc LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE cat_path path LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE cat_ipath ipath VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
                        CHANGE cat_status status VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A',
                        CHANGE cat_obj_status obj_status VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A',
                        CHANGE cat_cr_date cr_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE cat_cr_uid cr_uid INT(11) NOT NULL DEFAULT '0',
                        CHANGE cat_lu_date lu_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE cat_lu_uid lu_uid INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_categories_mapmeta TO categories_mapmeta";
    $commands[] = "ALTER TABLE categories_mapmeta
                        CHANGE cmm_id id INT(11) NOT NULL AUTO_INCREMENT ,
                        CHANGE cmm_meta_id meta_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE cmm_category_id category_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE cmm_obj_status obj_status VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A',
                        CHANGE cmm_cr_date cr_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE cmm_cr_uid cr_uid INT(11) NOT NULL DEFAULT '0',
                        CHANGE cmm_lu_date lu_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE cmm_lu_uid lu_uid INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_categories_mapobj TO categories_mapobj";
    $commands[] = "ALTER TABLE categories_mapobj
                        CHANGE cmo_id id INT(11) NOT NULL AUTO_INCREMENT,
                        CHANGE cmo_modname modname VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE cmo_table tablename VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE cmo_obj_id obj_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE cmo_obj_idcolumn obj_idcolumn VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'id',
                        CHANGE cmo_reg_id reg_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE cmo_reg_property reg_property VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE cmo_category_id category_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE cmo_obj_status obj_status VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A',
                        CHANGE cmo_cr_date cr_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE cmo_cr_uid cr_uid INT(11) NOT NULL DEFAULT '0',
                        CHANGE cmo_lu_date lu_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE cmo_lu_uid lu_uid INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_categories_registry TO categories_registry";
    $commands[] = "ALTER TABLE categories_registry
                        CHANGE crg_id id INT(11) NOT NULL AUTO_INCREMENT,
                        CHANGE crg_modname modname VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE crg_table tablename VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE crg_property property VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE crg_category_id category_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE crg_obj_status obj_status VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A',
                        CHANGE crg_cr_date cr_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE crg_cr_uid cr_uid INT(11) NOT NULL DEFAULT '0',
                        CHANGE crg_lu_date lu_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE crg_lu_uid lu_uid INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_objectdata_attributes TO objectdata_attributes";
    $commands[] = "ALTER TABLE objectdata_attributes
                        CHANGE oba_id id INT(11) NOT NULL AUTO_INCREMENT,
                        CHANGE oba_attribute_name attribute_name VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE oba_object_id object_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE oba_object_type object_type VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE oba_value value TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
                        CHANGE oba_obj_status obj_status VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A',
                        CHANGE oba_cr_date cr_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE oba_cr_uid cr_uid INT(11) NOT NULL DEFAULT '0',
                        CHANGE oba_lu_date lu_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE oba_lu_uid lu_uid INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_objectdata_log TO objectdata_log";
    $commands[] = "ALTER TABLE objectdata_log
                        CHANGE obl_id id INT(11) NOT NULL AUTO_INCREMENT,
                        CHANGE obl_object_type object_type VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE obl_object_id object_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE obl_op op VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE obl_diff diff TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obl_obj_status obj_status VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A',
                        CHANGE obl_cr_date cr_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE obl_cr_uid cr_uid INT(11) NOT NULL DEFAULT '0',
                        CHANGE obl_lu_date lu_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE obl_lu_uid lu_uid INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_objectdata_meta TO objectdata_meta";
    $commands[] = "ALTER TABLE objectdata_meta
                        CHANGE obm_id id INT(11) NOT NULL AUTO_INCREMENT,
                        CHANGE obm_module module VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE obm_table tablename VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE obm_idcolumn idcolumn VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                        CHANGE obm_obj_id obj_id INT(11) NOT NULL DEFAULT '0',
                        CHANGE obm_permissions permissions VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_title dc_title VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_author dc_author VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_subject dc_subject VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_keywords dc_keywords VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_description dc_description VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_publisher dc_publisher VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_contributor dc_contributor VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_startdate dc_startdate DATETIME NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE obm_dc_enddate dc_enddate DATETIME NULL DEFAULT '1970-01-01 00:00:00',
                        CHANGE obm_dc_type dc_type VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_format dc_format VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_uri dc_uri VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_source dc_source VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_language dc_language VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_relation dc_relation VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_coverage dc_coverage VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_entity dc_entity VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_comment dc_comment VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_dc_extra dc_extra VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                        CHANGE obm_obj_status obj_status VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A',
                        CHANGE obm_cr_date cr_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE obm_cr_uid cr_uid INT(11) NOT NULL DEFAULT '0',
                        CHANGE obm_lu_date lu_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE obm_lu_uid lu_uid INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "ALTER TABLE {$prefix}_sc_intrusion CHANGE z_id id INT( 11 ) NOT NULL AUTO_INCREMENT ,
                        CHANGE z_name name VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                        CHANGE z_tag tag VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
                        CHANGE z_value value LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                        CHANGE z_page page LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                        CHANGE z_uid uid INT( 11 ) NULL DEFAULT NULL ,
                        CHANGE z_ip ip VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                        CHANGE z_impact impact INT( 11 ) NOT NULL DEFAULT '0',
                        CHANGE z_filters filters LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";

    $commands[] = "RENAME TABLE {$prefix}_sc_intrusion TO sc_intrusion";

    $commands[] = "RENAME TABLE {$prefix}_hook_area TO hook_area";
    $commands[] = "RENAME TABLE {$prefix}_hook_binding TO hook_binding";
    $commands[] = "RENAME TABLE {$prefix}_hook_provider TO hook_provider";
    $commands[] = "RENAME TABLE {$prefix}_hook_runtime TO hook_runtime";
    $commands[] = "RENAME TABLE {$prefix}_hook_subscriber TO hook_subscriber";

    $silentCommands = array();
    $silentCommands[] = "ALTER TABLE {$prefix}_message
                            CHANGE z_mid mid INT(11) NOT NULL AUTO_INCREMENT ,
                            CHANGE z_title title VARCHAR(100) NOT NULL DEFAULT  '',
                            CHANGE z_content content LONGTEXT NOT NULL ,
                            CHANGE z_date date INT(11) NOT NULL DEFAULT  '0',
                            CHANGE z_expire expire INT(11) NOT NULL DEFAULT  '0',
                            CHANGE z_active active INT(11) NOT NULL DEFAULT  '1',
                            CHANGE z_view view INT(11) NOT NULL DEFAULT  '1',
                            CHANGE z_language language VARCHAR(30) NOT NULL DEFAULT  ''";

    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE id id INT(11) NOT NULL AUTO_INCREMENT";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE name name VARCHAR(100) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE cdate cdate DATETIME NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE edate edate DATETIME NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE session session VARCHAR(50) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE title title VARCHAR(100) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE ipno ipno VARCHAR(30) NOT NULL";
    $silentCommands[] = "RENAME TABLE {$prefix}_pagelock TO pagelock";

    $silentCommands[] = "RENAME TABLE {$prefix}_message TO message";

    // LONGBLOB is not supported by Doctrine 2
    $silentCommands[] = "ALTER TABLE {$prefix}_workflows CHANGE debug debug LONGTEXT NULL DEFAULT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_workflows CHANGE module module VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_workflows CHANGE schemaname schemaname VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_workflows CHANGE state state VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_workflows CHANGE obj_table obj_table VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_workflows CHANGE obj_idcolumn obj_idcolumn VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
 
    $silentCommands[] = "RENAME TABLE {$prefix}_workflows TO workflows";

    $silentCommands[] = "ALTER TABLE group_applications CHANGE application application LONGTEXT NOT NULL";

    foreach ($commands as $sql) {
        $stmt = $connection->prepare($sql);
        $stmt->execute();
    }

    foreach ($silentCommands as $sql) {
        $stmt = $connection->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            // Silent - trap and toss exceptions.
        }
    }
}
