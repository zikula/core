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

use ModUtil;
use SecurityUtil;
use UserUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\PermissionsModule\Api\PermissionApi;
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
        $groups = $this->getGroupsInfo();
        $permissionEntities = $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->getFilteredPermissions();
        $permissionLevels = $this->get('zikula_permissions_module.api.permission')->accessLevelNames();
        $permissions = [];

        foreach ($permissionEntities as $obj) {
            $id = $obj['gid'];
            $inserturl = $this->get('router')->generate('zikulapermissionsmodule_admin_listedit', [
                'action' => 'insert',
                'insseq' => $obj['sequence']
            ]);
            $editurl = $this->get('router')->generate('zikulapermissionsmodule_admin_listedit', [
                'chgpid' => $obj['pid'],
                'action' => 'modify'
            ]);
            $deleteurl = $this->get('router')->generate('zikulapermissionsmodule_admin_delete', [
                'pid' => $obj['pid'],
            ]);

            $permissions[] = [
                'sequence' => $obj['sequence'],
                // Realms not currently functional so hide the output - jgm
                //'realms'    => $realms[$realm],
                'group' => $groups[$id],
                'groupid' => $id,
                'component' => $obj['component'],
                'instance' => $obj['instance'],
                'accesslevel' => $permissionLevels[$obj['level']],
                'accesslevelid' => $obj['level'],
                'options' => [],
                'permid' => $obj['pid'],
                'inserturl' => $inserturl,
                'editurl' => $editurl,
                'deleteurl' => $deleteurl
            ];
            }

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
        $templateParameters['schemas'] = $this->get('zikula_permissions_module.helper.schema_helper')->getAllSchema();
        $templateParameters['enableFilter'] = (bool) $this->getVar('filter', 1);

        return $templateParameters;
    }

    /**
     * @Route("/edit/{action}/{chgpid}")
     * @Theme("admin")
     * @Template
     *
     * Edit / Create permissions in the main view.
     *
     * @return Response
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function listeditAction(Request $request, $action = 'add', $chgpid = null)
    {
        // Security check
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $insseq = $request->query->get('insseq', null);
        $permgrp = $request->get('permgrp', null);

        $templateParameters = [
            'permissionLevels' => SecurityUtil::accesslevelnames()
        ];

        // get all permissions
        $allperms = $this->get('doctrine.orm.entity_manager')->getRepository('ZikulaPermissionsModule:PermissionEntity')->findBy([], ['sequence' => 'ASC']);
        if (!$allperms && $action != 'add') {
            $this->addFlash('error', $this->__('Error! No permission rules of this kind were found. Please add some first.'));

            return $this->redirect($this->generateUrl('zikulapermissionsmodule_admin_listedit', ['action' => 'add']));
        }

        $viewperms = ($action == 'modify') ? $this->__('Modify permission rule') : $this->__('Create new permission rule');
        $templateParameters['title'] = $viewperms;

        $groupIds = $this->getGroupsInfo();
        $templateParameters['groups'] = $groupIds;

        if ($action == 'modify') {
            // Form-start
            $templateParameters['formurl'] = $this->get('router')->generate('zikulapermissionsmodule_admin_update');
            $templateParameters['permgrp'] = $permgrp;
            $templateParameters['chgpid'] = $chgpid;

            $templateParameters['insseq'] = $chgpid;
        } elseif ($action == 'insert' || $action == 'add') {
            $templateParameters['formurl'] = $this->get('router')->generate('zikulapermissionsmodule_admin_create');
            $templateParameters['permgrp'] = $permgrp;

            $templateParameters['insseq'] = ($action === 'insert') ? $insseq : -1;
        }

        $templateParameters['realm'] = 0;
        $templateParameters['action'] = $action;

        $accesslevels = SecurityUtil::accesslevelnames();
        $permissions = [];

        foreach ($allperms as $obj) {
            $id = $obj['gid']; //get's uid or gid accordingly

            $permissions[] = [
                'pid' => $obj['pid'],
                'group' => $groupIds[$id],
                'component' => $obj['component'],
                'instance' => $obj['instance'],
                'accesslevel' => $accesslevels[$obj['level']],
                'level' => $obj['level'],
                'sequence' => $obj['sequence']
            ];
            if ($action == 'modify' && $obj['pid'] == $chgpid) {
                $templateParameters['selectedId'] = $id;
            }
        }
        $templateParameters['permissions'] = $permissions;

        $tokenHandler = $this->get('zikula_core.common.csrf_token_handler');
        $templateParameters['csrfToken'] = $tokenHandler->generate(true);

        return $templateParameters;
    }

    /**
     * @Route("/update")
     * @Method("POST")
     *
     * Update.
     *
     * @param int 'pid' permissions id
     * @param int 'id' group or user id
     * @param int 'realm' realm to which the permission belongs
     * @param string 'component' component string
     * @param string 'instance' instance string
     * @param int 'level' permission level
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function updateAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters
        $pid = $request->request->get('pid', null);
        $seq = $request->request->get('seq', null);
        $oldseq = $request->request->get('oldseq', null);
        $realm = $request->request->get('realm', null);
        $id = $request->request->get('id', null);
        $component = $request->request->get('component', null);
        $instance = $request->request->get('instance', null);
        $level = $request->request->get('level', null);

        // Since we're using TextAreas, make sure no carriage-returns etc get through unnoticed.
        $warnmsg = '';
        if (preg_match("/[\n\r\t\x0B]/", $component)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
            $warnmsg .= $this->__('[Illegal input in component!]');
        }
        if (preg_match("/[\n\r\t\x0B]/", $instance)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
            $warnmsg .= $this->__('[Illegal input in instance!]');
        }

        // Pass to API
        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'update', [
            'pid' => $pid,
            'seq' => $seq,
            'oldseq' => $oldseq,
            'realm' => $realm,
            'id' => $id,
            'component' => $component,
            'instance' => $instance,
            'level' => $level
        ])) {
            // Success
            if ($warnmsg == '') {
                $this->addFlash('status', $this->__('Done! Saved permission rule.'));
            } else {
                $this->addFlash('error', $warnmsg);
            }
        }

        return $this->redirectToRoute('zikulapermissionsmodule_admin_view');
    }

    /**
     * @Route("/create")
     * @Method("POST")
     *
     * Create a new permission.
     *
     * @param int 'id' group
     * @param int 'realm' realm to which the permission belongs
     * @param string 'component' component string
     * @param string 'instance' instance string
     * @param int 'level' permission level
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function createAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters
        $realm = $request->request->get('realm', null);
        $id = $request->request->get('id', null);
        $component = $request->request->get('component', null);
        $instance = $request->request->get('instance', null);
        $level = $request->request->get('level', null);
        $insseq = $request->request->get('insseq', null);

        // Since we're using TextAreas, make sure no carriage-returns etc get through unnoticed.
        $warnmsg = '';
        if (preg_match("/[\n\r\t\x0B]/", $component)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
            $warnmsg .= $this->__('[Illegal input in component!]');
        }
        if (preg_match("/[\n\r\t\x0B]/", $instance)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
            $warnmsg .= $this->__('[Illegal input in instance!]');
        }

        // Pass to API
        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'create', [
            'realm' => $realm,
            'id' => $id,
            'component' => $component,
            'instance' => $instance,
            'level' => $level,
            'insseq' => $insseq
        ])) {
            // Success
            if ($warnmsg == '') {
                $this->addFlash('status', $this->__('Done! Created permission rule.'));
            } else {
                $this->addFlash('error', $warnmsg);
            }
        }

        return $this->redirectToRoute('zikulapermissionsmodule_admin_view');
    }

    /**
     * @Route("/delete/{pid}/{permgrp}", requirements={"pid"="\d+", "permgrp"="\d+"})
     * @Theme("admin")
     * @Template
     *
     * Delete a permission.
     *
     * @param Request $request
     * @param int     $pid     permissions id
     * @param int     $permgrp permissions group filter
     *
     * @return Response
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function deleteAction(Request $request, $pid, $permgrp = null)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $formValues = [
            'pid' => $pid,
            // Permission type and group
            'permgrp' => $permgrp
        ];

        $form = $this->createForm('Zikula\PermissionsModule\Form\Type\DeletePermissionType', $formValues, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $formData = $form->getData();

                try {
                    // delete permission rule
                    $delete = ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'delete', ['pid' => $formData['pid']]);
                    if ($delete) {
                        $this->addFlash('status', $this->__('Done! Permission rule deleted.'));
                    } else {
                        $this->addFlash('error', $this->__('Error! A problem occurred while attempting to delete the permission rule. The rule has not been deleted.'));
                    }
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirect($this->generateUrl('zikulapermissionsmodule_admin_view', ['filter-group' => $permgrp]));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * getGroupsInfo - get groups information.
     *
     * @todo remove calls to this function in favour of calls to the groups module
     *
     * @return array groups array
     */
    private function getGroupsInfo()
    {
        $groups = [];
        $groups[PermissionApi::ALL_GROUPS] = $this->__('All groups');
        $groups[PermissionApi::UNREGISTERED_USER_GROUP] = $this->__('Unregistered');

        $entities = $this->container->get('doctrine')->getRepository('ZikulaGroupsModule:GroupEntity')->findAll();
        foreach ($entities as $group) {
            $groups[$group->getGid()] = $group->getName();
        }

        return $groups;
    }

    /**
     * @Route("/instance-info")
     * @Theme("admin")
     * @Template
     *
     * howInstanceInformation.
     *
     * Show instance information gathered from blocks and modules.
     *
     * @return boolean
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function viewinstanceinfoAction()
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return [
            'schemas' => ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'getallschemas')
        ];
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
