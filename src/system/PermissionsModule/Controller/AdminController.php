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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Zikula_View;
use ModUtil;
use SecurityUtil;
use UserUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * Main administration function.
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        return $this->indexAction();
    }

    /**
     * @Route("/")
     *
     * Main administration function.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return new RedirectResponse($this->get('router')->generate('zikulapermissionsmodule_admin_view', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/view")
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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need.
        $filterGroup = $request->get('filter-group', -1);
        $filterComponent = $request->get('filter-component', -1);
        $testuser = $request->request->get('test_user', null);
        $testcomponent = $request->request->get('test_component', null);
        $testinstance = $request->request->get('test_instance', null);
        $testlevel = $request->request->get('test_level', null);

        $testresult = '';
        if (!empty($testuser) && !empty($testcomponent) && !empty($testinstance)) {
            // we have everything we need for an effective permission check
            $testuid = UserUtil::getIdFromName($testuser);
            if ($testuid != false) {
                if (SecurityUtil::checkPermission($testcomponent, $testinstance, $testlevel, $testuid)) {
                    $testresult = '<span id="permissiontestinfogreen">' . $this->__('permission granted.') . '</span>';
                } else {
                    $testresult = '<span id="permissiontestinfored">' . $this->__('permission not granted.') . '</span>';
                }
            } else {
                $testresult = '<span id="permissiontestinfored">' . $this->__('unknown user.') . '</span>';
            }
        }

        $this->view->assign('testuser', $testuser)
                   ->assign('testcomponent', $testcomponent)
                   ->assign('testinstance', $testinstance)
                   ->assign('testlevel', $testlevel)
                   ->assign('testresult', $testresult);

        $ids = $this->getGroupsInfo();

        // form the first part of the qbery
        $qb = $this->entityManager->createQueryBuilder()
                                  ->select('p')
                                  ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
                                  ->orderBy('p.sequence', 'ASC');

        $enableFilter = $this->getVar('filter', 1);
        if ($enableFilter == 1) {
            if ($filterGroup != -1) {
                $qb->where('(p.gid = :gid)')
                       ->setParameter('gid', $filterGroup);
            }
            if ($filterComponent != -1) {
                $qb->andWhere("(p.component LIKE :permgrpparts)")
                       ->setParameter('permgrpparts', $filterComponent.'%');
            }
            $this->view->assign('permgrps', $ids);
            $this->view->assign('filterGroup', $filterGroup);
            $this->view->assign('filterComponent', $filterComponent);
            $this->view->assign('enablefilter', true);
        } else {
            $this->view->assign('enablefilter', false);
            $this->view->assign('permgrp', SecurityUtil::PERMS_ALL);
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
            $csrftoken = SecurityUtil::generateCsrfToken($this->getContainer(), true);
            $rownum = 1;

            foreach ($objArray as $obj) {
                $id = $obj['gid'];
                $up = [
                    'url' => $this->get('router')->generate('zikulapermissionsmodule_admin_inc', [
                        'pid' => $obj['pid'],
                        'permgrp' => $filterGroup,
                        'csrftoken' => $csrftoken
                    ]),
                    'title' => $this->__('Up')
                ];
                $down = [
                    'url' => $this->get('router')->generate('zikulapermissionsmodule_admin_dec', [
                        'pid' => $obj['pid'],
                        'permgrp' => $filterGroup,
                        'csrftoken' => $csrftoken
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
                    'group' => $ids[$id],
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

        $components = [-1 => $this->__('All components')];
        // read all perms to extract components
        $allperms = $this->entityManager->getRepository('ZikulaPermissionsModule:PermissionEntity')->findBy([], ['sequence' => 'ASC']);
        foreach ($allperms as $singlePerm) {
            // extract components, we keep everything up to the first colon
            $compparts = explode(':', $singlePerm['component']);
            $components[$compparts[0]] = $compparts[0];
        }

        $this->view->assign('groups', $this->getGroupsInfo());
        $this->view->assign('permissions', $permissions);
        $this->view->assign('components', $components);

        $lockadmin = ($this->getVar('lockadmin')) ? 1 : 0;
        $this->view->assign('lockadmin', $lockadmin);
        $this->view->assign('adminid', $this->getVar('adminid'));

        // Assign the permission levels
        $this->view->assign('permissionlevels', SecurityUtil::accesslevelnames());

        $this->view->assign('schemas', ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'getallschemas'));

        return $this->response($this->view->fetch('Admin/view.tpl'));
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
        $this->checkCsrfToken($request->query->get('csrftoken', null));

        // MMaes,2003-06-23: Added sec.check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (empty($permgrp)) {
            // For group-permissions, make sure we return something sensible.
            // Doesn't matter if we're looking at user-permissions...
            $permgrp = SecurityUtil::PERMS_ALL;
        }

        // Pass to API
        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'inc', ['pid' => $pid, 'permgrp' => $permgrp])) {
            // Success
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Incremented permission rule.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulapermissionsmodule_admin_view',
            ['filter-group' => $permgrp], RouterInterface::ABSOLUTE_URL)
        );
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
        $this->checkCsrfToken($request->query->get('csrftoken', null));

        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!isset($permgrp) || $permgrp == '') {
            // For group-permissions, make sure we return something sensible.
            // This doesn't matter if we're looking at user-permissions...
            $permgrp = SecurityUtil::PERMS_ALL;
        }

        // Pass to API
        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'dec', ['pid' => $pid, 'permgrp' => $permgrp])) {
            // Success
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Decremented permission rule.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulapermissionsmodule_admin_view',
                ['filter-group' => $permgrp], RouterInterface::ABSOLUTE_URL)
        );
    }

    /**
     * @Route("/edit/{action}/{chgpid}")
     *
     * Edit / Create permissions in the mainview.
     *
     * @return Response
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function listeditAction(Request $request, $action = 'add', $chgpid = null)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $insseq = $request->query->get('insseq', null);
        $permgrp = $request->get('permgrp', null);

        // Assign the permission levels
        $this->view->assign('permissionlevels', SecurityUtil::accesslevelnames());

        // get all permissions
        $allperms = $this->entityManager->getRepository('ZikulaPermissionsModule:PermissionEntity')->findBy([], ['sequence' => 'ASC']);
        if (!$allperms && $action != 'add') {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! No permission rules of this kind were found. Please add some first.'));

            return new RedirectResponse($this->get('router')->generate('zikulapermissionsmodule_admin_listedit',
                ['action' => 'add'], RouterInterface::ABSOLUTE_URL));
        }

        $viewperms = ($action == 'modify') ? $this->__('Modify permission rule') : $this->__('Create new permission rule');
        $this->view->assign('title', $viewperms);

        $mlpermtype = $this->__('Group');
        $this->view->assign('mlpermtype', $mlpermtype);

        $ids = $this->getGroupsInfo();
        $this->view->assign('idvalues', $ids);

        if ($action == 'modify') {
            // Form-start
            $this->view->assign('formurl', $this->get('router')->generate('zikulapermissionsmodule_admin_update'))
                       ->assign('permgrp', $permgrp)
                       ->assign('chgpid', $chgpid);

            // Realms hard-code4d - jgm
            $this->view->assign('realm', 0)
                       ->assign('insseq', $chgpid)
                       ->assign('submit', $this->__('Edit permission rule'));
        } elseif ($action == 'insert' || $action == 'add') {
            $this->view->assign('formurl', $this->get('router')->generate('zikulapermissionsmodule_admin_create'))
                       ->assign('permgrp', $permgrp)
                       ->assign('insseq', $action === 'insert' ? $insseq : -1);

            // Realms hard-coded - jgm
            $this->view->assign('realm', 0)
                       ->assign('submit', $this->__('Create new permission rule'));
        }

        $this->view->assign('action', $action);

        $accesslevels = SecurityUtil::accesslevelnames();
        $permissions = [];

        foreach ($allperms as $obj) {
            $id = $obj['gid']; //get's uid or gid accordingly

            $permissions[] = [
                'pid' => $obj['pid'],
                'group' => $ids[$id],
                'component' => $obj['component'],
                'instance' => $obj['instance'],
                'accesslevel' => $accesslevels[$obj['level']],
                'level' => $obj['level'],
                'sequence' => $obj['sequence']
            ];
            if ($action == 'modify' && $obj['pid'] == $chgpid) {
                $this->view->assign('selectedid', $id);
            }
        }
        $this->view->assign('permissions', $permissions);

        return $this->response($this->view->fetch('Admin/listedit.tpl'));
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
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
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
                $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved permission rule.'));
            } else {
                $request->getSession()->getFlashBag()->add('error', $warnmsg);
            }
        }

        return new RedirectResponse($this->get('router')->generate('zikulapermissionsmodule_admin_view', [], RouterInterface::ABSOLUTE_URL));
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
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
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
                $request->getSession()->getFlashBag()->add('status', $this->__('Done! Created permission rule.'));
            } else {
                $request->getSession()->getFlashBag()->add('error', $warnmsg);
            }
        }

        return new RedirectResponse($this->get('router')->generate('zikulapermissionsmodule_admin_view', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/delete/{pid}/{permgrp}", requirements={"pid"="\d+", "permgrp"="\d+"})
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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // Check for confirmation.
        if ($request->isMethod('GET')) {
            // Add a hidden field for the item ID to the output
            $this->view->assign('pid', $pid);

            // assign the permission type and group
            $this->view->assign('permgrp', $permgrp);

            // Return the output that has been generated by this function
            return $this->response($this->view->fetch('Admin/delete.tpl'));
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        // Pass to API
        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'delete', ['pid' => $pid])) {
            // Success
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted permission rule.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulapermissionsmodule_admin_view',
                ['filter-group' => $permgrp], RouterInterface::ABSOLUTE_URL
        ));
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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get all permissions schemas, sort and assign to the template
        $this->view->assign('schemas', ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'getallschemas'));

        // we don't return the output back to the core here since this template is a full page
        // template i.e. we don't want this output wrapped in the theme.
        return $this->response($this->view->fetch('Admin/viewinstanceinfo.tpl'));
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
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // assign the module vars
        $this->view->assign($this->getVars());

        // return the output
        return $this->response($this->view->fetch('Admin/modifyconfig.tpl'));
    }

    /**
     * @Route("/config")
     * @Method("POST")
     *
     * Save new settings.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function updateconfigAction(Request $request)
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $error = false;
        $filter = (bool)$request->request->get('filter', false);
        $this->setVar('filter', $filter);

        $rowview = (int)$request->request->get('rowview', 25);
        $this->setVar('rowview', $rowview);

        $rowedit = (int)$request->request->get('rowedit', 35);
        $this->setVar('rowedit', $rowedit);

        $lockadmin = (bool)$request->request->get('lockadmin', false);
        $this->setVar('lockadmin', $lockadmin);

        $adminid = (int)$request->request->get('adminid', 1);
        if ($adminid != 0) {
            $perm = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $adminid);
            if (!$perm) {
                $adminid = 0;
                $error = true;
            }
        }
        $this->setVar('adminid', $adminid);

        // the module configuration has been updated successfuly
        if ($error == true) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! Could not save configuration: unknown permission rule ID.'));
        } else {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved module configuration.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulapermissionsmodule_admin_view', [], RouterInterface::ABSOLUTE_URL));
    }
}
