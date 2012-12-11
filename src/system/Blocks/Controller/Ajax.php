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
 * Blocks_Controller_Ajax class.
 */
class Blocks_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * Changeblockorder.
     *
     * @param blockorder array of sorted blocks (value = block id)
     * @param position int zone id
     *
     * @return mixed true or Ajax error
     */
    public function changeblockorder()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN));

        $blockorder = $this->request->request->get('blockorder');
        $position = $this->request->request->get('position');

        // remove all blocks from this position
        $entity = $this->name . '_Entity_BlockPlacement';
        $dql = "DELETE FROM $entity p WHERE p.pid = {$position}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();

        // add new block positions
        foreach ((array)$blockorder as $order => $bid) {
            $placement = new Blocks_Entity_BlockPlacement();
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
     * @param bid int  id of block to toggle.
     *
     * @return mixed true or Ajax error
     */
    public function toggleblock()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN));

        $bid = $this->request->request->get('bid', -1);

        if ($bid == -1) {
            throw new Zikula_Exception_Fatal($this->__('No block ID passed.'));
        }

        // read the block information
        $blockinfo = BlockUtil::getBlockInfo($bid);
        if ($blockinfo == false) {
            throw new Zikula_Exception_Fatal($this->__f('Error! Could not retrieve block information for block ID %s.', DataUtil::formatForDisplay($bid)));
        }

        if ($blockinfo['active'] == 1) {
            ModUtil::apiFunc('Blocks', 'admin', 'deactivate', array('bid' => $bid));
        } else {
            ModUtil::apiFunc('Blocks', 'admin', 'activate', array('bid' => $bid));
        }

        return new Zikula_Response_Ajax(array('bid' => $bid));
    }
}
