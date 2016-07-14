<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Controller;

use LogUtil;
use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\GroupsModule\Helper\CommonHelper;

/**
 * @Route("/ajax")
 *
 * Ajax controllers for the groups module
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/update", options={"expose"=true})
     * @Method("POST")
     *
     * Updates a group in the database
     *
     *  int $gid the group id.
     *  int $gtype the group type.
     *  bool $state the group state.
     *  int $nbumax the maximum of users.
     *  string $name the group name.
     *  string $description the group description.
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     */
    public function updategroupAction(Request $request)
    {
        $gid = $request->request->getDigits('gid');
        $gtype = $request->request->get('gtype', 9999);
        $state = $request->request->get('state');
        $nbumax = $request->request->get('nbumax', 9999);
        $name = $request->request->get('name');
        $description = $request->request->get('description');

        if (!$this->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (empty($name)) {
            return new AjaxResponse([
                'result' => false,
                'error' => true,
                'gid' => $gid,
                'message' => $this->__('Error! The group name is missing.')
            ]);
        }

        if (preg_match("/[\n\r\t\x0B]/", $name)) {
            $name = trim(preg_replace("/[\n\r\t\x0B]/", "", $name));
        }
        if (preg_match("/[\n\r\t\x0B]/", $description)) {
            $description = trim(preg_replace("/[\n\r\t\x0B]/", "", $description));
        }

        // Pass to API
        $res = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'update', [
            'gid' => $gid,
            'name' => $name,
            'gtype' => $gtype,
            'state' => $state,
            'nbumax' => $nbumax,
            'description' => $description
        ]);

        if (!$res) {
            // check for sessionvar
            $msgs = LogUtil::getStatusMessagesText();
            if (!empty($msgs)) {
                return new AjaxResponse([
                    'result' => false,
                    'error' => true,
                    'gid' => $gid,
                    'message' => $msgs
                ]);
            }
        }

        // Setting various defines
        $groupsCommon = new CommonHelper();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

        // get group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);

        // get group member count
        $group['nbuser'] = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', ['gid' => $gid]);

        $group['statelbl'] = $statelabel[$group['state']];
        $group['gtypelbl'] = $typelabel[$group['gtype']];

        return new AjaxResponse($group);
    }

    /**
     * @Route("/create", options={"expose"=true})
     * @Method("POST")
     *
     * Create a blank group and return it.
     *
     * @return AjaxResponse ajax response object
     * @throws FatalErrorException if cannot create
     */
    public function creategroupAction()
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $groupsCommon = new CommonHelper();
        $typeLabels = $groupsCommon->gtypeLabels();
        $stateLabels = $groupsCommon->stateLabels();

        // Default values
        $obj = [
            'name' => '',
            'gtype' => CommonHelper::GTYPE_CORE,
            'state' => CommonHelper::STATE_CLOSED,
            'nbumax' => 0,
            'description' => ''
        ];

        $group_id = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'create', $obj);

        if ($group_id == false) {
            throw new FatalErrorException($this->__('Error! Could not create the new group.'));
        }

        // update group's name
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $group = $entityManager->find('ZikulaGroupsModule:GroupEntity', $group_id);
        $group['name'] = $this->__f('Group %s', ['%s' => $group_id]);
        $entityManager->flush();

        // convert to array
        $group = $group->toArray();

        $group['statelbl'] = $stateLabels[$group['state']];
        $group['gtypelbl'] = $typeLabels[$group['gtype']];
        $group['membersurl'] = $this->get('router')->generate('zikulagroupsmodule_admin_groupmembership', ['gid' => $group_id]);

        return new AjaxResponse($group);
    }

    /**
     * @Route("/delete", options={"expose"=true})
     * @Method("POST")
     *
     * Delete a group.
     *
     *  int $gid the group id.
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     * @throws FatalErrorException if cannot delete
     */
    public function deletegroupAction(Request $request)
    {
        $gid = $request->request->getDigits('gid');
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);

        if (!$this->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // Check if it is the default group...
        $defaultgroup = $this->getVar('defaultgroup');

        if ($group['gid'] == $defaultgroup) {
            throw new FatalErrorException($this->__('Error! You cannot delete the default user group.'));
        }

        if (true != ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'delete', ['gid' => $gid])) {
            throw new FatalErrorException($this->__f('Error! Could not delete the \'%s\' group.', ['%s' => $gid]));
        }

        return new AjaxResponse(['gid' => $gid]);
    }

    /**
     * @Route("/removeuser", options={"expose"=true})
     * @Method("POST")
     *
     * Remove a user from a group
     *
     *  int $uid the user id
     *  int $gid the group id
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     * @throws FatalErrorException if cannot remove
     */
    public function removeuserAction(Request $request)
    {
        $gid = (int)$request->request->getDigits('gid');
        $uid = (int)$request->request->getDigits('uid');

        if (!$this->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (!ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'removeuser', ['gid' => $gid, 'uid' => $uid])) {
            throw new FatalErrorException($this->__('Error! A problem occurred while attempting to remove the user. The user has not been removed from the group.'));
        }

        $result = [
            'gid' => $gid,
            'uid' => $uid
        ];

        return new AjaxResponse($result);
    }
}
