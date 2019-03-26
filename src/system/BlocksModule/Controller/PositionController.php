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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Form\Type\BlockPositionType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class PositionController
 * @Route("/admin/position")
 */
class PositionController extends AbstractController
{
    /**
     * Create a new position or edit an existing position.
     *
     * @Route("/edit/{positionEntity}", requirements={"positionEntity" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaBlocksModule:Position:edit.html.twig")
     *
     * @param Request $request
     * @param BlockPositionEntity $positionEntity
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, BlockPositionEntity $positionEntity = null)
    {
        $permParam = (null !== $positionEntity) ? $positionEntity->getName() : 'position';
        if (!$this->hasPermission('ZikulaBlocksModule::' . $permParam, '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (null === $positionEntity) {
            $positionEntity = new BlockPositionEntity(); // sets defaults in constructor
        }

        $form = $this->createForm(BlockPositionType::class, $positionEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                /** @var \Doctrine\ORM\EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->persist($positionEntity);
                $em->flush();
                $this->addFlash('status', $this->__('Position saved!'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulablocksmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/delete/{pid}", requirements={"pid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaBlocksModule:Position:delete.html.twig")
     *
     * Delete a position.
     *
     * @param Request $request
     * @param BlockPositionEntity $positionEntity
     *
     * @return RedirectResponse|Response
     */
    public function deleteAction(Request $request, BlockPositionEntity $positionEntity)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::position', $positionEntity->getName() . '::' . $positionEntity->getPid(), ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(DeletionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($positionEntity);
                $em->flush();
                $this->addFlash('status', $this->__('Done! Position deleted.'));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulablocksmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
            'position' => $positionEntity
        ];
    }
}
