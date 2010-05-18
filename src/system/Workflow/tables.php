<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2006, Zikula Software Foundation
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Workflow
 */

/**
 * pntables for the Workflow.
 *
 * @return array of table data
 */
function Workflow_pntables()
{
    $pntables = array();
    $pntables['workflows'] = DBUtil::getLimitedTablename('workflows');
    $pntables['workflows_column'] = array('id'           => 'id',
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

    $pntables['workflows_column_def'] = array('id'           => 'I NOTNULL AUTO PRIMARY',
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

    return $pntables;
}
