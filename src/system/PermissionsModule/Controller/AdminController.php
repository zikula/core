<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class AdminController extends AbstractController
{
    /**
     * @Route("/")
     *
     * Main administration function.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulapermissionsmodule_admin_index route is deprecated. please use zikulapermissionsmodule_admin_view instead.', E_USER_DEPRECATED);

        // Security check will be done in view()
        return $this->redirectToRoute('zikulapermissionsmodule_admin_view');
    }

    /**
     * @Route("/view")
     * @Theme("admin")
     * @Template
     *
     * view permissions
     *
     * @return array
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function viewAction()
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $groups = $this->get('zikula_groups_module.group_repository')->getGroupNamesById();
        $permissions = $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->getFilteredPermissions();
        $permissionLevels = $this->get('zikula_permissions_module.api.permission')->accessLevelNames();
        $components = $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->getAllComponents();
        $components = [$this->__('All components') => '-1'] + $components;
        $filterForm = $this->createForm('Zikula\PermissionsModule\Form\Type\FilterListType', [], [
            'groupChoices' => $groups,
            'componentChoices' => $components,
            'translator' => $this->getTranslator()
        ]);
        $templateParameters['filterForm'] = $filterForm->createView();
        $permissionCheckForm = $this->createForm('Zikula\PermissionsModule\Form\Type\PermissionCheckType', [], [
            'translator' => $this->getTranslator(),
            'permissionLevels' => $permissionLevels
        ]);
        $templateParameters['permissionCheckForm'] = $permissionCheckForm->createView();
        $templateParameters['permissionLevels'] = $permissionLevels;
        $templateParameters['permissions'] = $permissions;
        $templateParameters['groups'] = $groups;
        $templateParameters['lockadmin'] = $this->getVar('lockadmin', 1) ? 1 : 0;
        $templateParameters['adminId'] = $this->getVar('adminid', 1);
        $templateParameters['schema'] = $this->get('zikula_permissions_module.helper.schema_helper')->getAllSchema();
        $templateParameters['enableFilter'] = (bool) $this->getVar('filter', 1);

        return $templateParameters;
    }

    /**
     * @Route("/config")
     * @Method("GET")
     *
     * Set configuration parameters of the module
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function modifyconfigAction()
    {
        @trigger_error('The zikulapermissionsmodule_admin_modifyconfig route is deprecated. please use zikulapermissionsmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulapermissionsmodule_config_config');
    }
}
