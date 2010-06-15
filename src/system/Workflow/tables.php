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
 * pntables for the Workflow.
 *
 * @return array of table data
 */
function Workflow_tables()
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
