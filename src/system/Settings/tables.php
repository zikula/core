<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

function Settings_tables()
{
    // Initialise table array
    $dbtables = array();

    $table = DBUtil::getLimitedTablename('objectdata_attributes');
    $dbtables['objectdata_attributes'] = $table;
    $columns = array ('id'             => 'oba_id',
                      'attribute_name' => 'oba_attribute_name',
                      'object_id'      => 'oba_object_id',
                      'object_type'    => 'oba_object_type',
                      'value'          => 'oba_value'
                      );
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'oba_');
    $dbtables['objectdata_attributes_column'] = $columns;
    $dbtables['objectdata_attributes_column_idx'] = array('object_type' => 'object_type', 'object_id' => 'object_id');

    $tabledef = array('id'             => 'I4 PRIMARY AUTO',
                      'attribute_name' => "C(80) NOTNULL DEFAULT ''",
                      'object_id'      => 'I4 NOTNULL DEFAULT 0',
                      'object_type'    => "C(80) NOTNULL DEFAULT ''",
                      'value'          => "X NOTNULL DEFAULT ''");
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'oba_');
    $dbtables['objectdata_attributes_column_def'] = $tabledef;

    $table = DBUtil::getLimitedTablename('objectdata_log');
    $dbtables['objectdata_log'] = $table;
    $columns = array ('id'           => 'obl_id',
                      'object_type'  => 'obl_object_type',
                      'object_id'    => 'obl_object_id',
                      'op'           => 'obl_op',
                      'diff'         => 'obl_diff');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'obl_');
    $dbtables['objectdata_log_column'] = $columns;

    $tabledef = array('id' => 'I4 PRIMARY AUTO',
                      'object_type' => "C(80) NOTNULL DEFAULT ''",
                      'object_id' => 'I4 NOTNULL DEFAULT 0',
                      'op' => "C(16) NOTNULL DEFAULT ''",
                      'diff' => "X NOT NULL DEFAULT ''");
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'obl_');
    $dbtables['objectdata_log_column_def'] = $tabledef;

    $table = DBUtil::getLimitedTablename('objectdata_meta');
    $dbtables['objectdata_meta'] = $table;
    $columns = array ('id'             => 'obm_id',
                      'module'         => 'obm_module',
                      'table'          => 'obm_table',
                      'idcolumn'       => 'obm_idcolumn',
                      'obj_id'         => 'obm_obj_id',
                      'permissions'    => 'obm_permissions',
                      'dc_title'       => 'obm_dc_title',
                      'dc_author'      => 'obm_dc_author',
                      'dc_subject'     => 'obm_dc_subject',
                      'dc_keywords'    => 'obm_dc_keywords',
                      'dc_description' => 'obm_dc_description',
                      'dc_publisher'   => 'obm_dc_publisher',
                      'dc_contributor' => 'obm_dc_contributor',
                      'dc_startdate'   => 'obm_dc_startdate',
                      'dc_enddate'     => 'obm_dc_enddate',
                      'dc_type'        => 'obm_dc_type',
                      'dc_format'      => 'obm_dc_format',
                      'dc_uri'         => 'obm_dc_uri',
                      'dc_source'      => 'obm_dc_source',
                      'dc_language'    => 'obm_dc_language',
                      'dc_relation'    => 'obm_dc_relation',
                      'dc_coverage'    => 'obm_dc_coverage',
                      'dc_entity'      => 'obm_dc_entity',
                      'dc_comment'     => 'obm_dc_comment',
                      'dc_extra'       => 'obm_dc_extra');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'obm_');
    $dbtables['objectdata_meta_column'] = $columns;

    $tabledef = array('id'             => 'I4 PRIMARY AUTO',
                      'module'         => "C(40) NOTNULL DEFAULT ''",
                      'table'          => "C(40) NOTNULL DEFAULT ''",
                      'idcolumn'       => "C(40) NOTNULL DEFAULT ''",
                      'obj_id'         => 'I4 NOTNULL DEFAULT 0',
                      'permissions'    => "C(255) DEFAULT ''",
                      'dc_title'       => "C(80) DEFAULT ''",
                      'dc_author'      => "C(80) DEFAULT ''",
                      'dc_subject'     => "C(255) DEFAULT ''",
                      'dc_keywords'    => "C(128) DEFAULT ''",
                      'dc_description' => "C(255) DEFAULT ''",
                      'dc_publisher'   => "C(128) DEFAULT ''",
                      'dc_contributor' => "C(128) DEFAULT ''",
                      'dc_startdate'   => "T DEFAULT '1970-01-01 00:00:00'",
                      'dc_enddate'     => "T DEFAULT '1970-01-01 00:00:00'",
                      'dc_type'        => "C(128) DEFAULT ''",
                      'dc_format'      => "C(128) DEFAULT ''",
                      'dc_uri'         => "C(255) DEFAULT ''",
                      'dc_source'      => "C(128) DEFAULT ''",
                      'dc_language'    => "C(32) DEFAULT ''",
                      'dc_relation'    => "C(255) DEFAULT ''",
                      'dc_coverage'    => "C(64) DEFAULT ''",
                      'dc_entity'      => "C(64) DEFAULT ''",
                      'dc_comment'     => "C(255) DEFAULT ''",
                      'dc_extra'       => "C(255) DEFAULT ''");
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'obm_');
    $dbtables['objectdata_meta_column_def'] = $tabledef;

    // workflow
    $dbtables['workflows'] = DBUtil::getLimitedTablename('workflows');
    $dbtables['workflows_column'] = array('id'           => 'id',
                                          'metaid'       => 'metaid',
                                          'module'       => 'module',
                                          'schemaname'   => 'schemaname',
                                          'state'        => 'state',
                                          'type'         => 'type',
                                          'obj_table'    => 'obj_table',
                                          'obj_idcolumn' => 'obj_idcolumn',
                                          'obj_id'       => 'obj_id',
                                          'busy'         => 'busy',
                                          'debug'        => 'debug');

    $dbtables['workflows_column_def'] = array('id'           => 'I NOTNULL AUTO PRIMARY',
                                              'metaid'       => 'I NOTNULL DEFAULT 0',
                                              'module'       => "C(255) NOTNULL DEFAULT ''",
                                              'schemaname'   => "C(255) NOTNULL DEFAULT ''",
                                              'state'        => "C(255) NOTNULL DEFAULT ''",
                                              'type'         => 'I2 NOTNULL DEFAULT 1',
                                              'obj_table'    => "C(40) NOTNULL DEFAULT ''",
                                              'obj_idcolumn' => "C(40) NOTNULL DEFAULT ''",
                                              'obj_id'       => 'I4 NOTNULL DEFAULT 0',
                                              'busy'         => 'I NOTNULL DEFAULT 0',
                                              'debug'        => 'B');

    // addtitional indexes
    $dbtables['workflows_column_idx'] = array('obj_table' => 'obj_table', 'obj_idcolumn' => 'obj_idcolumn', 'obj_id' => 'obj_id');

    return $dbtables;
}
