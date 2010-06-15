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
    $pntable = array();

    // get the db driver
    $dbdriver = DBConnectionStack::getConnectionDBDriver();

    $blocks = DBUtil::getLimitedTablename('blocks') ;
    $pntable['blocks'] = $blocks;
    $pntable['blocks_column'] = array ('bid'         => 'pn_bid',
                                       'bkey'        => 'pn_bkey',
                                       'title'       => 'pn_title',
                                       'content'     => 'pn_content',
                                       'url'         => 'pn_url',
                                       'mid'         => 'pn_mid',
                                       'filter'      => 'pn_filter',
                                       'active'      => 'pn_active',
                                       'collapsable' => 'pn_collapsable',
                                       'defaultstate'=> 'pn_defaultstate',
                                       'refresh'     => 'pn_refresh',
                                       'last_update' => 'pn_last_update',
                                       'language'    => 'pn_language');

    // column definition
    $pntable['blocks_column_def'] = array ('bid'          => "I AUTO PRIMARY",
                                           'bkey'         => "C(255) NOTNULL DEFAULT ''",
                                           'title'        => "C(255) NOTNULL DEFAULT ''",
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
    $pntable['blocks_column_idx'] = array ('active_idx' => 'active');

    // additional indexes
    $userblocks = DBUtil::getLimitedTablename('userblocks') ;
    $pntable['userblocks'] = $userblocks;
    $pntable['userblocks_column'] = array ('uid'         => 'pn_uid',
                                           'bid'         => 'pn_bid',
                                           'active'      => 'pn_active',
                                           'lastupdate'  => 'pn_last_update');

    // column definition
    $pntable['userblocks_column_def'] = array ('uid'         => "I NOTNULL DEFAULT 0",
                                               'bid'         => "I NOTNULL DEFAULT 0",
                                               'active'      => "I1 NOTNULL DEFAULT 1",
                                               'lastupdate'  => "T DEFTIMESTAMP");

    // additional indexes
    $pntable['userblocks_column_idx'] = array ('bid_uid_idx'    => array('uid', 'bid'));

    $block_positions = DBUtil::getLimitedTablename('block_positions') ;
    $pntable['block_positions'] = $block_positions;
    $pntable['block_positions_column'] = array ('pid'         => 'pn_pid',
                                                'name'        => 'pn_name',
                                                'description' => 'pn_description');

    // column definitions
    $pntable['block_positions_column_def'] = array('pid'         => "I AUTO PRIMARY",
                                                   'name'        => "C(255) NOTNULL DEFAULT ''",
                                                   'description' => "C(255) NOTNULL DEFAULT ''");

    // additional indexes
    $pntable['block_positions_column_idx'] = array ('name_idx' => 'name');

    $block_placements = DBUtil::getLimitedTablename('block_placements') ;
    $pntable['block_placements'] = $block_placements;
    $pntable['block_placements_column'] = array ('pid'   => 'pn_pid',
                                                 'bid'   => 'pn_bid',
                                                 'order' => 'pn_order');

    // column definitions
    $pntable['block_placements_column_def'] = array('pid'    => "I NOTNULL DEFAULT 0",
                                                    'bid'    => "I NOTNULL DEFAULT 0",
                                                    'order'  => "I NOTNULL DEFAULT 0");

    // additional indexes
    $pntable['block_placements_column_idx'] = array ('bid_pid_idx'    => array('bid', 'pid'));

    // Return the table information
    return $pntable;
}
