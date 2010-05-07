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
 * @param 'active_status'   filter by active status (0=all, 1=active, 2=inactive)
 * @param 'blockposition'   block position id to filter block selection for
 * @param 'inactive'        force inclusion of inactive blocks (true overrides active_status to 0, false goes with active_status)
 * @param 'language'        language to filter block selection for
 * @param 'module_id'       module id to filter block selection for
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

    $pntable      = pnDBGetTables();
    $blockstable  = $pntable['blocks'];
    $blockscolumn = $pntable['blocks_column'];
    $sort         = isset($args['sort']) && $args['sort'] ? $args['sort'] : '';
    $sortdir      = isset($args['sortdir']) && $args['sortdir'] ? $args['sortdir'] : 'ASC';
    if ($sort) {
        $sort     .= " $sortdir";
    } else {
        $sort     = 'title';
    }

    // backwards parameter compatability
    if (isset($args['modid']) && is_numeric($args['modid'])) {
        $args['module_id'] = $args['modid']; 
    }

    // initialise the where arguments array
    $whereargs = array();

    // filter by block position
    if (isset($args['blockposition_id']) && is_numeric($args['blockposition_id']) && $args['blockposition_id']) {
        $where       = "pn_pid = $args[blockposition_id]";
        $bids        = DBUtil::selectFieldArray ('block_placements', 'bid', $where);
        $bidList     = $bids ? implode (',', $bids) : -1;
        $whereargs[] = "$blockscolumn[bid] IN ($bidList)";
    }
    
    // filter by active block status
    if (isset($args['inactive']) && $args['inactive']) {
            $args['active_status'] = 0;
    }
    if (isset($args['active_status']) && is_numeric($args['active_status']) && $args['active_status']) { // new logic
        $whereargs[] = "$blockscolumn[active] = " . ($args['active_status'] == 1 ? '1' : '0');
    } 

    // filter by module 
    if (isset($args['module_id']) && is_numeric($args['module_id']) && $args['module_id']) {
        $whereargs[] = "$blockscolumn[mid] = '".DataUtil::formatForStore($args['module_id'])."'";
    }

    // filter by language
    if (isset($args['language']) && $args['language']) {
        $whereargs[] = "$blockscolumn[language] = '".DataUtil::formatForStore($args['language'])."'";
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

    $joinInfo = array();
    $joinInfo[] = array ('join_table'          =>  'modules',
                         'join_field'          =>  'name',
                         'object_field_name'   =>  'module_name',
                         'compare_field_table' =>  'mid',
                         'compare_field_join'  =>  'id');

    return DBUtil::selectExpandedObjectArray ('blocks', $joinInfo, $where, $sort, -1, -1, '', $permFilter);
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
