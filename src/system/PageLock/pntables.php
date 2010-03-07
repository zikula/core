<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2007, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Jorn Wildt
 * @package Zikula_System_Modules
 * @subpackage PageLock
 */


/**
 * define table information for the module
 *
 */
function & PageLock_pntables()
{
  $pntable = array();
  $prefix = pnConfigGetVar('prefix');

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

