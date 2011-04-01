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
 * define table information for the module
 *
 */
function pagelock_tables()
{
  $dbtable = array();
  $prefix = System::getVar('prefix');

    // Lock table setup

  $tableName = DBUtil::getLimitedTablename('pagelock');
  $dbtable['pagelock'] = $tableName;

  $dbtable['pagelock_column'] =
    array('id'                => 'z_id',
          'name'              => 'z_name',
          'createdDate'       => 'z_cdate',
          'expiresDate'       => 'z_edate',
          'lockedBySessionId' => 'z_session',
          'lockedByTitle'     => 'z_title',
          'lockedByIPNo'      => 'z_ipno');


  $def =
    array('id'                => "I NOTNULL AUTO PRIMARY",
          'name'              => "C(100) NOTNULL DEFAULT ''",
          'createdDate'       => "T NOTNULL",
          'expiresDate'       => "T NOTNULL",
          'lockedBySessionId' => "C(50) NOTNULL",
          'lockedByTitle'     => "C(100) NOTNULL",
          'lockedByIPNo'      => "C(30) NOTNULL");

  $dbtable['pagelock_column_def'] = $def;

  return $dbtable;
}

