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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
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
        @trigger_error('The zikulcategoriesmodule_admin_main action is deprecated. please use zikulacategoriesmodule_admin_view instead.', E_USER_DEPRECATED);

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
        @trigger_error('The zikulcategoriesmodule_admin_index route is deprecated. please use zikulacategoriesmodule_admin_view instead.', E_USER_DEPRECATED);

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
     * @Method("GET")
     * @Template
     *
     * Displays page for rebuilding pathes.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have administrative permission for this module
     */
    public function rebuildAction()
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return [
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
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
            if (!$editCat) {
                $this->addFlash('error', $this->__('Sorry! No such item found.'));

                return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
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
                $newCatActionData = \SessionUtil::getVar('newCatActionData');
                \SessionUtil::delVar('newCatActionData');
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

        $selector = $this->get('zikula_categories_module.html_tree_helper')->getSelector($allCats, 'id',
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
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $root_id = $request->query->get('dr', 1);
        $id = $request->query->get('id', 0);

        $obj = new CategoryRegistryEntity();

        $category_registry = $request->query->get('category_registry', null);
        if ($category_registry) {
            $obj->merge($category_registry);
            $obj = $obj->toArray();
        }

        $entityManager = $this->get('doctrine')->getManager();

        $registries = $entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')
            ->findBy([], ['modname' => 'ASC', 'property' => 'ASC']);
        $modules = $entityManager->getRepository('ZikulaExtensionsModule:ExtensionEntity')
            ->findBy(['state' => 3], ['displayname' => 'ASC']);

        $moduleOptions = [];
        foreach ($modules as $module) {
            if ($this->get("zikula_extensions_module.api.capability")->isCapable($module['name'], 'categorizable')) {
                $moduleOptions[$module['name']] = $module['displayname'];
            }
        }

        $templateParameters = [
            'objectArray' => $registries,
            'moduleOptions' => $moduleOptions,
            'newobj' => $obj,
            'root_id' => $root_id,
            'id' => $id,
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];

        return $this->render('@ZikulaCategoriesModule/Admin/registry_edit.html.twig', $templateParameters);
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
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $id = $request->query->get('id', 0);

        $entityManager = $this->get('doctrine')->getManager();
        $obj = $entityManager->find('ZikulaCategoriesModule:CategoryRegistryEntity', $id);

        $templateParameters = [
            'data' => $obj->toArray(),
            'id' => $id,
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];

        return $this->render('@ZikulaCategoriesModule/Admin/registry_delete.html.twig', $templateParameters);
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
        $cid = $request->query->get('cid', 1);
        $root_id = $request->query->get('dr', 1);
        $op = $request->query->get('op', 'NOOP');

        if (!$this->hasPermission('ZikulaCategoriesModule::category', "ID::$cid", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $categoryApi = $this->get('zikula_categories_module.api.category');

        $category = $categoryApi->getCategoryById($cid);
        $subCats = $categoryApi->getSubCategories($cid, false, false);
        $allCats = $categoryApi->getSubCategories($root_id, true, true, true, false, true, $cid);
        $selector = $this->get('zikula_categories_module.html_tree_helper')->getSelector($allCats);
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        if ($op == 'delete' || $op == 'move') {
            // prevent deletion or move if category is already used
            if (!$processingHelper->mayCategoryBeDeletedOrMoved($category)) {
                if ($op == 'delete') {
                    $this->addFlash('error', $this->__f('Error! Category %s can not be deleted, because it is already used.', ['%s' => $category['name']]));
                } elseif ($op == 'move') {
                    $this->addFlash('error', $this->__f('Error! Category %s can not be moved, because it is already used.', ['%s' => $category['name']]));
                }

                return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
            }
        }

        $templateParameters = [
            'category' => $category,
            'numSubcats' => count($subCats),
            'categorySelector' => $selector,
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];

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

        @trigger_error('The zikulcategoriesmodule_admin_preferences route is deprecated. please use zikulacategoriesmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_config_config');
    }
}
