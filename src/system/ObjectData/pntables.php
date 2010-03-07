<?php
/**
 * Zikula Application Framework
 *
 * @copyright Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */


/**
 * This function is called internally by the core whenever the module is loaded.
 */
function ObjectData_pntables()
{
    // Initialise table array
    $pntables = array();

    $table = DBUtil::getLimitedTablename('objectdata_attributes');
    $pntables['objectdata_attributes'] = $table;
    $columns = array ('id'             => 'oba_id',
                      'attribute_name' => 'oba_attribute_name',
                      'object_id'      => 'oba_object_id',
                      'object_type'    => 'oba_object_type',
                      'value'          => 'oba_value'
                      );
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'oba_');
    $pntables['objectdata_attributes_column'] = $columns;

    $tabledef = array('id'             => 'I4 PRIMARY AUTO',
                      'attribute_name' => "C(80) NOTNULL DEFAULT ''",
                      'object_id'      => 'I4 NOTNULL DEFAULT 0',
                      'object_type'    => "C(80) NOTNULL DEFAULT ''",
                      'value'          => "X NOTNULL DEFAULT ''");
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'oba_');
    $pntables['objectdata_attributes_column_def'] = $tabledef;

    $table = DBUtil::getLimitedTablename('objectdata_log');
    $pntables['objectdata_log'] = $table;
    $columns = array ('id'           => 'obl_id',
                      'object_type'  => 'obl_object_type',
                      'object_id'    => 'obl_object_id',
                      'op'           => 'obl_op',
                      'diff'         => 'obl_diff');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'obl_');
    $pntables['objectdata_log_column'] = $columns;

    $tabledef = array('id' => 'I4 PRIMARY AUTO',
                      'object_type' => "C(80) NOTNULL DEFAULT ''",
                      'object_id' => 'I4 NOTNULL DEFAULT 0',
                      'op' => "C(16) NOTNULL DEFAULT ''",
                      'diff' => "X NOT NULL DEFAULT ''");
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'obl_');
    $pntables['objectdata_log_column_def'] = $tabledef;

    $table = DBUtil::getLimitedTablename('objectdata_meta');
    $pntables['objectdata_meta'] = $table;
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
    $pntables['objectdata_meta_column'] = $columns;

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
    $pntables['objectdata_meta_column_def'] = $tabledef;

    return $pntables;
}
