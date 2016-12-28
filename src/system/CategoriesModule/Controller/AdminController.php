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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Core\Controller\AbstractController;
use ZLanguage;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the categories module.
 */
class AdminController extends AbstractController
{
    /**
     * Route not needed here because method is legacy-only.
     *
     * Main admin function.
     *
     * @deprecated since 1.4.0 see indexAction()
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        @trigger_error('The zikulacategoriesmodule_admin_main action is deprecated. please use zikulacategoriesmodule_admin_view instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("")
     *
     * Main admin function.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulacategoriesmodule_admin_index route is deprecated. please use zikulacategoriesmodule_admin_view instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/view")
     * @Template
     *
     * View categories.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit the category
     */
    public function viewAction(Request $request)
    {
        $root_id = $request->query->get('dr', 1);

        if (!$this->hasPermission('ZikulaCategoriesModule::category', "ID::$root_id", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (!$this->hasPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $cats = $this->get('zikula_categories_module.api.category')->getSubCategories($root_id, true, true, true, true, true);
        $menuTxt = $this->get('zikula_categories_module.js_tree_helper')->getCategoryTreeJqueryJs($cats, true, true);

        return [
            'menuTxt' => $menuTxt
        ];
    }

    /**
     * @Route("/rebuild")
     * @Template
     *
     * Displays page for rebuilding pathes.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have administrative permission for this module
     */
    public function rebuildAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder()
            ->add('rebuild', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Rebuild paths'),
                'icon' => 'fa-refresh',
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
            ])
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('rebuild')->isClicked()) {
                $pathBuilder = $this->get('zikula_categories_module.path_builder_helper');
                $pathBuilder->rebuildPaths('path', 'name');
                $pathBuilder->rebuildPaths('ipath', 'id');

                $this->addFlash('status', $this->__('Done! Rebuilt the category paths.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{cid}/{dr}/{mode}", requirements={"cid" = "^[1-9]\d*$", "dr" = "^[1-9]\d*$", "mode" = "edit|new"})
     * @Method("GET")
     * @Template
     *
     * Edits a category.
     *
     * @param Request $request
     * @param integer $cid
     * @param integer $dr
     * @param string $mode new|edit
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit or add the category
     */
    public function editAction(Request $request, $cid = 0, $dr = 1, $mode = 'new')
    {
        $editCat = '';

        $languages = ZLanguage::getInstalledLanguages();
        $categoryApi = $this->get('zikula_categories_module.api.category');

        // indicates that we're editing
        if ($mode == 'edit') {
            if (!$this->hasPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
                throw new AccessDeniedException();
            }

            if (!$cid) {
                $this->addFlash('error', $this->__('Error! Cannot determine valid \'cid\' for edit mode in \'ZikulaCategoriesModule_admin_edit\'.'));

                return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
            }

            $editCat = $categoryApi->getCategoryById($cid);
            if (null === $editCat) {
                throw new NotFoundHttpException($this->__('Category not found.'));
            }
        } else {
            // new category creation
            if (!$this->hasPermission('ZikulaCategoriesModule::category', '::', ACCESS_ADD)) {
                throw new AccessDeniedException();
            }

            $validationErrors = [];
            $validationErrorsInSession = $request->getSession()->get('validationErrors', '');
            if (is_array($validationErrorsInSession)) {
                $validationErrors = $validationErrorsInSession;
                $request->getSession()->remove('validationErrors');
            }

            // since we inherit the domain settings from the parent, we get
            // the inherited (and merged) object from session
            if (isset($_SESSION['newCategory']) && $_SESSION['newCategory']) {
                $editCat = $_SESSION['newCategory'];
                unset($_SESSION['newCategory']);
                $category = new CategoryEntity(); // need this for validation info
            } elseif (count($validationErrors) > 0) {
                // if we're back from validation get the posted data from session
                $newCatActionData = $request->getSession()->get('newCatActionData');
                $request->getSession()->del('newCatActionData');
                $editCat = new CategoryEntity();
                $editCat = $editCat->toArray();
                $editCat = array_merge($editCat, $newCatActionData);
                unset($editCat['path']);
                unset($editCat['ipath']);
                $category = new CategoryEntity(); // need this for validation info
            } else {
                // someone just pressed 'new' -> populate defaults
                $category = new CategoryEntity();
                $editCat['sort_value'] = '0';
            }
        }

        $allCats = $categoryApi->getSubCategories($dr, true, true, true, false, true);

        // now remove the categories which are below $editCat ...
        // you should not be able to set these as a parent category as it creates a circular hierarchy (see bug #4992)
        if (isset($editCat['ipath'])) {
            $cSlashEdit = mb_substr_count($editCat['ipath'], '/');
            foreach ($allCats as $k => $v) {
                $cSlashCat = mb_substr_count($v['ipath'], '/');
                if ($cSlashCat >= $cSlashEdit && false !== strpos($v['ipath'], $editCat['ipath'])) {
                    unset($allCats[$k]);
                }
            }
        }

        $selector = $this->get('zikula_categories_module.html_tree_helper')->getSelector_Categories($allCats, 'id',
            (isset($editCat['parent_id']) ? $editCat['parent_id'] : 0),
            'category[parent_id]',
            isset($defaultValue) ? $defaultValue : null,
            null,
            0,
            null,
            false, // do not submit on selector change
            false,
            true,
            1,
            false,
            'form-control');

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : [];

        $templateParameters = [
            'mode' => $mode,
            'category' => $editCat,
            'attributes' => $attributes,
            'languages' => $languages,
            'categorySelector' => $selector,
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];

        if ($mode == 'edit') {
            $hierarchyHelper = $this->get('zikula_categories_module.hierarchy_helper');
            $templateParameters['haveSubcategories'] = $hierarchyHelper->hasDirectSubcategories($cid);
            $templateParameters['haveLeafSubcategories'] = $hierarchyHelper->hasDirectSubcategories($cid, false, true);
        }

        return $templateParameters;
    }

    /**
     * @Route("/editregistry")
     * @Method("GET")
     *
     * Edits a category registry.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function editregistryAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_editregistry action is deprecated. please use zikulacategoriesmodule_registry_edit instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_registry_edit', $request->query->all());
    }

    /**
     * @Route("/deleteregistry")
     *
     * Deletes a category registry.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function deleteregistryAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_deleteregistry action is deprecated. please use zikulacategoriesmodule_registry_delete instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_registry_delete', $request->query->all());
    }

    /**
     * @Route("/new")
     * @Method("GET")
     *
     * Displays new category form.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     */
    public function newcatAction(Request $request)
    {
        $path = [
            '_controller' => 'ZikulaCategoriesModule:Admin:edit',
            'mode' => 'new'
        ];
        $subRequest = $request->duplicate($request->query->all(), $request->request->all(), $path);

        return $this->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @Route("/update")
     * @Method("POST")
     *
     * Updates a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function updateAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get data from post
        $data = $request->request->get('category', null);

        if (!isset($data['is_locked'])) {
            $data['is_locked'] = 0;
        }
        if (!isset($data['is_leaf'])) {
            $data['is_leaf'] = 0;
        }
        if (!isset($data['status'])) {
            $data['status'] = 'I';
        }

        $args = [];

        if ($request->request->get('category_copy', null)) {
            $args['op'] = 'copy';
            $args['cid'] = (int)$data['id'];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_op', $args);
        }

        if ($request->request->get('category_move', null)) {
            $args['op'] = 'move';
            $args['cid'] = (int)$data['id'];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_op', $args);
        }

        if ($request->request->get('category_delete', null)) {
            $args['op'] = 'delete';
            $args['cid'] = (int)$data['id'];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_op', $args);
        }

        if ($request->request->get('category_user_edit', null)) {
            $_SESSION['category_referer'] = $request->server->get('HTTP_REFERER');
            $args['dr'] = (int)$data['id'];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_edit', $args);
        }

        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        $valid = $processingHelper->validateCategoryData($data);
        if (!$valid) {
            $args = [
                'mode' => 'edit',
                'cid' => (int)$data['id']
            ];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_edit', $args);
        }

        // process name
        $data['name'] = $processingHelper->processCategoryName($data['name']);

        // process parent
        $data['parent'] = $processingHelper->processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = $processingHelper->processCategoryDisplayName($data['display_name'], $data['name']);

        // get existing category
        $entityManager = $this->get('doctrine')->getManager();
        $category = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $data['id']);

        $prevCategoryName = $category['name'];

        // save category
        $category->merge($data);
        $entityManager->flush();

        // process path and ipath
        $category['path'] = $processingHelper->processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = $processingHelper->processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        $processingHelper->processCategoryAttributes($category, $attrib_names, $attrib_values);

        $entityManager->flush();

        // since a name change will change the object path, we must rebuild it here
        if ($prevCategoryName != $category['name']) {
            $this->get('zikula_categories_module.path_builder_helper')->rebuildPaths('path', 'name', $category['id']);
        }

        $this->addFlash('status', $this->__f('Done! Saved the %s category.', ['%s' => $prevCategoryName]));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/newsave")
     * @Method("POST")
     *
     * Creates a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to add a category
     */
    public function savenewcatAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        // get data from post
        $data = $request->request->get('category', null);
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        $valid = $processingHelper->validateCategoryData($data);
        if (!$valid) {
            return $this->redirectToRoute('zikulacategoriesmodule_admin_newcat');
        }

        // process name
        $data['name'] = $processingHelper->processCategoryName($data['name']);

        // process parent
        $data['parent'] = $processingHelper->processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = $processingHelper->processCategoryDisplayName($data['display_name'], $data['name']);

        // save category
        $entityManager = $this->get('doctrine')->getManager();
        $category = new CategoryEntity();
        $category->merge($data);
        $entityManager->persist($category);
        $entityManager->flush();

        // process path and ipath
        $category['path'] = $processingHelper->processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = $processingHelper->processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        $processingHelper->processCategoryAttributes($category, $attrib_names, $attrib_values);

        $entityManager->flush();

        $this->addFlash('status', $this->__f('Done! Inserted the %s category.', ['%s' => $category['name']]));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
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
                'empty_data' => '/__SYSTEM__',
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
     * @Method("GET")
     *
     * Global module preferences.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function preferencesAction()
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        @trigger_error('The zikulacategoriesmodule_admin_preferences route is deprecated. please use zikulacategoriesmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_config_config');
    }
}
