<?php
/**
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
use Symfony\Component\Routing\RouterInterface;
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
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function viewAction(Request $request)
    {
        // Security check
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need.
        $filterGroup = $request->get('filter-group', -1);
        $filterComponent = $request->get('filter-component', -1);
        $testUser = $request->request->get('test_user', null);
        $testComponent = $request->request->get('test_component', null);
        $testInstance = $request->request->get('test_instance', null);
        $testLevel = $request->request->get('test_level', null);

        $testResult = '';
        if (!empty($testUser) && !empty($testComponent) && !empty($testInstance)) {
            // we have everything we need for an effective permission check
            $testuid = UserUtil::getIdFromName($testUser);
            if ($testuid != false) {
                if ($this->hasPermission($testComponent, $testInstance, $testLevel, $testuid)) {
                    $testResult = '<span id="permissiontestinfogreen">' . $this->__('permission granted.') . '</span>';
                } else {
                    $testResult = '<span id="permissiontestinfored">' . $this->__('permission not granted.') . '</span>';
                }
            } else {
                $testResult = '<span id="permissiontestinfored">' . $this->__('unknown user.') . '</span>';
            }
        }

        $templateParameters = [
            'testUser' => $testUser,
            'testComponent' => $testComponent,
            'testInstance' => $testInstance,
            'testLevel' => $testLevel,
            'testResult' => $testResult
        ];

        $groupIds = $this->getGroupsInfo();

        $tokenHandler = $this->get('zikula_core.common.csrf_token_handler');
        $csrfToken = $tokenHandler->generate(true);

        $variableApi = $this->get('zikula_extensions_module.api.variable');

        // form the first part of the query
        $qb = $this->get('doctrine.orm.entity_manager')->createQueryBuilder()
            ->select('p')
            ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
            ->orderBy('p.sequence', 'ASC');

        $enableFilter = $variableApi->get('ZikulaPermissionsModule', 'filter', 1);
        if ($enableFilter == 1) {
            if ($filterGroup != -1) {
                $qb->where('(p.gid = :gid)')
                    ->setParameter('gid', $filterGroup);
            }
            if ($filterComponent != -1) {
                $qb->andWhere("(p.component LIKE :permgrpparts)")
                    ->setParameter('permgrpparts', $filterComponent.'%');
            }
            $templateParameters['permgrps'] = $groupIds;
            $templateParameters['filterGroup'] = $filterGroup;
            $templateParameters['filterComponent'] = $filterComponent;
            $templateParameters['enableFilter'] = true;
            $templateParameters['csrfToken'] = $csrfToken;
        } else {
            $templateParameters['enableFilter'] = false;
            $templateParameters['permgrp'] = SecurityUtil::PERMS_ALL;
        }

        if ($filterGroup == -1) {
            $filterGroup = null;
        }

        $query = $qb->getQuery();
        $objArray = $query->getResult();
        $numrows = count($objArray);

        $permissions = [];

        if ($numrows > 0) {
            $accesslevels = SecurityUtil::accesslevelnames();
            $rownum = 1;

            foreach ($objArray as $obj) {
                $id = $obj['gid'];
                $up = [
                    'url' => $this->get('router')->generate('zikulapermissionsmodule_admin_inc', [
                        'pid' => $obj['pid'],
                        'permgrp' => $filterGroup,
                        'csrftoken' => $csrfToken
                    ]),
                    'title' => $this->__('Up')
                ];
                $down = [
                    'url' => $this->get('router')->generate('zikulapermissionsmodule_admin_dec', [
                        'pid' => $obj['pid'],
                        'permgrp' => $filterGroup,
                        'csrftoken' => $csrfToken
                    ]),
                    'title' => $this->__('Down')
                ];
                switch ($rownum) {
                    case 1:
                        $arrows = ['up' => 0, 'down' => 1];
                        break;
                    case $numrows:
                        $arrows = ['up' => 1, 'down' => 0];
                        break;
                    default:
                        $arrows = ['up' => 1, 'down' => 1];
                        break;
                }
                $rownum++;

                $options = [];
                $inserturl = $this->get('router')->generate('zikulapermissionsmodule_admin_listedit', [
                    'permgrp' => $filterGroup,
                    'action' => 'insert',
                    'insseq' => $obj['sequence']
                ]);
                $editurl = $this->get('router')->generate('zikulapermissionsmodule_admin_listedit', [
                    'chgpid' => $obj['pid'],
                    'permgrp' => $filterGroup,
                    'action' => 'modify'
                ]);
                $deleteurl = $this->get('router')->generate('zikulapermissionsmodule_admin_delete', [
                    'pid' => $obj['pid'],
                    'permgrp' => $filterGroup
                ]);

                $permissions[] = [
                    'sequence' => $obj['sequence'],
                    'arrows' => $arrows,
                    // Realms not currently functional so hide the output - jgm
                    //'realms'    => $realms[$realm],
                    'group' => $groupIds[$id],
                    'groupid' => $id,
                    'component' => $obj['component'],
                    'instance' => $obj['instance'],
                    'accesslevel' => $accesslevels[$obj['level']],
                    'accesslevelid' => $obj['level'],
                    'options' => $options,
                    'up' => $up,
                    'down' => $down,
                    'permid' => $obj['pid'],
                    'inserturl' => $inserturl,
                    'editurl' => $editurl,
                    'deleteurl' => $deleteurl
                ];
            }
        }

        $components = [
            -1 => $this->__('All components')
        ];
        // read all perms to extract components
        $allPerms = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ZikulaPermissionsModule:PermissionEntity')
            ->findBy([], ['sequence' => 'ASC']);
        foreach ($allPerms as $singlePerm) {
            // extract components, we keep everything up to the first colon
            $compparts = explode(':', $singlePerm['component']);
            $components[$compparts[0]] = $compparts[0];
        }

        $templateParameters['groups'] = $this->getGroupsInfo();
        $templateParameters['permissions'] = $permissions;
        $templateParameters['components'] = $components;

        $lockadmin = ($variableApi->get('ZikulaPermissionsModule', 'lockadmin', 1)) ? 1 : 0;
        $templateParameters['lockadmin'] = $lockadmin;
        $templateParameters['adminId'] = $variableApi->get('ZikulaPermissionsModule', 'adminid', 1);

        $templateParameters['permissionLevels'] = SecurityUtil::accesslevelnames();
        $templateParameters['schemas'] = ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'getallschemas');

        return $templateParameters;
    }

    /**
     * @Route("/inc/{pid}/{permgrp}", requirements={"pid"="\d+","permgrp"="\d+"})
     *
     * Increment a permission.
     *
     * @param int 'pid' permissions id
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function incAction(Request $request, $pid, $permgrp = null)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (empty($permgrp)) {
            // Make sure we return something sensible.
            $permgrp = SecurityUtil::PERMS_ALL;
        }

        // Pass to API
        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'inc', ['pid' => $pid, 'permgrp' => $permgrp])) {
            // Success
            $this->addFlash('status', $this->__('Done! Incremented permission rule.'));
        }

        return $this->redirect($this->generateUrl('zikulapermissionsmodule_admin_view', ['filter-group' => $permgrp]));
    }

    /**
     * @Route("/dec/{pid}/{permgrp}", requirements={"pid"="\d+","permgrp"="\d+"})
     *
     * Decrement a permission.
     *
     * @param int 'pid' permissions id.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function decAction(Request $request, $pid, $permgrp = null)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!isset($permgrp) || $permgrp == '') {
            // Make sure we return something sensible.
            $permgrp = SecurityUtil::PERMS_ALL;
        }

        // Pass to API
        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'dec', ['pid' => $pid, 'permgrp' => $permgrp])) {
            // Success
            $this->addFlash('status', $this->__('Done! Decremented permission rule.'));
        }

        return $this->redirect($this->generateUrl('zikulapermissionsmodule_admin_view', ['filter-group' => $permgrp]));
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
     * @param int 'pid' permissions id.
     * @param int 'id' group or user id.
     * @param int 'realm' realm to which the permission belongs.
     * @param string 'component' component string.
     * @param string 'instance' instance string.
     * @param int 'level' permission level.
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
     * @param int 'id' group.
     * @param int 'realm' realm to which the permission belongs.
     * @param string 'component' component string.
     * @param string 'instance' instance string.
     * @param int 'level' permission level.
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
     * @param int 'pid' permissions id.
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
                    // delete category
                    $delete = ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'delete', ['pid' => $formData['pid']]);
                    if ($delete) {
                        $this->addFlash('status', $this->__('Done! Permission rule deleted.'));
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
        $groups[SecurityUtil::PERMS_ALL] = $this->__('All groups');
        $groups[SecurityUtil::PERMS_UNREGISTERED] = $this->__('Unregistered');

        $objArray = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');
        foreach ($objArray as $group) {
            $groups[$group['gid']] = $group['name'];
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
