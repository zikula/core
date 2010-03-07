<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

/**
 * Get all blocks
 *
 * This function gets all block entries from the database
 *
 * @param 'modid'  module id to filter block selection for
 * @param 'inactive' force inclusion of inactive blocks
 * @author Mark West
 * @return   array   array of items, or false on failure
 */
function Blocks_userapi_getall($args)
{
    // create an empty items array
    $items = array();

    // Security check
    if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_OVERVIEW)) {
        return $items;
    }

    if (!isset($args['inactive']) || !is_bool($args['inactive'])) {
        $args['inactive'] = false;
    }

    $pntable      = pnDBGetTables();
    $blockstable  = $pntable['blocks'];
    $blockscolumn = $pntable['blocks_column'];

    // initialise the where arguments array
    $whereargs = array();

    // Work out if we're showing all blocks or just active ones
    if (!SessionUtil::getVar('blocks_show_all') && !$args['inactive']) {
        $whereargs[] = "$blockscolumn[active] = 1";
    }

    // check for a filter by module id
    if (isset($args['modid']) && is_numeric($args['modid'])) {
        $whereargs[] = "$blockscolumn[mid] = '".DataUtil::formatForStore($args['modid'])."'";
    }

    // construct the where clause
    $where = '';
    if (!empty($whereargs)) {
        $where = 'WHERE ' . implode(' AND ', $whereargs);
    }

    $permFilter   = array();
    $permFilter[] = array ('realm'            =>  '0',
                           'component_left'   =>  'Blocks',
                           'component_middle' =>  '',
                           'component_right'  =>  '',
                           'instance_left'    =>  'bkey',
                           'instance_middle'  =>  'title',
                           'instance_right'   =>  'bid',
                           'level'            =>  ACCESS_OVERVIEW);

    return DBUtil::selectObjectArray ('blocks', $where, 'title', -1, -1, '', $permFilter);
}

/**
 * get a specific block
 *
 * @param    $args['bid']  id of block to get
 * @return   array         item array, or false on failure
 */
function Blocks_userapi_get($args)
{
    // Argument check
    if (!isset($args['bid']) || !is_numeric($args['bid'])) {
        return LogUtil::registerArgsError();
    }

    // Return the item array
    return pnBlockGetInfo($args['bid']);
}

/**
 * utility function to count the number of items held by this module
 *
 * @return   integer   number of items held by this module
 */
function Blocks_userapi_countitems()
{
    $permFilter   = array();
    $permFilter[] = array ('realm'            =>  '0',
                           'component_left'   =>  'Blocks',
                           'component_middle' =>  '',
                           'component_right'  =>  '',
                           'instance_left'    =>  'bkey',
                           'instance_middle'  =>  'title',
                           'instance_right'   =>  'bid',
                           'level'            =>  ACCESS_OVERVIEW);

    $blocks = DBUtil::selectObjectArray ('blocks', '', '', -1, -1, '', $permFilter);
    return count($blocks);
}

/**
 * Get all block positions
 *
 * This function gets all block position entries from the database
 * @author Mark West
 * @return   array   array of items, or false on failure
 */
function Blocks_userapi_getallpositions($args)
{
    // create an empty items array
    $block_positions = array();

    // Security check
    if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_OVERVIEW)) {
        return $block_positions;
    }

    return DBUtil::selectObjectArray('block_positions', null, 'name', -1, -1, '', null);
}

/**
 * get a specific block position
 * @author Mark West
 * @param int $args['pid'] position id
 * @return mixed item array, or false on failure
 */
function Blocks_userapi_getposition($args)
{
    // Argument check
    if (!isset($args['pid']) || !is_numeric($args['pid'])) {
        return LogUtil::registerArgsError();
    }

    return DBUtil::selectObjectByID('block_positions', $args['pid'], 'pid');
}

/**
 * get all block id's a block position
 * @author Mark West
 * @param int $args['pid'] position id
 * @return mixed item array, or false on failure
 */
function Blocks_userapi_getblocksinposition($args)
{
    // Argument check
    if (!isset($args['pid']) || !is_numeric($args['pid'])) {
        return LogUtil::registerArgsError();
    }
    $where = "WHERE pn_pid = '" . DataUtil::formatForStore($args['pid']) . '\'';
    return DBUtil::selectObjectArray('block_placements', $where, 'pn_order');
}

/**
 * get all block id's a block position
 * @author Mark West
 * @param int $args['bid'] block id
 * @return mixed item array, or false on failure
 */
function Blocks_userapi_getallblockspositions($args)
{
    // Argument check
    if (!isset($args['bid']) || !is_numeric($args['bid'])) {
        return LogUtil::registerArgsError();
    }
    $where = "WHERE pn_bid = '" . DataUtil::formatForStore($args['bid']) . '\'';
    return DBUtil::selectObjectArray('block_placements', $where, 'pn_order');
}
