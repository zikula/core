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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\BlockHandlerInterface;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\FatalResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
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
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('bkey', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'placeholder' => 'Choose a block type',
                'choices' => $this->get('zikula_blocks_module.api.block')->getAvailableBlockTypes(),
                'label' => 'Block type',
            ])
            ->add('choose', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $bkey = json_encode($form->getData()['bkey']);

            return $this->redirectToRoute('zikulablocksmodule_block_edit', ['bkey' => $bkey]);
        }

        return $this->render('@ZikulaBlocksModule/Admin/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Create a new block or edit an existing block.
     *
     * @Route("/edit/{blockEntity}", requirements={"blockEntity" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @param Request $request
     * @param BlockEntity $blockEntity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, BlockEntity $blockEntity = null)
    {
        $accessLevelRequired = ACCESS_EDIT;
        if (null === $blockEntity) {
            $bKey = json_decode($request->query->get('bkey'));
            if (empty($bKey)) {
                return $this->redirectToRoute('zikulablocksmodule_block_new');
            }
            $blockEntity = new BlockEntity(); // sets defaults in constructor
            $blockEntity->setBkey($bKey);
            $accessLevelRequired = ACCESS_ADD;
        }

        if (!$this->hasPermission('ZikulaBlocksModule::', $blockEntity->getBlocktype() . ':' . $blockEntity->getTitle() . ':' . $blockEntity->getBid(), $accessLevelRequired)) {
            throw new AccessDeniedException();
        }

        $blockInstance = $this->get('zikula_blocks_module.api.block')->createInstanceFromBKey($blockEntity->getBkey());
        $blockType = $blockEntity->getBlocktype();
        if (empty($blockType)) {
            $blockEntity->setBlocktype($blockInstance->getType());
        }

        $form = $this->createForm('Zikula\BlocksModule\Form\Type\BlockType', $blockEntity);
        if (($blockInstance instanceof BlockHandlerInterface) && (null !== $blockInstance->getFormClassName())) {
            $form->add('properties', $blockInstance->getFormClassName(), $blockInstance->getFormOptions());
        }
        $form->handleRequest($request);

        list($moduleName, $blockFqCn) = explode(':', $blockEntity->getBkey());
        // @todo @deprecated remove this code block (re: $renderedPropertiesForm) at Core-2.0
        $renderedPropertiesForm = $this->getBlockModifyOutput($blockInstance, $blockEntity);
        if (($blockInstance instanceof \Zikula_Controller_AbstractBlock) && $blockInstance->info()['form_content']) {
            $renderedPropertiesForm = $this->formContentModify($request, $blockEntity);
        }

        if ($form->isSubmitted() and $form->get('save')->isClicked() and $form->isValid()) {
            if ($blockInstance instanceof \Zikula_Controller_AbstractBlock) { // @todo remove this BC at Core-2.0
                if ($blockInstance->info()['form_content']) {
                    $content = $this->formContentModify($request);
                    $blockEntity->setContent($content);
                } else {
                    $blockInfo = call_user_func([$blockInstance, 'update'], $blockEntity->toArray());
                    $properties = $blockInfo['content'];
                    $blockEntity->setProperties($properties);
                }
            }

            // sort Filter array so keys are always sequential.
            $filters = $blockEntity->getFilters();
            sort($filters);
            $blockEntity->setFilters($filters);
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $module = $em->getRepository('ZikulaExtensionsModule:ExtensionEntity')->findOneBy(['name' => $moduleName]);
            $blockEntity->setModule($module);
            $em->persist($blockEntity);
            $em->flush();
            $this->addFlash('status', $this->__('Block saved!'));

            return $this->redirectToRoute('zikulablocksmodule_admin_view');
        }
        if ($form->isSubmitted() and $form->get('cancel')->isClicked()) {
            $this->addFlash('status', $this->__('Operation cancelled.'));

            return $this->redirectToRoute('zikulablocksmodule_admin_view');
        }

        return $this->render('@ZikulaBlocksModule/Admin/edit.html.twig', [
            'moduleName' => $moduleName,
            'renderedPropertiesForm' => $renderedPropertiesForm, // @remove at Core-2.0
            'propertiesFormTemplate' => ($blockInstance instanceof BlockHandlerInterface) ? $blockInstance->getFormTemplate() : null,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{bid}", requirements={"bid" = "^[1-9]\d*$"})
     * @Theme("admin")
     *
     * Delete a block.
     *
     * @param Request $request
     * @param BlockEntity $blockEntity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, BlockEntity $blockEntity)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', $blockEntity->getBkey() . ':' . $blockEntity->getTitle() . ':' . $blockEntity->getBid(), ACCESS_DELETE)) {
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
                $em->remove($blockEntity);
                $em->flush();
                $this->addFlash('status', $this->__('Done! Block deleted.'));
            }
            if ($form->get('cancel')->isClicked()) {
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
     * Ajax method to toggle the active status of a block.
     *
     *  bid int id of block to toggle.
     *
     * @Route("/toggle-active", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse|FatalResponse|ForbiddenResponse bid or Ajax error
     */
    public function toggleblockAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action.'));
        }
        $bid = $request->request->get('bid', -1);
        if ($bid == -1) {
            return new FatalResponse($this->__('No block ID passed.'));
        }
        $em = $this->getDoctrine()->getManager();
        $block = $em->find('ZikulaBlocksModule:BlockEntity', $bid);
        $block->setActive($block->getActive() == BlockApi::BLOCK_ACTIVE ? BlockApi::BLOCK_INACTIVE : BlockApi::BLOCK_ACTIVE);
        $em->flush();

        return new JsonResponse(['bid' => $bid]);
    }

    /**
     * Get the html output from the block's `modify` method.
     *
     * @deprecated Remove at Core-2.0.
     * @param $blockClassInstance
     * @param BlockEntity $blockEntity
     * @return mixed|string
     */
    private function getBlockModifyOutput($blockClassInstance, BlockEntity $blockEntity)
    {
        $output = '';
        if ($blockClassInstance instanceof \Zikula_Controller_AbstractBlock) {
            $blockInfo = \BlockUtil::getBlockInfo($blockEntity->getBid());
            $blockInfo = $blockInfo ? $blockInfo : ['content' => ''];
            $output = call_user_func([$blockClassInstance, 'modify'], $blockInfo);
        }

        return $output;
    }

    /**
     * Handle modification of blocks with form_content = true
     *
     * @deprecated This option is no longer allowed in Core-2.0. Blocks must provide their own properties handling.
     * @param Request $request
     * @param BlockEntity $blockEntity
     * @return mixed|string
     */
    private function formContentModify(Request $request, BlockEntity $blockEntity = null)
    {
        if (isset($blockEntity)) {
            $options = ['data' => $blockEntity->getContent() == [] ? '' : $blockEntity->getContent()];
        } else {
            $options = [];
        }
        $form = $this->createFormBuilder()
            ->add('content', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', $options)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $form->getData()['content'];
        }

        return $this->renderView('ZikulaBlocksModule:Block:default_modify.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/view/{bid}", requirements={"bid" = "^[1-9]\d*$"}, options={"expose"=true})
     * @Method("GET")
     *
     * Display a block.
     *
     * @param BlockEntity $blockEntity
     * @return Response symfony response object
     */
    public function viewAction(BlockEntity $blockEntity = null)
    {
        return $this->render('@ZikulaBlocksModule/Admin/blockview.html.twig', [
                'block' => $blockEntity,
            ]);
    }
}
