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

namespace BlocksModule\Controller;

use UserUtil, ModUtil, SecurityUtil, LogUtil, DataUtil, System, ZLanguage, CategoryRegistryUtil, CategoryUtil;
use PageUtil, ThemeUtil, BlockUtil, EventUtil, Zikula_View;
use Zikula\Framework\Exception\FatalException;
use Zikula\Framework\Response\Ajax\AjaxResponse;
use Zikula\Framework\Exception\BadDataException;
use BlocksModule\Entity\BlockPlacement;

/**
 * Blocks_Controller_Ajax class.
 */
class Blocks_Controller_AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * Changeblockorder.
     *
     * @param blockorder array of sorted blocks (value = block id)
     * @param position int zone id
     *
     * @return mixed true or Ajax error
     */
    public function changeblockorderAction()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN));

        $blockorder = $this->request->request->get('blockorder');
        $position = $this->request->request->get('position');

        // remove all blocks from this position
        $entity = $this->name . '\Entity\BlockPlacement';
        $dql = "DELETE FROM $entity p WHERE p.pid = {$position}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();
        
        // add new block positions
        foreach ((array)$blockorder as $order => $bid) {
            $placement = new BlockPlacement();
            $placement->setPid($position);
            $placement->setBid($bid);
            $placement->setSortorder($order);
            $this->entityManager->persist($placement);
        }
        $this->entityManager->flush();

        return new AjaxResponse(array('result' => true));
    }

    /**
     * Toggleblock.
     *
     * This function toggles active/inactive.
     *
     * @param bid int  id of block to toggle.
     *
     * @return mixed true or Ajax error
     */
    public function toggleblockAction()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN));

        $bid = $this->request->request->get('bid', -1);

        if ($bid == -1) {
            throw new FatalException($this->__('No block ID passed.'));
        }

        // read the block information
        $blockinfo = BlockUtil::getBlockInfo($bid);
        if ($blockinfo == false) {
            throw new FatalException($this->__f('Error! Could not retrieve block information for block ID %s.', DataUtil::formatForDisplay($bid)));
        }

        if ($blockinfo['active'] == 1) {
            ModUtil::apiFunc('BlocksModule', 'admin', 'deactivate', array('bid' => $bid));
        } else {
            ModUtil::apiFunc('BlocksModule', 'admin', 'activate', array('bid' => $bid));
        }

        return new AjaxResponse(array('bid' => $bid));
    }
}