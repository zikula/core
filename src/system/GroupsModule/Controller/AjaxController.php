<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Controller;

use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\FatalResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
use Zikula\GroupsModule\Helper\CommonHelper;
use SecurityUtil;
use ModUtil;
use LogUtil;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove

/**
 * @Route("/ajax")
 *
 * Ajax controllers for the groups module
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
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
     * @return AjaxResponse|ForbiddenResponse ajax response object
     */
    public function updategroupAction(Request $request)
    {
        $this->checkAjaxToken();

        $gid = $request->request->get('gid');
        $gtype = $request->request->get('gtype', 9999);
        $state = $request->request->get('state');
        $nbumax = $request->request->get('nbumax', 9999);
        $name = $request->request->get('name');
        $description = $request->request->get('description');

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            return new ForbiddenResponse($this->__('You do not have permission for this action.'));
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
     * @return AjaxResponse|ForbiddenResponse|FatalResponse ajax response object
     */
    public function creategroupAction()
    {
        $this->checkAjaxToken();

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_ADD)) {
            return new ForbiddenResponse($this->__('You do not have permission for this action.'));
        }

        $groupsCommon = new CommonHelper();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

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
            return new FatalResponse($this->__('Error! Could not create the new group.'));
        }

        // update group's name
        $group = $this->entityManager->find('ZikulaGroupsModule:GroupEntity', $group_id);
        $group['name'] = $this->__f('Group %s', $group_id);
        $this->entityManager->flush();

        // convert to array
        $group = $group->toArray();

        $group['statelbl'] = $statelabel[$group['state']];
        $group['gtypelbl'] = $typelabel[$group['gtype']];
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
     * @return AjaxResponse|ForbiddenResponse|FatalResponse ajax response object
     */
    public function deletegroupAction(Request $request)
    {
        $this->checkAjaxToken();

        $gid = $request->request->get('gid');
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_DELETE)) {
            return new ForbiddenResponse($this->__('You do not have permission for this action.'));
        }

        // Check if it is the default group...
        $defaultgroup = $this->getVar('defaultgroup');

        if ($group['gid'] == $defaultgroup) {
            return new FatalResponse($this->__('Error! You cannot delete the default user group.'));
        }

        if (ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'delete', ['gid' => $gid]) == true) {
            return new AjaxResponse(['gid' => $gid]);
        }

        return new FatalResponse($this->__f('Error! Could not delete the \'%s\' group.', $gid));
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
     * @return AjaxResponse|ForbiddenResponse|FatalResponse ajax response object
     */
    public function removeuserAction(Request $request)
    {
        $this->checkAjaxToken();

        $gid = (int)$request->request->get('gid');
        $uid = (int)$request->request->get('uid');

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            return new ForbiddenResponse($this->__('You do not have permission for this action.'));
        }

        if (!ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'removeuser', ['gid' => $gid, 'uid' => $uid])) {
            return new FatalResponse($this->__('Error! A problem occurred while attempting to remove the user. The user has not been removed from the group.'));
        }

        $result = [
            'gid' => $gid,
            'uid' => $uid
        ];

        return new AjaxResponse($result);
    }
}
