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

namespace Zikula\Module\BlocksModule\Controller;

use SecurityUtil;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Zikula\Module\BlocksModule\Entity\BlockPlacementEntity;
use Zikula_Response_Ajax;
use Zikula_Exception_Fatal;
use BlockUtil;
use DataUtil;
use ModUtil;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * Blocks_Controller_Ajax class.
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * Changeblockorder.
     *
     * @param blockorder array of sorted blocks (value = block id)
     * @param position int zone id
     *
     * @return Zikula_Response_Ajax true or Ajax error
     */
    public function changeblockorderAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $blockorder = $this->request->request->get('blockorder');
        $position = $this->request->request->get('position');

        // remove all blocks from this position
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('Zikula\Module\BlocksModule\Entity\BlockPlacementEntity', 'p')
                                     ->where('p.pid = :pid')
                                     ->setParameter('pid', $position)
                                     ->getQuery();
        $query->getResult();

        // add new block positions
        foreach ((array)$blockorder as $order => $bid) {
            $placement = new BlockPlacementEntity();
            $placement->setPid($position);
            $placement->setBid($bid);
            $placement->setSortorder($order);
            $this->entityManager->persist($placement);
        }
        $this->entityManager->flush();

        return new Zikula_Response_Ajax(array('result' => true));
    }

    /**
     * Toggleblock.
     *
     * This function toggles active/inactive.
     *
     * @param bid int id of block to toggle.
     *
     * @return Zikula_Response_Ajax true or Ajax error
     *
     * @throws FatalErrorException  Thrown if no block id is supplied or
     *                                     if the requested block isn't valid
     */
    public function toggleblockAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $bid = $this->request->request->get('bid', -1);

        if ($bid == -1) {
            throw new FatalErrorException($this->__('No block ID passed.'));
        }

        // read the block information
        $blockinfo = BlockUtil::getBlockInfo($bid);
        if ($blockinfo == false) {
            throw new FatalErrorException($this->__f('Error! Could not retrieve block information for block ID %s.', DataUtil::formatForDisplay($bid)));
        }

        if ($blockinfo['active'] == 1) {
            ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'deactivate', array('bid' => $bid));
        } else {
            ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'activate', array('bid' => $bid));
        }

        return new Zikula_Response_Ajax(array('bid' => $bid));
    }
}