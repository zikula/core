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
 * Tables definition for object data functionality and workflows.
 *
 * TODO remove for 2.0
 */
function ZikulaSettingsModule_tables()
{
    // Initialise table array
    $dbtables = [];

    $table = 'objectdata_attributes';
    $dbtables['objectdata_attributes'] = $table;
    $columns = [
        'id'             => 'id',
        'attribute_name' => 'attribute_name',
        'object_id'      => 'object_id',
        'object_type'    => 'object_type',
        'value'          => 'value'
    ];
    ObjectUtil::addStandardFieldsToTableDefinition($columns);
    $dbtables['objectdata_attributes_column'] = $columns;
    $dbtables['objectdata_attributes_column_idx'] = [
        'object_type' => 'object_type',
        'object_id' => 'object_id'
    ];

    $tabledef = [
        'id'             => 'I4 PRIMARY AUTO',
        'attribute_name' => "C(80) NOTNULL DEFAULT ''",
        'object_id'      => 'I4 NOTNULL DEFAULT 0',
        'object_type'    => "C(80) NOTNULL DEFAULT ''",
        'value'          => "X NOTNULL DEFAULT ''"
    ];
    ObjectUtil::addStandardFieldsToTableDataDefinition($tabledef);
    $dbtables['objectdata_attributes_column_def'] = $tabledef;

    $table = 'objectdata_log';
    $dbtables['objectdata_log'] = $table;
    $columns = array ('id'           => 'id',
                      'object_type'  => 'object_type',
                      'object_id'    => 'object_id',
                      'op'           => 'op',
                      'diff'         => 'diff');
    ObjectUtil::addStandardFieldsToTableDefinition($columns);
    $dbtables['objectdata_log_column'] = $columns;

    $tabledef = [
        'id' => 'I4 PRIMARY AUTO',
        'object_type' => "C(80) NOTNULL DEFAULT ''",
        'object_id' => 'I4 NOTNULL DEFAULT 0',
        'op' => "C(16) NOTNULL DEFAULT ''",
        'diff' => "X NOT NULL DEFAULT ''"
    ];
    ObjectUtil::addStandardFieldsToTableDataDefinition($tabledef, 'obl_');
    $dbtables['objectdata_log_column_def'] = $tabledef;

    $table = 'objectdata_meta';
    $dbtables['objectdata_meta'] = $table;
    $columns = array ('id'             => 'id',
                      'module'         => 'module',
                      'table'          => 'tablename',
                      'idcolumn'       => 'idcolumn',
                      'obj_id'         => 'obj_id',
                      'permissions'    => 'permissions',
                      'dc_title'       => 'dc_title',
                      'dc_author'      => 'dc_author',
                      'dc_subject'     => 'dc_subject',
                      'dc_keywords'    => 'dc_keywords',
                      'dc_description' => 'dc_description',
                      'dc_publisher'   => 'dc_publisher',
                      'dc_contributor' => 'dc_contributor',
                      'dc_startdate'   => 'dc_startdate',
                      'dc_enddate'     => 'dc_enddate',
                      'dc_type'        => 'dc_type',
                      'dc_format'      => 'dc_format',
                      'dc_uri'         => 'dc_uri',
                      'dc_source'      => 'dc_source',
                      'dc_language'    => 'dc_language',
                      'dc_relation'    => 'dc_relation',
                      'dc_coverage'    => 'dc_coverage',
                      'dc_entity'      => 'dc_entity',
                      'dc_comment'     => 'dc_comment',
                      'dc_extra'       => 'dc_extra');
    ObjectUtil::addStandardFieldsToTableDefinition($columns);
    $dbtables['objectdata_meta_column'] = $columns;

    $tabledef = [
        'id'             => 'I4 PRIMARY AUTO',
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
        'dc_extra'       => "C(255) DEFAULT ''"
    ];
    ObjectUtil::addStandardFieldsToTableDataDefinition($tabledef);
    $dbtables['objectdata_meta_column_def'] = $tabledef;

    // workflow
    $dbtables['workflows'] = 'workflows';
    $dbtables['workflows_column'] = [
        'id'           => 'id',
        'metaid'       => 'metaid',
        'module'       => 'module',
        'schemaname'   => 'schemaname',
        'state'        => 'state',
        'type'         => 'type',
        'obj_table'    => 'obj_table',
        'obj_idcolumn' => 'obj_idcolumn',
        'obj_id'       => 'obj_id',
        'busy'         => 'busy',
        'debug'        => 'debug'
    ];

    $dbtables['workflows_column_def'] = [
        'id'           => 'I NOTNULL AUTO PRIMARY',
        'metaid'       => 'I NOTNULL DEFAULT 0',
        'module'       => "C(255) NOTNULL DEFAULT ''",
        'schemaname'   => "C(255) NOTNULL DEFAULT ''",
        'state'        => "C(255) NOTNULL DEFAULT ''",
        'type'         => 'I2 NOTNULL DEFAULT 1',
        'obj_table'    => "C(40) NOTNULL DEFAULT ''",
        'obj_idcolumn' => "C(40) NOTNULL DEFAULT ''",
        'obj_id'       => 'I4 NOTNULL DEFAULT 0',
        'busy'         => 'I NOTNULL DEFAULT 0',
        'debug'        => 'XL'
    ];

    // addtitional indexes
    $dbtables['workflows_column_idx'] = [
        'obj_table' => 'obj_table',
        'obj_idcolumn' => 'obj_idcolumn',
        'obj_id' => 'obj_id'
    ];

    return $dbtables;
}
