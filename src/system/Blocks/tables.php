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
 * Populate pntables array for Blocks module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @author       Mark West
 * @return       array       The table information.
 */
function Blocks_tables()
{
    // Initialise table array
    $dbtable = array();

    // get the db driver
    $dbdriver = DBConnectionStack::getConnectionDBDriver();

    $blocks = DBUtil::getLimitedTablename('blocks') ;
    $dbtable['blocks'] = $blocks;
    $dbtable['blocks_column'] = array ('bid'         => 'z_bid',
                                       'bkey'        => 'z_bkey',
                                       'title'       => 'z_title',
                                       'description' => 'z_description',
                                       'content'     => 'z_content',
                                       'url'         => 'z_url',
                                       'mid'         => 'z_mid',
                                       'filter'      => 'z_filter',
                                       'active'      => 'z_active',
                                       'collapsable' => 'z_collapsable',
                                       'defaultstate'=> 'z_defaultstate',
                                       'refresh'     => 'z_refresh',
                                       'last_update' => 'z_last_update',
                                       'language'    => 'z_language');

    // column definition
    $dbtable['blocks_column_def'] = array ('bid'          => "I AUTO PRIMARY",
                                           'bkey'         => "C(255) NOTNULL DEFAULT ''",
                                           'title'        => "C(255) NOTNULL DEFAULT ''",
                                           'description'  => "X NOTNULL",
                                           'content'      => "XL NOTNULL",
                                           'url'          => "XL NOTNULL",
                                           'mid'          => "I NOTNULL DEFAULT 0",
                                           'filter'       => "XL NOTNULL",
                                           'active'       => "I1 NOTNULL DEFAULT 1",
                                           'refresh'      => "I NOTNULL DEFAULT 0",
                                           'last_update'  => "T DEFTIMESTAMP NOTNULL",
                                           'language'     => "C(30) NOTNULL DEFAULT ''",
                                           'collapsable'  => "I NOTNULL DEFAULT 1",
                                           'defaultstate' => "I NOTNULL DEFAULT 1");

    // additional indexes
    $dbtable['blocks_column_idx'] = array ('active_idx' => 'active');

    // additional indexes
    $userblocks = DBUtil::getLimitedTablename('userblocks') ;
    $dbtable['userblocks'] = $userblocks;
    $dbtable['userblocks_column'] = array ('uid'         => 'z_uid',
                                           'bid'         => 'z_bid',
                                           'active'      => 'z_active',
                                           'lastupdate'  => 'z_last_update');

    // column definition
    $dbtable['userblocks_column_def'] = array ('uid'         => "I NOTNULL DEFAULT 0",
                                               'bid'         => "I NOTNULL DEFAULT 0",
                                               'active'      => "I1 NOTNULL DEFAULT 1",
                                               'lastupdate'  => "T DEFTIMESTAMP");

    // additional indexes
    $dbtable['userblocks_column_idx'] = array ('bid_uid_idx'    => array('uid', 'bid'));

    $block_positions = DBUtil::getLimitedTablename('block_positions') ;
    $dbtable['block_positions'] = $block_positions;
    $dbtable['block_positions_column'] = array ('pid'         => 'z_pid',
                                                'name'        => 'z_name',
                                                'description' => 'z_description');

    // column definitions
    $dbtable['block_positions_column_def'] = array('pid'         => "I AUTO PRIMARY",
                                                   'name'        => "C(255) NOTNULL DEFAULT ''",
                                                   'description' => "C(255) NOTNULL DEFAULT ''");

    // additional indexes
    $dbtable['block_positions_column_idx'] = array ('name_idx' => 'name');

    $block_placements = DBUtil::getLimitedTablename('block_placements') ;
    $dbtable['block_placements'] = $block_placements;
    $dbtable['block_placements_column'] = array ('pid'   => 'z_pid',
                                                 'bid'   => 'z_bid',
                                                 'order' => 'z_order');

    // column definitions
    $dbtable['block_placements_column_def'] = array('pid'    => "I NOTNULL DEFAULT 0",
                                                    'bid'    => "I NOTNULL DEFAULT 0",
                                                    'order'  => "I NOTNULL DEFAULT 0");

    // additional indexes
    $dbtable['block_placements_column_idx'] = array ('bid_pid_idx'    => array('bid', 'pid'));

    // Return the table information
    return $dbtable;
}
