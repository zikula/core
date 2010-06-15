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
 * Internal Admin module function.
 * This function is called internally by the core whenever the module is loaded.  It adds in the information.
 * @author Mark West
 *
 * @return array Return pntables array.
 */
function Admin_tables()
{
    $pntable = array();

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $pntable['admin_category'] = DBUtil::getLimitedTablename('admin_category');
    $pntable['admin_category_column'] = array('cid'         => 'pn_cid',
                                              'catname'     => 'pn_name',
                                              'description' => 'pn_description');

    $pntable['admin_category_column_def'] = array('cid'         => "I NOTNULL AUTO PRIMARY",
                                                  'catname'     => "C(32) NOTNULL DEFAULT ''",
                                                  'description' => "C(254) NOTNULL DEFAULT ''");



    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $admin_category = DBUtil::getLimitedTablename('admin_module');
    $pntable['admin_module'] = $admin_category;
    $pntable['admin_module_column'] = array('id'  => 'pn_amid',
                                            'mid' => 'pn_mid',
                                            'cid' => 'pn_cid');

    $pntable['admin_module_column_def'] = array('id'  => "I NOTNULL AUTO PRIMARY",
                                                'mid' => "I NOTNULL DEFAULT 0",
                                                'cid' => "I NOTNULL DEFAULT 0");

    $pntable['admin_module_column_idx'] = array ('mid_cid' => array('mid', 'cid'));

    // Return the table information
    return $pntable;
}
