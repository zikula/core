<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Zikula_View;
use FormUtil;
use SecurityUtil;
use CategoryUtil;
use ZLanguage;
use StringUtil;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the categories module
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * Route not needed here because method is legacy-only
     *
     * main admin function
     *
     * @deprecated since 1.4.0 see indexAction()
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        // Security check will be done in view()
        return new RedirectResponse($this->get('router')->generate('zikulacategoriesmodule_admin_view', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("")
     *
     * main admin function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return new RedirectResponse($this->get('router')->generate('zikulacategoriesmodule_admin_view', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/view")
     *
     * view categories
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit the category
     */
    public function viewAction(Request $request)
    {
        $root_id = $request->get('dr', 1);

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "ID::$root_id", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $cats = CategoryUtil::getSubCategories($root_id, true, true, true, true, true);
        $menuTxt = CategoryUtil::getCategoryTreeJqueryJS($cats, true, true);

        $this->view->assign('menuTxt', $menuTxt);

        return $this->response($this->view->fetch('Admin/view.tpl'));
    }

    /**
     * @Route("/config")
     * @Method("GET")
     *
     * display configure module page
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module configuration
     */
    public function configAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return $this->response($this->view->fetch('Admin/config.tpl'));
    }

    /**
     * @Route("/edit/{cid}/{dr}/{mode}", requirements={"cid" = "^[1-9]\d*$", "dr" = "^[1-9]\d*$", "mode" = "edit|new"})
     * @Method("GET")
     *
     * edit category
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
    public function editAction(Request $request, $cid = 0, $dr = 1, $mode = "new")
    {
        $editCat = '';

        $languages = ZLanguage::getInstalledLanguages();

        // indicates that we're editing
        if ($mode == 'edit') {
            if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
                throw new AccessDeniedException();
            }

            if (!$cid) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Cannot determine valid \'cid\' for edit mode in \'ZikulaCategoriesModule_admin_edit\'.'));

                return new RedirectResponse($this->get('router')->generate('zikulacategoriesmodule_admin_view', [], RouterInterface::ABSOLUTE_URL));
            }

            $editCat = CategoryUtil::getCategoryByID($cid);
            if (!$editCat) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Sorry! No such item found.'));

                return new RedirectResponse($this->get('router')->generate('zikulacategoriesmodule_admin_view', [], RouterInterface::ABSOLUTE_URL));
            }
        } else {
            // new category creation
            if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_ADD)) {
                throw new AccessDeniedException();
            }

            // since we inherit the domain settings from the parent, we get
            // the inherited (and merged) object from session
            if (isset($_SESSION['newCategory']) && $_SESSION['newCategory']) {
                $editCat = $_SESSION['newCategory'];
                unset($_SESSION['newCategory']);
                $category = new CategoryEntity(); // need this for validation info
            } elseif (FormUtil::getValidationErrors()) {
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

        $allCats = CategoryUtil::getSubCategories($dr, true, true, true, false, true);

        // now remove the categories which are below $editCat ...
        // you should not be able to set these as a parent category as it creates a circular hierarchy (see bug #4992)
        if (isset($editCat['ipath'])) {
            $cSlashEdit = StringUtil::countInstances($editCat['ipath'], '/');
            foreach ($allCats as $k => $v) {
                $cSlashCat = StringUtil::countInstances($v['ipath'], '/');
                if ($cSlashCat >= $cSlashEdit && strpos($v['ipath'], $editCat['ipath']) !== false) {
                    unset($allCats[$k]);
                }
            }
        }

        $selector = CategoryUtil::getSelector_Categories($allCats,
                                                         'id',
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

        $this->view->assign('mode', $mode)
                   ->assign('category', $editCat)
                   ->assign('attributes', $attributes)
                   ->assign('languages', $languages)
                   ->assign('categorySelector', $selector);

        if ($mode == 'edit') {
            $this->view->assign('haveSubcategories', CategoryUtil::haveDirectSubcategories($cid))
                       ->assign('haveLeafSubcategories', CategoryUtil::haveDirectSubcategories($cid, false, true));
        }

        return $this->response($this->view->fetch('Admin/edit.tpl'));
    }

    /**
     * @Route("/editregistry")
     * @Method("GET")
     *
     * edit category registry
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function editregistryAction(Request $request)
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $root_id = $request->get('dr', 1);
        $id = $request->get('id', 0);

        $obj = new CategoryRegistryEntity();

        $category_registry = $request->query->get('category_registry', null);
        if ($category_registry) {
            $obj->merge($category_registry);
            $obj = $obj->toArray();
        }

        $registries = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->findBy([], ['modname' => 'ASC', 'property' => 'ASC']);
        $modules = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionEntity')->findBy(['state' => 3], ['displayname' => 'ASC']);
        $moduleOptions = [];
        foreach ($modules as $module) {
            $bundle = \ModUtil::getModule($module['name']);
            if ((null !== $bundle) && !class_exists($bundle->getVersionClass())) {
                // this check just confirming a Core-2.0 spec bundle - remove in 2.0.0
                // then instead of getting MetaData, could just do ModUtil::getCapabilitiesOf($module['name'])
                $capabilities = $bundle->getMetaData()->getCapabilities();
                if (!isset($capabilities['categorizable'])) {
                    continue; // skip this module if not categorizable
                }
            }
            $moduleOptions[$module['name']] = $module['displayname'];
        }

        $this->view->assign('objectArray', $registries)
                   ->assign('moduleOptions', $moduleOptions)
                   ->assign('newobj', $obj)
                   ->assign('root_id', $root_id)
                   ->assign('id', $id);

        return $this->response($this->view->fetch('Admin/registry_edit.tpl'));
    }

    /**
     * @Route("/deleteregistry")
     *
     * delete category registry
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function deleteregistryAction(Request $request)
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $id = $request->get('id', 0);

        $obj = $this->entityManager->find('ZikulaCategoriesModule:CategoryRegistryEntity', $id);
        $data = $obj->toArray();

        $this->view->assign('data', $data)
                   ->assign('id', $id);

        return $this->response($this->view->fetch('Admin/registry_delete.tpl'));
    }

    /**
     * @Route("/new")
     * @Method("GET")
     *
     * display new category form
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
     * generic function to handle copy, delete and move operations
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have access to delete the category
     */
    public function opAction(Request $request)
    {
        $cid = $request->get('cid', 1);
        $root_id = $request->get('dr', 1);
        $op = $request->get('op', 'NOOP');

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "ID::$cid", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $category = CategoryUtil::getCategoryByID($cid);
        $subCats = CategoryUtil::getSubCategories($cid, false, false);
        $allCats = CategoryUtil::getSubCategories($root_id, true, true, true, false, true, $cid);
        $selector = CategoryUtil::getSelector_Categories($allCats);

        $this->view->assign('category', $category)
                   ->assign('numSubcats', count($subCats))
                   ->assign('categorySelector', $selector);

        return $this->response($this->view->fetch("Admin/{$op}.tpl"));
    }

    /**
     * @Route("/preferences")
     * @Method("GET")
     *
     * global module preferences
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function preferencesAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::preferences', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->view->assign('userrootcat', $this->getVar('userrootcat', '/__SYSTEM__'))
                   ->assign('allowusercatedit', $this->getVar('allowusercatedit', 0))
                   ->assign('autocreateusercat', $this->getVar('autocreateusercat', 0))
                   ->assign('autocreateuserdefaultcat', $this->getVar('autocreateuserdefaultcat', 0))
                   ->assign('userdefaultcatname', $this->getVar('userdefaultcatname', 0))
                   ->assign('permissionsall', $this->getVar('permissionsall', 0));

        return $this->response($this->view->fetch('Admin/preferences.tpl'));
    }
}
