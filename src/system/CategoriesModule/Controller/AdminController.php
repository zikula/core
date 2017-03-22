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
        @trigger_error('The zikulacategoriesmodule_admin_edit action is deprecated. please use Javascript UI instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_category_list');
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
        @trigger_error('The zikulacategoriesmodule_admin_new action is deprecated. please use Javascript UI instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_category_list');
    }

    /**
     * @Route("/update")
     */
    public function updateAction(Request $request)
    {
        @trigger_error('The zikulacategoriesmodule_admin_update action is deprecated. please use Javascript UI instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_category_list');
    }

    /**
     * @Route("/op")
     */
    public function opAction(Request $request)
    {
        throw new \RuntimeException($this->__('This route no longer functions.'));
    }

    /**
     * @Route("/preferences")
     */
    public function preferencesAction()
    {
        @trigger_error('The zikulacategoriesmodule_admin_preferences route is removed.', E_USER_DEPRECATED);

        return $this->redirectToRoute('home');
    }
}
