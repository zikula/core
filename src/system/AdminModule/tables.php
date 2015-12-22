<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Internal Admin module function.
 * This function is called internally by the core whenever the module is loaded.  It adds in the information.
 *
 * @return array Return tables array.
 */
function ZikulaAdminModule_tables()
{
    $dbtable = array();

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $dbtable['admin_category'] = 'admin_category';
    $dbtable['admin_category_column'] = array('cid'         => 'cid',
                                              'catname'     => 'name',
                                              'description' => 'description',
                                              'order'       => 'sortorder');

    $dbtable['admin_category_column_def'] = array('cid'         => "I NOTNULL AUTO PRIMARY",
                                                  'catname'     => "C(32) NOTNULL DEFAULT ''",
                                                  'description' => "C(254) NOTNULL DEFAULT ''",
                                                  'order'       => "I NOTNULL DEFAULT 0");

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $admin_category = 'admin_module';
    $dbtable['admin_module'] = $admin_category;
    $dbtable['admin_module_column'] = array('id'    => 'amid',
                                            'mid'   => 'mid',
                                            'cid'   => 'cid',
                                            'order' => 'sortorder');

    $dbtable['admin_module_column_def'] = array('id'    => "I NOTNULL AUTO PRIMARY",
                                                'mid'   => "I NOTNULL DEFAULT 0",
                                                'cid'   => "I NOTNULL DEFAULT 0",
                                                'order' => "I NOTNULL DEFAULT 0");

    $dbtable['admin_module_column_idx'] = array('mid_cid' => array('mid', 'cid'));

    // Return the table information
    return $dbtable;
}
