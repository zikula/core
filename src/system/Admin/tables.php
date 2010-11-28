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
 * Internal Admin module function.
 * This function is called internally by the core whenever the module is loaded.  It adds in the information.
 * @author Mark West
 *
 * @return array Return pntables array.
 */
function Admin_tables()
{
    $dbtable = array();

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $dbtable['admin_category'] = DBUtil::getLimitedTablename('admin_category');
    $dbtable['admin_category_column'] = array('cid'         => 'z_cid',
                                              'catname'     => 'z_name',
                                              'description' => 'z_description',
                                              'order'       => 'z_order');

    $dbtable['admin_category_column_def'] = array('cid'         => "I NOTNULL AUTO PRIMARY",
                                                  'catname'     => "C(32) NOTNULL DEFAULT ''",
                                                  'description' => "C(254) NOTNULL DEFAULT ''",
                                                  'order'       => "I NOTNULL DEFAULT 0");



    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $admin_category = DBUtil::getLimitedTablename('admin_module');
    $dbtable['admin_module'] = $admin_category;
    $dbtable['admin_module_column'] = array('id'    => 'z_amid',
                                            'mid'   => 'z_mid',
                                            'cid'   => 'z_cid',
                                            'order' => 'z_order');

    $dbtable['admin_module_column_def'] = array('id'    => "I NOTNULL AUTO PRIMARY",
                                                'mid'   => "I NOTNULL DEFAULT 0",
                                                'cid'   => "I NOTNULL DEFAULT 0",
                                                'order' => "I NOTNULL DEFAULT 0");

    $dbtable['admin_module_column_idx'] = array ('mid_cid' => array('mid', 'cid'));

    // Return the table information
    return $dbtable;
}
