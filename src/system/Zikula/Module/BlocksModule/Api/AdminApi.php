<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 * @package Zikula
 * @subpackage ZikulaBlocksModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\BlocksModule\Api;

use System;
use SecurityUtil;
use Zikula\Module\BlocksModule\Entity\BlockPlacementEntity;
use ModUtil;
use Zikula\Module\BlocksModule\Entity\BlockEntity;
use Zikula\Module\BlocksModule\Entity\BlockPositionEntity;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API functions used by administrative controllers
 *
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Update attributes of a block
     *
     * @param mixed[] $args {<ul>
     *      <li>@type int    $bid         the ID of the block to update</li>
     *      <li>@type string $title       the new title of the block</li>
     *      <li>@type string $description the new description of the block</li>
     *      <li>@type string $positions   the new positions of the block</li>
     *      <li>@type string $url         the new URL of the block</li>
     *      <li>@type string $language    the new language of the block</li>
     *      <li>@type string $content     the new content of the block</li>
     *                       </ul>}
     *
     * @return bool true on success, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to update the block
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
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Security check
        // this function is called during the init process so we have to check in _ZINSTALLVER
        // is set as alternative to the correct permission check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('ZikulaBlocksModule::', "$args[bkey]:$args[title]:$args[bid]", ACCESS_EDIT)) {
            throw new AccessDeniedHttpException();
        }

        // remove old placements and insert the new ones
        $items = $this->entityManager->getRepository('Zikula\Module\BlocksModule\Entity\BlockPlacementEntity')
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
                $placement = new BlockPlacementEntity();
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
        $item = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'get', array('bid' => $args['bid']));
        $item->merge($args);

        $this->entityManager->flush();

        return true;
    }

    /**
     * Create a new block.
     *
     * @param mixed[] $args {
     *      @type string $title       the title of the block</li>
     *      @type string $description the description of the block</li>
     *      @type int    $mid         the module ID of the block</li>
     *      @type string $language    the language of the block</li>
     *      @type int    $bkey        the key of the block</li>
     *
     * @return int|bool block id on success, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to create the block
     */
    public function create($args)
    {
        // Argument check
        if ((!isset($args['title'])) ||
            (!isset($args['description'])) ||
            (!isset($args['mid']) || !is_numeric($args['mid'])) ||
            (!isset($args['language'])) ||
            (!isset($args['collapsable'])) ||
            (!isset($args['defaultstate'])) ||
            (!isset($args['bkey']))) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Security check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('ZikulaBlocksModule::', "$args[bkey]:$args[title]:", ACCESS_ADD)) {
            throw new AccessDeniedHttpException();
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

        $item = new BlockEntity();
        $item->merge($block);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        // insert block positions for this block
        if (isset($args['positions']) && is_array($args['positions'])) {

            foreach ($args['positions'] as $position) {
                $placement = new BlockPlacementEntity();
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
     * @param int[] $block {<ul>
     *      <li>@type int $bid the ID of the block to deactivate</li>
     *                      </ul>} 
     *
     * @return bool true on success, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to update the block
     */
    public function setActiveState($block)
    {
        if (!isset($block['bid']) || !is_numeric($block['bid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!isset($block['active']) || !is_numeric($block['active'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $item = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'get', array('bid' => $block['bid']));
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::', "$item[bkey]:$item[title]:$item[bid]", ACCESS_EDIT)) {
            throw new AccessDeniedHttpException();
        }

        // set block's new state
        $item['active'] = $block['active'];
        $this->entityManager->flush();

        return true;
    }

    /**
     * Deactivate a block.
     *
     * @param int[] $args {<ul>
     *      <li>@type int $bid the ID of the block to deactivate</li>
     *                     </ul>} 
     *
     * @return bool true on success, false on failure.
     *
     * @throws \RuntimeException Thrown if block cannot be deactivated
     */
    public function deactivate($args)
    {
        $args['active'] = 0;
        $res = (boolean)$this->setActiveState($args);

        if (!$res) {
            throw new \RuntimeException($this->__('Error! Could not deactivate the block.'));
        }

        return $res;
    }

    /**
     * Activate a block.
     *
     * @param int[] $args {<ul>
     *      <li>@type int $bid the ID of the block to activate.
     *                     </ul>}
     *
     * @return bool true on success, false on failure.
     *
     * @throws \RuntimeException Thrown if the block cannot be activated
     */
    public function activate($args)
    {
        $args['active'] = 1;
        $res = (boolean)$this->setActiveState($args);

        if (!$res) {
            throw new \RuntimeException($this->__('Error! Could not activate the block.'));
        }

        return $res;
    }

    /**
     * Delete a block.
     *
     * @param int[] $args {<ul>
     *      <li>@type int $args ['bid'] the ID of the block to delete</li>
     *                     </ul>
     *
     * @return bool true on success, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to delete the block
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['bid']) || !is_numeric($args['bid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $block = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'get', array('bid' => $args['bid']));

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::', "$block[bkey]:$block[title]:$block[bid]", ACCESS_DELETE)) {
            throw new AccessDeniedHttpException();
        }

        // delete block's placements
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('Zikula\Module\BlocksModule\Entity\BlockPlacementEntity', 'p')
                                     ->where('p.bid = :bid')
                                     ->setParameter('bid', $block['bid'])
                                     ->getQuery();

        $query->getResult();

        // Now actually delete the block
        $this->entityManager->remove($block);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Create a block position.
     *
     * @param string[] $args {<ul>
     *      <li>@type string $name        name of the position</li>
     *      <li>@type string $description description of the position</li>
     *                        </ul>
     *
     * @return int|bool position ID on success, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to create the block position
     * @throws \RuntimeException Thrown if a block position with the same name already exists
     */
    public function createposition($args)
    {
        // Argument check
        if (!isset($args['name']) || !strlen($args['name']) ||
            !isset($args['description'])) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Security check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('ZikulaBlocksModule::position', "$args[name]::", ACCESS_ADD)) {
            throw new AccessDeniedHttpException();
        }

        $positions = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getallpositions');
        if (isset($positions) && is_array($positions)) {
            foreach ($positions as $position) {
                if ($position['name'] == $args['name']) {
                    throw new \RuntimeException($this->__('Error! There is already a block position with the name you entered.'));
                }
            }
        }

        $item = new BlockPositionEntity();
        $item->merge($args);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        // Return the id of the newly created item to the calling process
        return $item['pid'];
    }

    /**
     * Update a block position item.
     *
     * @param mixed[] $args {<ul>
     *      <li>@type int    $pid         the ID of the item</li>
     *      <li>@type string $name        name of the block position</li>
     *      <li>@type string $description description of the block position</li>
     *                       </ul>}
     *
     * @return bool true if successful, false otherwise.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to update the block position
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException Thrown if the block position to be updated doesn't exist
     * @throws \RuntimeException Thrown if a block position with the same name, but different id, already exists
     */
    public function updateposition($args)
    {
        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid']) ||
            !isset($args['name']) ||
            !isset($args['description'])) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Get the existing position
        $item = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getposition', array('pid' => $args['pid']));

        if ($item == false) {
            throw new NotFoundHttpException($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::position', "$item[name]::$item[pid]", ACCESS_EDIT)) {
            throw new AccessDeniedHttpException();
        }

        $positions = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getallpositions');
        if (isset($positions) && is_array($positions)) {
            foreach ($positions as $position) {
                if ($position['name'] == $args['name'] && $position['pid'] != $args['pid']) {
                    throw new \RuntimeException($this->__('Error! There is already a block position with the name you entered.'));
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
     * @param int[] $args {<ul>
     *      <li>@type int $pid ID of the position</li>
     *                     </ul>}
     *
     * @return bool true on success, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to delete the block position
     */
    public function deleteposition($args)
    {
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $position = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getposition', array('pid' => $args['pid']));

        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::position', "$position[name]::$position[pid]", ACCESS_DELETE)) {
            throw new AccessDeniedHttpException();
        }

        // delete placements of the position to be deleted
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('Zikula\Module\BlocksModule\Entity\BlockPlacementEntity', 'p')
                                     ->where('p.pid = :pid')
                                     ->setParameter('pid', $position['pid'])
                                     ->getQuery();

        $query->getResult();

        // Now actually delete the position
        $this->entityManager->remove($position);
        $this->entityManager->flush();

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
        $blockspositions = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getallpositions');

        // Create array for dropdown menu links
        foreach ($blockspositions as $blocksposition) {
            $filter['blockposition_id'] = $blocksposition['pid'];
            $submenulinks[] = array('url' => ModUtil::url('ZikulaBlocksModule', 'admin', 'view', array('filter' => $filter)),
                    'text' => $this->__f('Position "%s"', $blocksposition['name']));
        }

        if (SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            $links[] = array('url' => ModUtil::url('ZikulaBlocksModule', 'admin', 'view'),
                    'text' => $this->__('Blocks list'),
                    'icon' => 'table',
                    'links' => $submenulinks);
        }

        if (SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('ZikulaBlocksModule', 'admin', 'newblock'), 'text' => $this->__('Create new block'), 'icon' => 'plus');
        }
        if (SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('ZikulaBlocksModule', 'admin', 'newposition'), 'text' => $this->__('Create new block position'), 'icon' => 'plus');
        }
        if (SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaBlocksModule', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'icon' => 'wrench');
        }

        return $links;
    }
}