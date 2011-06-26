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
 * Populate tables array for Blocks module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 *
 * @return       array       The table information.
 */
function Blocks_tables()
{
    // Initialise table array
    $dbtable = array();

    $blocks = 'blocks';
    $dbtable['blocks'] = $blocks;
    $dbtable['blocks_column'] = array ('bid'         => 'bid',
                                       'bkey'        => 'bkey',
                                       'title'       => 'title',
                                       'description' => 'description',
                                       'content'     => 'content',
                                       'url'         => 'url',
                                       'mid'         => 'mid',
                                       'filter'      => 'filter',
                                       'active'      => 'active',
                                       'collapsable' => 'collapsable',
                                       'defaultstate'=> 'defaultstate',
                                       'refresh'     => 'refresh',
                                       'last_update' => 'last_update',
                                       'language'    => 'language');

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
    $userblocks = 'userblocks';
    $dbtable['userblocks'] = $userblocks;
    $dbtable['userblocks_column'] = array ('uid'         => 'uid',
                                           'bid'         => 'bid',
                                           'active'      => 'active',
                                           'lastupdate'  => 'last_update');

    // column definition
    $dbtable['userblocks_column_def'] = array ('uid'         => "I NOTNULL DEFAULT 0",
                                               'bid'         => "I NOTNULL DEFAULT 0",
                                               'active'      => "I1 NOTNULL DEFAULT 1",
                                               'lastupdate'  => "T DEFTIMESTAMP");

    // additional indexes
    $dbtable['userblocks_column_idx'] = array ('bid_uid_idx'    => array('uid', 'bid'));

    $block_positions = 'block_positions';
    $dbtable['block_positions'] = $block_positions;
    $dbtable['block_positions_column'] = array ('pid'         => 'pid',
                                                'name'        => 'name',
                                                'description' => 'description');

    // column definitions
    $dbtable['block_positions_column_def'] = array('pid'         => "I AUTO PRIMARY",
                                                   'name'        => "C(255) NOTNULL DEFAULT ''",
                                                   'description' => "C(255) NOTNULL DEFAULT ''");

    // additional indexes
    $dbtable['block_positions_column_idx'] = array ('name_idx' => 'name');

    $block_placements = 'block_placements';
    $dbtable['block_placements'] = $block_placements;
    $dbtable['block_placements_column'] = array ('pid'   => 'pid',
                                                 'bid'   => 'bid',
                                                 'order' => 'sortorder');

    // column definitions
    $dbtable['block_placements_column_def'] = array('pid'    => "I NOTNULL DEFAULT 0",
                                                    'bid'    => "I NOTNULL DEFAULT 0",
                                                    'order'  => "I NOTNULL DEFAULT 0");

    // additional indexes
    $dbtable['block_placements_column_idx'] = array ('bid_pid_idx'    => array('bid', 'pid'));

    // Return the table information
    return $dbtable;
}
