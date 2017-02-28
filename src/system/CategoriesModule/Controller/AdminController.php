<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zikula\Core\Controller\AbstractController;

/**
 * @Route("/admin")
 * @deprecated
 */
class AdminController extends AbstractController
{
    /**
     * Route not needed here because method is legacy-only.
     * @deprecated since 1.4.0 see indexAction()
     */
    public function mainAction()
    {
        @trigger_error('The zikulacategoriesmodule_admin_main action is deprecated. please use zikulacategoriesmodule_category_list instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_category_list');
    }

    /**
     * @Route("")
     */
    public function indexAction()
    {
        @trigger_error('The zikulacategoriesmodule_admin_index route is deprecated. please use zikulacategoriesmodule_category_list instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_category_list');
    }

    /**
     * @Route("/view")
     */
    public function viewAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_view route is deprecated. please use zikulacategoriesmodule_category_list instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_category_list');
    }

    /**
     * @Route("/rebuild")
     */
    public function rebuildAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_rebubild route is no longer used.', E_USER_DEPRECATED);
        $this->addFlash('info', $this->__('Category paths no longer need to be rebuilt.'));

        return $this->redirectToRoute('zikulacategoriesmodule_category_list');
    }

    /**
     * @Route("/edit/{cid}/{dr}/{mode}", requirements={"cid" = "^[1-9]\d*$", "dr" = "^[1-9]\d*$", "mode" = "edit|new"})
     */
    public function editAction(Request $request, $cid = 0, $dr = 1, $mode = 'new')
    {
        @trigger_error('The zikulacategoriesmodule_admin_edit action is deprecated. please use zikulacategoriesmodule_category_edit instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_category_edit', ['category' => $cid]);
    }

    /**
     * @Route("/editregistry")
     */
    public function editregistryAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_editregistry action is deprecated. please use zikulacategoriesmodule_registry_edit instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_registry_edit', $request->query->all());
    }

    /**
     * @Route("/deleteregistry")
     */
    public function deleteregistryAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_deleteregistry action is deprecated. please use zikulacategoriesmodule_registry_delete instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_registry_delete', $request->query->all());
    }

    /**
     * @Route("/new")
     */
    public function newcatAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_new action is deprecated. please use zikulacategoriesmodule_category_edit instead.', E_USER_DEPRECATED);
        $path = [
            '_controller' => 'ZikulaCategoriesModule:Category:edit',
            'mode' => 'new'
        ];
        $subRequest = $request->duplicate($request->query->all(), $request->request->all(), $path);

        return $this->get('kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @Route("/update")
     */
    public function updateAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_update action is deprecated. please use zikulacategoriesmodule_category_edit instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_category_edit');
    }

    /**
     * @Route("/op")
     *
     * Generic function to handle copy, delete and move operations.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have access to delete the category
     */
    public function opAction(Request $request)
    {
        $cid = $request->query->getInt('cid', 1);
        $root_id = $request->query->get('dr', 1);
        $op = $request->query->get('op', 'NOOP');
        if (!in_array($op, ['copy', 'move', 'delete'])) {
            throw new AccessDeniedException();
        }

        if (!$this->hasPermission('ZikulaCategoriesModule::category', "ID::$cid", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $categoryApi = $this->get('zikula_categories_module.api.category');
        $category = $categoryApi->getCategoryById($cid);
        if (null === $category) {
            throw new NotFoundHttpException($this->__('Category not found.'));
        }

        if ($op == 'delete' || $op == 'move') {
            // prevent deletion or move if category is already used
            $processingHelper = $this->get('zikula_categories_module.category_processing_helper');
            if (!$processingHelper->mayCategoryBeDeletedOrMoved($category)) {
                if ($op == 'delete') {
                    $this->addFlash('error', $this->__f('Error! Category %s can not be deleted, because it is already used.', ['%s' => $category['name']]));
                } elseif ($op == 'move') {
                    $this->addFlash('error', $this->__f('Error! Category %s can not be moved, because it is already used.', ['%s' => $category['name']]));
                }

                return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
            }
        }

        $isCopy = $op == 'copy';
        $isMove = $op == 'move';
        $isDelete = $op == 'delete';

        $parentLabel = $isCopy
            ? $this->__('Copy this category and all sub-categories of this category into')
            : $this->__('Move this category and all sub-categories of this category into');
        $actionLabel = $isCopy ? $this->__('Copy') : ($isMove ? $this->__('Move') : $this->__('Delete'));
        $actionIcon = $isCopy ? 'files-o' : ($isMove ? 'scissors' : 'trash-o');

        $amountOfSubCategories = 0;

        $builder = $this->createFormBuilder()
            ->add($op, 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $actionLabel,
                'icon' => 'fa-' . $actionIcon,
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ]);

        $templateParameters = [
            'category' => $category
        ];

        if ($isDelete) {
            $subCategories = $categoryApi->getSubCategories($cid, false, false);
            $amountOfSubCategories = count($subCategories);
            if ($amountOfSubCategories > 0) {
                $builder->add('subcatAction', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                    'label' => $this->__('Action for sub-categories'),
                    'choices' => [
                        $this->__('Delete all sub-categories') => 'delete',
                        $this->__('Move all sub-categories into') => 'move'
                    ],
                    'choices_as_values' => true,
                    'expanded' => true
                ]);
            }
            $templateParameters['amountOfSubCategories'] = $amountOfSubCategories;
        }

        if (!$isDelete || $amountOfSubCategories > 0) {
            $builder->add('parent', 'Zikula\CategoriesModule\Form\Type\CategoryTreeType', [
                'label' => $parentLabel,
                'translator' => $this->get('translator.default'),
                'includeRoot' => true
            ]);
        }

        $form = $builder->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get($op)->isClicked()) {
                $formData = $form->getData();
                $copyAndMoveHelper = $this->get('zikula_categories_module.copy_and_move_helper');

                if ($isCopy) {
                    $copyAndMoveHelper->copyCategoriesByPath($category['ipath'], $formData['parent']);
                    $this->addFlash('status', $this->__f('Done! Copied the %s category.', ['%s' => $category['name']]));
                } elseif ($isMove) {
                    $copyAndMoveHelper->moveCategoriesByPath($category['ipath'], $formData['parent']);
                    $this->addFlash('status', $this->__f('Done! Moved the %s category.', ['%s' => $category['name']]));
                } elseif ($isDelete) {
                    if ($amountOfSubCategories > 0) {
                        if ($formData['subcatAction'] == 'delete') {
                            // delete subdirectories
                            $categoryApi->deleteCategoriesByPath($category['ipath']);
                        } elseif ($formData['subcatAction'] == 'move') {
                            // move subdirectories
                            $copyAndMoveHelper->moveSubCategoriesByPath($category['ipath'], $formData['parent']);
                            $categoryApi->deleteCategoryById($cid);
                        }
                    }
                    $this->addFlash('status', $this->__f('Done! Deleted the %s category.', ['%s' => $category['name']]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }

        $templateParameters['form'] = $form->createView();

        return $this->render('@ZikulaCategoriesModule/Admin/' . $op . '.html.twig', $templateParameters);
    }

    /**
     * @Route("/preferences")
     */
    public function preferencesAction()
    {
        @trigger_error('The zikulacategoriesmodule_admin_preferences route is deprecated. please use zikulacategoriesmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_config_config');
    }
}
