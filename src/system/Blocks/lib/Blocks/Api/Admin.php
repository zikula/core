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
class Blocks_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Update attributes of a block.
     *
     * @param int    $args ['bid'] the ID of the block to update.
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
        // Argument check
        if (!isset($args['bid']) || !is_numeric($args['bid']) ||
            !isset($args['content']) ||
            !isset($args['title']) ||
            !isset($args['description']) ||
            !isset($args['language']) ||
            !isset($args['collapsable']) ||
            !isset($args['defaultstate'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        // this function is called during the init process so we have to check in _ZINSTALLVER
        // is set as alternative to the correct permission check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('Blocks::', "$args[bkey]:$args[title]:$args[bid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // remove old placements and insert the new ones
        $items = $this->entityManager->getRepository($this->name . '_Entity_BlockPlacement')
                                     ->findBy(array('bid'=>$args['bid']));

        // refactor position array (keys=values)
        $positions = $args['positions'];
        $args['positions'] = array();
        foreach ($positions as $value) {
            $args['positions'][$value] = $value;
        }

        foreach ($items as $item) {
            $pid = $item->getPid();
            if (!in_array($pid,$args['positions'])) {
                $this->entityManager->remove($item);
            } else {
                unset($args['positions'][$pid]);
            }
        }

        if (isset($args['positions']) && is_array($args['positions'])) {

            foreach ($args['positions'] as $position) {
                $placement = new Blocks_Entity_BlockPlacement();
                $placement->setPid($position);
                $placement->setBid($args['bid']);
                $this->entityManager->persist($placement);
            }
        }

        // unset positions
        if (isset($args['positions'])) {
            unset($args['positions']);
        }

        // update item
        $item = ModUtil::apiFunc('Blocks', 'user', 'get', array('bid' => $args['bid']));
        $item->merge($args);

        $this->entityManager->flush();

        return true;
    }

    /**
     * Create a new block.
     *
     * @param string $block ['title'] the title of the block.
     * @param string $block ['description'] the description of the block.
     * @param int    $block ['mid'] the module ID of the block.
     * @param string $block ['language'] the language of the block.
     * @param int    $block ['bkey'] the key of the block.
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

        $item = new Blocks_Entity_Block();
        $item->merge($block);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        // insert block positions for this block
        if (isset($args['positions']) && is_array($args['positions'])) {

            foreach ($args['positions'] as $position) {
                $placement = new Blocks_Entity_BlockPlacement();
                $placement->setPid($position);
                $placement->setBid($item['bid']);
                $this->entityManager->persist($placement);
            }

            $this->entityManager->flush();
        }

        return $item['bid'];
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

        $item = ModUtil::apiFunc('Blocks', 'user', 'get', array('bid' => $block['bid']));
        if (!SecurityUtil::checkPermission('Blocks::', "$item[bkey]:$item[title]:$item[bid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // set block's new state
        $item['active'] = $block['active'];
        $this->entityManager->flush();

        return true;
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

        $block = ModUtil::apiFunc('Blocks', 'user', 'get', array('bid' => $args['bid']));

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', "$block[bkey]:$block[title]:$block[bid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // delete block's placements and block itself
        $entity = $this->name . '_Entity_BlockPlacement';
        $dql = "DELETE FROM $entity p WHERE p.bid = {$block['bid']}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();

        $entity = $this->name . '_Entity_Block';
        $dql = "DELETE FROM $entity b WHERE b.bid = {$block['bid']}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();

        return true;
    }

    /**
     * Create a block position.
     *
     * @param string $args['name']        name of the position.
     * @param string $args['description'] description of the position.
     *
     * @return mixed position ID on success, false on failure.
     */
    public function createposition($args)
    {
        // Argument check
        if (!isset($args['name']) || !strlen($args['name']) ||
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

        $item = new Blocks_Entity_BlockPosition();
        $item->merge($args);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        // Return the id of the newly created item to the calling process
        return $item['pid'];
    }

    /**
     * Update a block position item.
     *
     * @param int    $args['pid']         the ID of the item.
     * @param sting  $args['name']        name of the block position.
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

        // Get the existing position
        $item = ModUtil::apiFunc('Blocks', 'user', 'getposition', array('pid' => $args['pid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::position', "$item[name]::$item[pid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $positions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        if (isset($positions) && is_array($positions)) {
            foreach ($positions as $position) {
                if ($position['name'] == $args['name'] && $position['pid'] != $args['pid']) {
                    return LogUtil::registerError($this->__('Error! There is already a block position with the name you entered.'));
                }
            }
        }

        // update item
        $item->merge($args);
        $this->entityManager->flush();

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

        $position = ModUtil::apiFunc('Blocks', 'user', 'getposition', array('pid' => $args['pid']));

        if (!SecurityUtil::checkPermission('Blocks::position', "$position[name]::$position[pid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // delete placements of the position to be deleted
        $entity = $this->name . '_Entity_BlockPlacement';
        $dql = "DELETE FROM $entity p WHERE p.pid = {$position['pid']}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();

        // delete position
        $entity = $this->name . '_Entity_BlockPosition';
        $dql = "DELETE FROM $entity p WHERE p.pid = {$position['pid']}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();

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
