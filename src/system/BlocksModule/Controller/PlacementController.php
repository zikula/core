<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Controller;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class PlacementController
 * @Route("/admin/placement")
 */
class PlacementController extends AbstractController
{
    /**
     * @Route("/edit/{pid}", requirements={"pid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaBlocksModule/Placement/edit.html.twig")
     *
     * Create a new placement or edit an existing placement.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function editAction(
        BlockPositionEntity $positionEntity,
        BlockRepositoryInterface $blockRepository,
        BlockPositionRepositoryInterface $positionRepository
    ): array {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

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
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function changeBlockOrderAction(Request $request): JsonResponse
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->trans('No permission for this action.'), Response::HTTP_FORBIDDEN);
        }

        $blockorder = $request->request->get('blockorder', []); // [7, 1]
        $position = $request->request->get('position'); // 1
        /** @var EntityManager $em */
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

        return $this->json(['result' => true]);
    }
}
