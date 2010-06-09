<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */



/**
 * define table information for the module
 *
 */
function & PageLock_pntables()
{
  $pntable = array();
  $prefix = System::getVar('prefix');

    // Lock table setup

  $tableName = DBUtil::getLimitedTablename('pagelock');
  $pntable['PageLock'] = $tableName;

  $pntable['PageLock_column'] =
    array('id'                => 'plock_id',
          'name'              => 'plock_name',
          'createdDate'       => 'plock_cdate',
          'expiresDate'       => 'plock_edate',
          'lockedBySessionId' => 'plock_session',
          'lockedByTitle'     => 'plock_title',
          'lockedByIPNo'      => 'plock_ipno');


  $def =
    array('id'                => "I NOTNULL AUTO PRIMARY",
          'name'              => "C(100) NOTNULL DEFAULT ''",
          'createdDate'       => "T NOTNULL",
          'expiresDate'       => "T NOTNULL",
          'lockedBySessionId' => "C(50) NOTNULL",
          'lockedByTitle'     => "C(100) NOTNULL",
          'lockedByIPNo'      => "C(30) NOTNULL");

  $pntable['PageLock_column_def'] = $def;

  return $pntable;
}

