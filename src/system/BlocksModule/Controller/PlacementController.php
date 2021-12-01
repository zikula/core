<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class PlacementController
 *
 * @Route("/admin/placement")
 * @PermissionCheck("admin")
 */
class PlacementController extends AbstractController
{
    /**
     * @Route("/edit/{pid}", requirements={"pid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaBlocksModule/Placement/edit.html.twig")
     *
     * Create a new placement or edit an existing placement.
     */
    public function edit(
        BlockPositionEntity $positionEntity,
        BlockRepositoryInterface $blockRepository,
        BlockPositionRepositoryInterface $positionRepository
    ): array {
        $allBlocks = $blockRepository->findAll();
        $assignedBlocks = [];
        foreach ($positionEntity->getPlacements() as $blockPlacement) {
            $bid = $blockPlacement->getBlock()->getBid();
            foreach ($allBlocks as $key => $allblock) {
                if ($allblock->getBid() === $bid) {
                    unset($allBlocks[$key]);
                }
            }
            $assignedBlocks[] = $blockPlacement->getBlock();
        }

        return [
            'position' => $positionEntity,
            'positionChoices' => $positionRepository->getPositionChoiceArray(),
            'assignedblocks' => $assignedBlocks,
            'unassignedblocks' => $allBlocks
        ];
    }

    /**
     * @Route("/ajax/changeorder", methods = {"POST"}, options={"expose"=true, "i18n"=false})
     *
     * Change the block order.
     */
    public function changeBlockOrder(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $blockOrder = $request->request->get('blockorder', null); // [7, 1]
        if (null === $blockOrder) {
            $blockOrder = [];
        }
        $position = $request->request->get('position'); // 1
        /** @var EntityManager $em */
        $em = $doctrine->getManager();

        // remove all block placements from this position
        $query = $em->createQueryBuilder()
            ->delete()
            ->from('ZikulaBlocksModule:BlockPlacementEntity', 'p')
            ->where('p.position = :position')
            ->setParameter('position', $position)
            ->getQuery();
        $query->getResult();

        // add new block positions
        foreach ((array) $blockOrder as $order => $bid) {
            $placement = new BlockPlacementEntity();
            $placement->setPosition($em->getReference('ZikulaBlocksModule:BlockPositionEntity', $position));
            $placement->setBlock($em->getReference('ZikulaBlocksModule:BlockEntity', $bid));
            $placement->setSortorder($order);
            $em->persist($placement);
        }
        $em->flush();

        return $this->json(['result' => true]);
    }
}
