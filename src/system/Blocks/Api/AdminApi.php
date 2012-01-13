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
 * Blocks_Api_Admin class.
 */
class Blocks_Api_AdminApi extends Zikula_AbstractApi
{
    /**
     * Update attributes of a block.
     *
     * @param int $args ['bid'] the ID of the block to update.
     * @param string $args ['title'] the new title of the block.
     * @param string $args ['description'] the new description of the block.
     * @param string $args ['positions'] the new positions of the block.
     * @param string $args ['url'] the new URL of the block.
     * @param string $args ['language'] the new language of the block.
     * @param string $args ['content'] the new content of the block.
     *
     * @return bool true on success, false on failure.
     */
    public function update($args)
    {
        // Optional arguments
        if (!isset($args['url'])) {
            $args['url'] = '';
        }
        if (!isset($args['content'])) {
            $args['content'] = '';
        }

        // Argument check
        if (!isset($args['bid']) ||
                !is_numeric($args['bid']) ||
                !isset($args['content']) ||
                !isset($args['title']) ||
                !isset($args['description']) ||
                !isset($args['language']) ||
                !isset($args['collapsable']) ||
                !isset($args['defaultstate'])) {
            return LogUtil::registerArgsError();
        }

        $block = DBUtil::selectObjectByID('blocks', $args['bid'], 'bid');

        // Security check
        // this function is called durung the init process so we have to check in _ZINSTALLVER
        // is set as alternative to the correct permission check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('Blocks::', "$block[bkey]:$block[title]:$block[bid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $item = array(
                'bid' => isset($args['bid']) ? $args['bid'] : $block['bid'],
                'content' => isset($args['content']) ? $args['content'] : $block['content'],
                'title' => isset($args['title']) ? $args['title'] : $block['title'],
                'description' => isset($args['description']) ? $args['description'] : $block['description'],
                'filter' => isset($args['filter']) ? serialize($args['filter']) : $block['filter'],
                'url' => isset($args['url']) ? $args['url'] : $block['url'],
                'refresh' => isset($args['refresh']) ? $args['refresh'] : $block['refresh'],
                'language' => isset($args['language']) ? $args['language'] : $block['language'],
                'collapsable' => isset($args['collapsable']) ? $args['collapsable'] : $block['collapsable'],
                'defaultstate' => isset($args['defaultstate']) ? $args['defaultstate'] : $block['defaultstate']
        );

        $res = DBUtil::updateObject($item, 'blocks', '', 'bid');
        if (!$res) {
            return LogUtil::registerError($this->__('Error! Could not save your changes.'));
        }

        // leave unchanged positions as is, delete removed positions from placements table
        // and add placement for new positions
        if (isset($args['positions'])) {
            // Get all existing block positions. We do not use the userapi function here because we need
            // an associative array for the next steps: key = pid (position id)
            $allblockspositions = DBUtil::selectObjectArray('block_positions', null, 'pid', -1, -1, 'pid', null);
            foreach ($allblockspositions as $positionid => $blockposition) {
                if (in_array($positionid, $args['positions'])) {
                    // position name is present in the array submitted from the user
                    $where = "WHERE pid = '" . DataUtil::formatForStore($positionid) . '\'';
                    $blocksinposition = DBUtil::selectObjectArray('block_placements', $where, 'sortorder', -1, -1, 'bid');
                    if (array_key_exists($item['bid'], $blocksinposition)) {
                        // block is already in this position, placement did not change, this means we do nothing
                    } else {
                        // add the block to the given position as last entry (max(sortorder) +1
                        $newplacement = array('pid' => $blockposition['pid'],
                                'bid' => $item['bid'],
                                'order' => count($blocksinpositions));
                        $res = DBUtil::insertObject($newplacement, 'block_placements', 'bid', true);
                        if (!$res) {
                            return LogUtil::registerError($this->__('Error! Could not perform the insertion.'));
                        }
                    }
                } else {
                    // position name is NOT present in the array submitted from the user
                    // delete the block id from the placements table for this position
                    $where = '(bid = \'' . DataUtil::formatForStore($item['bid']) . '\' AND pid = \'' . DataUtil::formatForStore($blockposition['pid']) . '\')';
                    $res = DBUtil::deleteWhere('block_placements', $where);
                    if (!$res) {
                        return LogUtil::registerError($this->__('Error! Could not save your changes.'));
                    }
                }
            }
        }

        return true;
    }

    /**
     * Create a new block.
     *
     * @param string $block ['title'] the title of the block.
     * @param string $block ['description'] the description of the block.
     * @param int $block ['mid'] the module ID of the block.
     * @param string $block ['language'] the language of the block.
     * @param int $block ['bkey'] the key of the block.
     *
     * @return mixed block Id on success, false on failure.
     */
    public function create($args)
    {
        // Argument check
        if ((!isset($args['title'])) ||
                (!isset($args['description'])) ||
                (!isset($args['mid'])) ||
                (!isset($args['language'])) ||
                (!isset($args['collapsable'])) ||
                (!isset($args['defaultstate'])) ||
                (!isset($args['bkey']))) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('Blocks::', "$args[bkey]:$args[title]:", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // optional arguments
        if (!isset($args['content']) || !is_string($args['content'])) {
            $args['content'] = '';
        }

        $block = array(
                'title' => $args['title'],
                'description' => $args['description'],
                'language' => $args['language'],
                'collapsable' => $args['collapsable'],
                'mid' => $args['mid'],
                'defaultstate' => $args['defaultstate'],
                'bkey' => $args['bkey'],
                'content' => $args['content']
        );

        $block['url'] = '';
        $block['filter'] = '';
        $block['active'] = 1;
        $block['refresh'] = 3600;
        $block['last_update'] = DateUtil::getDatetime();
        $block['active'] = 1;

        $res = DBUtil::insertObject($block, 'blocks', 'bid');

        if (!$res) {
            return LogUtil::registerError($this->__('Error! Could not create the new item.'));
        }

        // empty block positions for this block
        if (isset($args['positions'])) {
            // add new block positions
            $blockplacments = array();
            foreach ($args['positions'] as $position) {
                $blockplacments[] = array('bid' => $block['bid'], 'pid' => $position);
            }
            $res = DBUtil::insertObjectArray($blockplacments, 'block_placements');
            if (!$res) {
                return LogUtil::registerError($this->__('Error! Could not create the new item.'));
            }
        }

        return $block['bid'];
    }

    /**
     * Set a block's active state.
     *
     * @param int $args ['bid'] the ID of the block to deactivate.
     *
     * @return bool true on success, false on failure.
     */
    public function setActiveState($block)
    {
        if (!isset($block['bid']) || !is_numeric($block['bid'])) {
            return LogUtil::registerArgsError();
        }
        if (!isset($block['active']) || !is_numeric($block['active'])) {
            return LogUtil::registerArgsError();
        }
        $blockinfo = BlockUtil::getBlockInfo($block['bid']);
        if (!SecurityUtil::checkPermission('Blocks::', "$blockinfo[bkey]:$blockinfo[title]:$block[bid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // create a new object to ensure that we only update the 'active' field
        $obj = array();
        $obj['bid'] = $block['bid'];
        $obj['active'] = $block['active'];
        $res = DBUtil::updateObject($obj, 'blocks', '', 'bid');

        return $res;
    }

    /**
     * Deactivate a block.
     *
     * @param int $args ['bid'] the ID of the block to deactivate.
     *
     * @return bool true on success, false on failure.
     */
    public function deactivate($args)
    {
        $args['active'] = 0;
        $res = (boolean)$this->setActiveState($args);

        if (!$res) {
            return LogUtil::registerError($this->__('Error! Could not deactivate the block.'));
        }

        return $res;
    }

    /**
     * Activate a block.
     *
     * @param int $args ['bid'] the ID of the block to activate.
     *
     * @return bool true on success, false on failure.
     */
    public function activate($args)
    {
        $args['active'] = 1;
        $res = (boolean)$this->setActiveState($args);

        if (!$res) {
            return LogUtil::registerError($this->__('Error! Could not activate the block.'));
        }

        return $res;
    }

    /**
     * Delete a block.
     *
     * @param int $args ['bid'] the ID of the block to delete.
     *
     * @return bool true on success, false on failure.
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['bid']) || !is_numeric($args['bid'])) {
            return LogUtil::registerArgsError();
        }

        $block = DBUtil::selectObjectByID('blocks', $args['bid'], 'bid');

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', "$block[bkey]:$block[title]:$block[bid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // delete block placements for this block
        $res = DBUtil::deleteObjectByID('block_placements', $args['bid'], 'bid');
        if (!$res) {
            return LogUtil::registerError($this->__('Error! Could not perform the deletion.'));
        }

        // delete the block itself
        $res = DBUtil::deleteObjectByID('blocks', $args['bid'], 'bid');
        if (!$res) {
            return LogUtil::registerError($this->__('Error! Could not perform the deletion.'));
        }

        return true;
    }

    /**
     * Create a block position.
     *
     * @param string $args['name'] name of the position.
     * @param string $args['description'] description of the position.
     *
     * @return mixed position ID on success, false on failure.
     */
    public function createposition($args)
    {
        // Argument check
        if (!isset($args['name']) ||
                !strlen($args['name']) ||
                !isset($args['description'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('Blocks::position', "$args[name]::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $positions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        if (isset($positions) && is_array($positions)) {
            foreach ($positions as $position) {
                if ($position['name'] == $args['name']) {
                    return LogUtil::registerError($this->__('Error! There is already a block position with the name you entered.'));
                }
            }
        }

        $item = array('name' => $args['name'], 'description' => $args['description']);

        if (!DBUtil::insertObject($item, 'block_positions', 'pid')) {
            return LogUtil::registerError($this->__('Error! Could not create the new item.'));
        }

        // Return the id of the newly created item to the calling process
        return $item['pid'];
    }

    /**
     * Update a block position item.
     *
     * @param int $args['pid'] the ID of the item.
     * @param sting $args['name'] name of the block position.
     * @param string $args['description'] description of the block position.
     *
     * @return bool true if successful, false otherwise.
     */
    public function updateposition($args)
    {
        // Argument check
        if (!isset($args['pid']) ||
                !isset($args['name']) ||
                !isset($args['description'])) {
            return LogUtil::registerArgsError();
        }

        // Get the existing admin message
        $item = ModUtil::apiFunc('Blocks', 'user', 'getposition', array('pid' => $args['pid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::position', "$item[name]::$item[pid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // create the item array
        $item = array('pid' => $args['pid'], 'name' => $args['name'], 'description' => $args['description']);

        if (!DBUtil::updateObject($args, 'block_positions', '', 'pid')) {
            return LogUtil::registerError($this->__('Error! Could not save your changes.'));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Delete a block position.
     *
     * @param int $args['pid'] ID of the position.
     *
     * @return bool true on success, false on failure.
     */
    public function deleteposition($args)
    {
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            return LogUtil::registerArgsError();
        }

        $item = ModUtil::apiFunc('Blocks', 'user', 'getposition', array('pid' => $args['pid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        if (!SecurityUtil::checkPermission('Blocks::position', "$item[name]::$item[pid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Now actually delete the category
        if (!DBUtil::deleteObjectByID('block_positions', $args['pid'], 'pid')) {
            return LogUtil::registerError($this->__('Error! Could not perform the deletion.'));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Get available admin panel links.
     *
     * @return array array of admin links.
     */
    public function getlinks()
    {
        $links = array();
        $submenulinks = array();

        // get all possible block positions
        $blockspositions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');

        // Create array for dropdown menu links
        foreach ($blockspositions as $blocksposition) {
            $filter['blockposition_id'] = $blocksposition['pid'];
            $submenulinks[] = array('url' => ModUtil::url('Blocks', 'admin', 'view', array('filter' => $filter)),
                    'text' => $this->__f('Position \"%s\" ', $blocksposition['name']));
        }

        if (SecurityUtil::checkPermission('Blocks::', '::', ACCESS_EDIT)) {
            $links[] = array('url' => ModUtil::url('Blocks', 'admin', 'view'),
                    'text' => $this->__('Blocks list'),
                    'class' => 'z-icon-es-view',
                    'links' => $submenulinks);
        }

        if (SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Blocks', 'admin', 'newblock'), 'text' => $this->__('Create new block'), 'class' => 'z-icon-es-new');
        }
        if (SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Blocks', 'admin', 'newposition'), 'text' => $this->__('Create new block position'), 'class' => 'z-icon-es-new');
        }
        if (SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Blocks', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        return $links;
    }

}
