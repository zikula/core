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

    $table = 'categories_category';
    $dbtable['categories_category'] = $table;
    $columns = array('id'              => 'id',
                     'parent_id'       => 'parent_id',
                     'is_locked'       => 'is_locked',
                     'is_leaf'         => 'is_leaf',
                     'name'            => 'name',
                     'value'           => 'value',
                     'sort_value'      => 'sort_value',
                     'display_name'    => 'display_name',
                     'display_desc'    => 'display_desc',
                     'path'            => 'path',
                     'ipath'           => 'ipath',
                     'status'          => 'status');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns);
    $dbtable['categories_category_column'] = $columns;

    // Enable attribution services
    $dbtable['categories_category_db_extra_enable_attribution'] = true;

    $pathType = 'X';
    $dbType = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());

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
                      'sort_value'      => 'I4 NOTNULL DEFAULT 2147483647',
                      'display_name'    => "X NOTNULL DEFAULT ''",
                      'display_desc'    => "X NOTNULL DEFAULT ''",
                      'path'            => "$pathType NOTNULL DEFAULT ''",
                      'ipath'           => "C(255) NOTNULL DEFAULT ''",
                      'status'          => "C(1) NOTNULL DEFAULT 'A'");
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef);
    $dbtable['categories_category_column_def'] = $tabledef;

    $table = 'categories_mapmeta';
    $dbtable['categories_mapmeta'] = $table;
    $columns = array('id'          => 'id',
                     'meta_id'     => 'meta_id',
                     'category_id' => 'category_id');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns);
    $dbtable['categories_mapmeta_column'] = $columns;

    $tabledef = array('id'          => 'I4 PRIMARY AUTO',
                      'meta_id'     => 'I4 NOTNULL DEFAULT 0',
                      'category_id' => 'I4  NOTNULL DEFAULT 0');
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef);
    $dbtable['categories_mapmeta_column_def'] = $tabledef;

    $table = 'categories_mapobj';
    $dbtable['categories_mapobj'] = $table;
    $columns = array('id'           => 'id',
                     'modname'      => 'modname',
                     'table'        => 'tablename',
                     'obj_id'       => 'obj_id',
                     'obj_idcolumn' => 'obj_idcolumn',
                     'reg_id'       => 'reg_id',
                     'reg_property' => 'reg_property',
                     'category_id'  => 'category_id');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns);
    $dbtable['categories_mapobj_column'] = $columns;

    $tabledef = array('id'           => 'I4 PRIMARY AUTO',
                      'modname'      => "C(60) NOTNULL DEFAULT ''",
                      'table'        => "C(60) NOTNULL DEAULT ''",
                      'obj_id'       => 'I4 NOTNULL DEFAULT 0',
                      'obj_idcolumn' => "C(60) NOTNULL DEFAULT 'id'",
                      'reg_id'       => 'I4 NOTNULL DEFAULT 0',
                      'reg_property' => "C(60) NOTNULL DEFAULT ''",
                      'category_id'  => 'I4 NOTNULL DEFAULT 0');
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef);
    $dbtable['categories_mapobj_column_def'] = $tabledef;

    $table = 'categories_registry';
    $dbtable['categories_registry'] = $table;
    $columns = array('id'           => 'id',
                     'modname'      => 'modname',
                     'table'        => 'tablename',
                     'property'     => 'property',
                     'category_id'  => 'category_id');
    ObjectUtil::addStandardFieldsToTableDefinition ($columns);
    $dbtable['categories_registry_column'] = $columns;

    $tabledef = array('id' => 'I4 PRIMARY AUTO',
                      'modname' => "C(60) NOTNULL DEFAULT ''",
                      'table' => "C(60) NOTNULL DEFAULT ''",
                      'property' => "C(60) NOTNULL DEFAULT ''",
                      'category_id' => 'I4 NOTNULL DEFAULT 0');
    ObjectUtil::addStandardFieldsToTableDataDefinition ($tabledef);
    $dbtable['categories_registry_column_def'] = $tabledef;
    return $dbtable;
}
