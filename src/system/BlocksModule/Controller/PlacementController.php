<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Theme\Annotation\Theme;
use Zikula\Core\Response\Ajax\ForbiddenResponse;

/**
 * Class PlacementController
 * @package Zikula\BlocksModule\Controller
 * @Route("/admin/placement")
 */
class PlacementController extends AbstractController
{
    /**
     * Create a new position or edit an existing position.
     *
     * @Route("/edit/{pid}", requirements={"pid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * @param BlockPositionEntity $positionEntity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(BlockPositionEntity $positionEntity)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $allBlocks = $this->getDoctrine()->getManager()->getRepository('ZikulaBlocksModule:BlockEntity')->findAll();
        $assignedBlocks = [];
        foreach ($positionEntity->getPlacements() as $blockPlacement) {
            $bid = $blockPlacement->getBlock()->getBid();
            foreach ($allBlocks as $key => $allblock) {
                if ($allblock->getBid() == $bid) {
                    unset($allBlocks[$key]);
                }
            }
            $assignedBlocks[] = $blockPlacement->getBlock();
        }

        return [
            'position' => $positionEntity,
            'positionChoices' => $this->getDoctrine()->getRepository('ZikulaBlocksModule:BlockPositionEntity')->getPositionChoiceArray(),
            'assignedblocks' => $assignedBlocks,
            'unassignedblocks' => $allBlocks
        ];
    }

    /**
     * @Route("/ajax/changeorder", options={"expose"=true, "i18n"=false})
     * @Method("POST")
     *
     * Change the block order.
     *
     * @param Request $request
     *
     *  blockorder array of sorted blocks (value = block id)
     *  position int zone id
     *
     * @return JsonResponse|ForbiddenResponse true or Ajax error
     */
    public function changeBlockOrderAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action.'));
        }

        $blockorder = $request->request->get('blockorder', []); // [7, 1]
        $position = $request->request->get('position'); // 1
        $em = $this->getDoctrine()->getManager();

        // remove all block placements from this position
        $query = $em->createQueryBuilder()
            ->delete()
            ->from('ZikulaBlocksModule:BlockPlacementEntity', 'p')
            ->where('p.position = :position')
            ->setParameter('position', $position)
            ->getQuery();
        $query->getResult();

        // add new block positions
        foreach ((array)$blockorder as $order => $bid) {
            $placement = new BlockPlacementEntity();
            $placement->setPosition($em->getReference('ZikulaBlocksModule:BlockPositionEntity', $position));
            $placement->setBlock($em->getReference('ZikulaBlocksModule:BlockEntity', $bid));
            $placement->setSortorder($order);
            $em->persist($placement);
        }
        $em->flush();

        return new JsonResponse(['result' => true]);
    }
}
