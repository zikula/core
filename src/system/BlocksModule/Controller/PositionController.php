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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
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
     * @Template
     *
     * @param Request $request
     * @param BlockPositionEntity $positionEntity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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

        $form = $this->createForm('Zikula\BlocksModule\Form\Type\BlockPositionType', $positionEntity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                /** @var \Doctrine\ORM\EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->persist($positionEntity);
                $em->flush();
                $this->addFlash('status', __('Position saved!'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', __('Operation cancelled.'));
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
     * @Template
     *
     * Delete a position.
     *
     * @param Request $request
     * @param BlockPositionEntity $positionEntity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, BlockPositionEntity $positionEntity)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::position', $positionEntity->getName() .'::'. $positionEntity->getPid(), ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder()
            ->add('delete', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => 'Delete'])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => 'Cancel'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($positionEntity);
                $em->flush();
                $this->addFlash('status', __('Done! Position deleted.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', __('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulablocksmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
            'position' => $positionEntity
        ];
    }
}
