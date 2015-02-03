<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\BlocksModule\Controller;

use SecurityUtil;
use Zikula\Module\BlocksModule\Entity\BlockPlacementEntity;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\FatalResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
use BlockUtil;
use DataUtil;
use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove

/**
 * @Route("/ajax")
 * 
 * Ajax controllers for the blocks module
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * @Route("/changeorder", options={"expose"=true})
     * @Method("POST")
     * 
     * Changeblockorder.
     *
     * @param Request $request
     * 
     *  blockorder array of sorted blocks (value = block id)
     *  position int zone id
     *
     * @return AjaxResponse|ForbiddenResponse true or Ajax error
     */
    public function changeblockorderAction(Request $request)
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {

            return new ForbiddenResponse($this->__('No permission for this action.'));
        }

        $blockorder = $request->request->get('blockorder', array());
        $position = $request->request->get('position');

        // remove all blocks from this position
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('ZikulaBlocksModule:BlockPlacementEntity', 'p')
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

        return new AjaxResponse(array('result' => true));
    }

    /**
     * @Route("/toggle", options={"expose"=true})
     * @Method("POST")
     * 
     * Toggleblock.
     *
     * This function toggles active/inactive.
     *
     * @param Request $request
     * 
     *  bid int id of block to toggle.
     *
     * @return AjaxResponse|FatalResponse|ForbiddenResponse true or Ajax error
     */
    public function toggleblockAction(Request $request)
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {

            return new ForbiddenResponse($this->__('No permission for this action.'));
        }

        $bid = $request->request->get('bid', -1);

        if ($bid == -1) {

            return new FatalResponse($this->__('No block ID passed.'));
        }

        // read the block information
        $blockinfo = BlockUtil::getBlockInfo($bid);
        if ($blockinfo == false) {

            return new FatalResponse($this->__f('Error! Could not retrieve block information for block ID %s.', DataUtil::formatForDisplay($bid)));
        }

        if ($blockinfo['active'] == 1) {
            ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'deactivate', array('bid' => $bid));
        } else {
            ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'activate', array('bid' => $bid));
        }

        return new AjaxResponse(array('bid' => $bid));
    }
}