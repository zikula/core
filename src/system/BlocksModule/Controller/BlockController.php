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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Api\ApiInterface\BlockApiInterface;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\BlockHandlerInterface;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Form\Type\BlockType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class BlockController
 * @Route("/admin/block")
 */
class BlockController extends AbstractController
{
    /**
     * @Route("/new")
     * @Theme("admin")
     *
     * Choose type for creating a new block.
     */
    public function newAction(Request $request, BlockApiInterface $blockApi): Response
    {
        $form = $this->createFormBuilder()
            ->add('bkey', ChoiceType::class, [
                'placeholder' => 'Choose a block type',
                'choices' => array_flip($blockApi->getAvailableBlockTypes()),
                'label' => $this->__('Block type')
            ])
            ->add('choose', SubmitType::class, [
                'label' => $this->__('Choose'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $bkey = json_encode($form->getData()['bkey']);

            return $this->redirectToRoute('zikulablocksmodule_block_edit', ['bkey' => $bkey]);
        }

        return $this->render('@ZikulaBlocksModule/Admin/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{blockEntity}", requirements={"blockEntity" = "^[1-9]\d*$"})
     * @Theme("admin")
     *
     * Create a new block or edit an existing block.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permissions for creating new blocks or editing a given one
     */
    public function editAction(
        Request $request,
        BlockApiInterface $blockApi,
        ExtensionRepositoryInterface $extensionRepository,
        BlockEntity $blockEntity = null
    ): Response {
        $accessLevelRequired = ACCESS_EDIT;
        if (null === $blockEntity) {
            $bKey = json_decode($request->query->get('bkey'));
            if (empty($bKey)) {
                return $this->redirectToRoute('zikulablocksmodule_block_new');
            }
            $blockEntity = new BlockEntity(); // sets defaults in constructor
            $blockEntity->setBkey($bKey);
            $accessLevelRequired = ACCESS_ADD;
            $request->attributes->set('blockEntity', $blockEntity);
        }

        if (!$this->hasPermission('ZikulaBlocksModule::', $blockEntity->getBlocktype() . ':' . $blockEntity->getTitle() . ':' . $blockEntity->getBid(), $accessLevelRequired)) {
            throw new AccessDeniedException();
        }

        $blockInstance = $blockApi->createInstanceFromBKey($blockEntity->getBkey());
        $blockType = $blockEntity->getBlocktype();
        if (empty($blockType)) {
            $blockEntity->setBlocktype($blockInstance->getType());
        }

        $form = $this->createForm(BlockType::class, $blockEntity, ['locale' => $request->getLocale()]);
        if (($blockInstance instanceof BlockHandlerInterface) && (null !== $blockInstance->getFormClassName())) {
            $form->add('properties', $blockInstance->getFormClassName(), $blockInstance->getFormOptions());
        }
        $form->handleRequest($request);

        list($moduleName) = explode(':', $blockEntity->getBkey());
        if ($form->isSubmitted()) {
            if ($form->isValid() && $form->get('save')->isClicked()) {
                // sort filter array so keys are always sequential.
                $filters = $blockEntity->getFilters();
                sort($filters);
                $blockEntity->setFilters($filters);

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                /** @var ExtensionEntity $module */
                $module = $extensionRepository->findOneBy(['name' => $moduleName]);
                $blockEntity->setModule($module);

                $em->persist($blockEntity);
                $em->flush();
                $this->addFlash('status', $this->__('Block saved!'));

                return $this->redirectToRoute('zikulablocksmodule_admin_view');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));

                return $this->redirectToRoute('zikulablocksmodule_admin_view');
            }
        }

        return $this->render('@ZikulaBlocksModule/Admin/edit.html.twig', [
            'moduleName' => $moduleName,
            'propertiesFormTemplate' => ($blockInstance instanceof BlockHandlerInterface) ? $blockInstance->getFormTemplate() : null,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{bid}", requirements={"bid" = "^[1-9]\d*$"})
     * @Theme("admin")
     *
     * Delete a block.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions for the block
     */
    public function deleteAction(Request $request, BlockEntity $blockEntity): Response
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', $blockEntity->getBkey() . ':' . $blockEntity->getTitle() . ':' . $blockEntity->getBid(), ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(DeletionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($blockEntity);
                $em->flush();
                $this->addFlash('status', $this->__('Done! Block deleted.'));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulablocksmodule_admin_view');
        }

        return $this->render('@ZikulaBlocksModule/Admin/delete.html.twig', [
            'form' => $form->createView(),
            'block' => $blockEntity
        ]);
    }

    /**
     * @Route("/toggle-active", methods = {"POST"}, options={"expose"=true})
     *
     * Ajax method to toggle the active status of a block.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function toggleblockAction(Request $request): JsonResponse
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('No permission for this action.'), Response::HTTP_FORBIDDEN);
        }
        $bid = $request->request->getInt('bid', -1);
        if (-1 === $bid) {
            return $this->json($this->__('No block ID passed.'), Response::HTTP_BAD_REQUEST);
        }
        $em = $this->getDoctrine()->getManager();
        $block = $em->find('ZikulaBlocksModule:BlockEntity', $bid);
        if (null !== $block) {
            $block->setActive(BlockApi::BLOCK_ACTIVE === $block->getActive() ? BlockApi::BLOCK_INACTIVE : BlockApi::BLOCK_ACTIVE);
            $em->flush();
        }

        return $this->json(['bid' => $bid]);
    }

    /**
     * @Route("/view/{bid}", requirements={"bid" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Display a block.
     */
    public function viewAction(BlockEntity $blockEntity = null): response
    {
        return $this->render('@ZikulaBlocksModule/Admin/blockview.html.twig', [
            'block' => $blockEntity,
        ]);
    }
}
