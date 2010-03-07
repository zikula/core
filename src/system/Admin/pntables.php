<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Admin
 */

/**
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in the information
 * @author Mark West
 * @return array pntables array
 */
function Admin_pntables()
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
