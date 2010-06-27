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


/**
 * Populate pntables array for Categories module.
 *
 * @return array Array of informations related to the Categories module tables.
 */
function Categories_tables()
{
    // Initialise table array
    $dbtable = array();
    $prefix = System::getVar('prefix');

    $table = DBUtil::getLimitedTablename('categories_category');
    $dbtable['categories_category'] = $table;
    $columns = array('id'              => 'cat_id',
                     'parent_id'       => 'cat_parent_id',
                     'is_locked'       => 'cat_is_locked',
                     'is_leaf'         => 'cat_is_leaf',
                     'name'            => 'cat_name',
                     'value'           => 'cat_value',
                     'sort_value'      => 'cat_sort_value',
                     'display_name'    => 'cat_display_name',
                     'display_desc'    => 'cat_display_desc',
                     'path'            => 'cat_path',
                     'ipath'           => 'cat_ipath',
                     'status'          => 'cat_status');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'cat_');
    $dbtable['categories_category_column'] = $columns;

    // Enable attribution services
    $dbtable['categories_category_db_extra_enable_attribution'] = true;

    $pathType = 'X';
    $dbType = DBConnectionStack::getConnectionDBType();

   // mssql can't sort on fields of type text
    if ($dbType == 'mssql') {
        $pathType = 'C(8000)';
    }

    $tabledef = array('id'              => 'I4 NOTNULL PRIMARY AUTO',
                      'parent_id'       => 'I4 NOTNULL DEFAULT 1',
                      'is_locked'       => 'I1 NOTNULL DEFAULT 0',
                      'is_leaf'         => 'I1 NOTNULL DEFAULT 0',
                      'name'            => "C(255) NOTNULL DEFAULT ''",
                      'value'           => "C(255) NOTNULL DEFAULT ''",
                      'sort_value'      => 'I4 NOTNULL DEFAULT 0',
                      'display_name'    => "X NOTNULL DEFAULT ''",
                      'display_desc'    => "X NOTNULL DEFAULT ''",
                      'path'            => "$pathType NOTNULL DEFAULT ''",
                      'ipath'           => "C(255) NOTNULL DEFAULT ''",
                      'status'          => "C(1) NOTNULL DEFAULT 'A'");
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'cat_');
    $dbtable['categories_category_column_def'] = $tabledef;

    $table = DBUtil::getLimitedTablename('categories_mapmeta');
    $dbtable['categories_mapmeta'] = $table;
    $columns = array('id'          => 'cmm_id',
                     'meta_id'     => 'cmm_meta_id',
                     'category_id' => 'cmm_category_id');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'cmm_');
    $dbtable['categories_mapmeta_column'] = $columns;

    $tabledef = array('id'          => 'I4 PRIMARY AUTO',
                      'meta_id'     => 'I4 NOTNULL DEFAULT 0',
                      'category_id' => 'I4  NOTNULL DEFAULT 0');
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'cmm_');
    $dbtable['categories_mapmeta_column_def'] = $tabledef;

    $table = DBUtil::getLimitedTablename('categories_mapobj');
    $dbtable['categories_mapobj'] = $table;
    $columns = array('id'           => 'cmo_id',
                     'modname'      => 'cmo_modname',
                     'table'        => 'cmo_table',
                     'obj_id'       => 'cmo_obj_id',
                     'obj_idcolumn' => 'cmo_obj_idcolumn',
                     'reg_id'       => 'cmo_reg_id',
                     'category_id'  => 'cmo_category_id');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'cmo_');
    $dbtable['categories_mapobj_column'] = $columns;

    $tabledef = array('id'           => 'I4 PRIMARY AUTO',
                      'modname'      => "C(60) NOTNULL DEFAULT ''",
                      'table'        => "C(60) NOTNULL DEAULT ''",
                      'obj_id'       => 'I4 NOTNULL DEFAULT 0',
                      'obj_idcolumn' => "C(60) NOTNULL DEFAULT 'id'",
                      'reg_id'       => 'I4 NOTNULL DEFAULT 0',
                      'category_id'  => 'I4 NOTNULL DEFAULT 0');
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'cmo_');
    $dbtable['categories_mapobj_column_def'] = $tabledef;

    $table = DBUtil::getLimitedTablename('categories_registry');
    $dbtable['categories_registry'] = $table;
    $columns = array('id'           => 'crg_id',
                     'modname'      => 'crg_modname',
                     'table'        => 'crg_table',
                     'property'     => 'crg_property',
                     'category_id'  => 'crg_category_id');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns, 'crg_');
    $dbtable['categories_registry_column'] = $columns;

    $tabledef = array('id' => 'I4 PRIMARY AUTO',
                      'modname' => "C(60) NOTNULL DEFAULT ''",
                      'table' => "C(60) NOTNULL DEFAULT ''",
                      'property' => "C(60) NOTNULL DEFAULT ''",
                      'category_id' => 'I4 NOTNULL DEFAULT 0');
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef, 'crg_');
    $dbtable['categories_registry_column_def'] = $tabledef;
    return $dbtable;
}
