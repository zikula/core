<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\GroupsModule\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Module\GroupsModule\Helper\CommonHelper;
use SecurityUtil;
use ModUtil;
use LogUtil;
use Zikula_Exception_Fatal;

/**
 * Groups_Controller_Ajax class.
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * Updates a group in the database
     *
     * @param gid the group id.
     * @param gtype the group type.
     * @param state the group state.
     * @param nbumax the maximum of users.
     * @param name the group name.
     * @param description the group description.
     *
     * @return AjaxResponse
     */
    public function updategroupAction()
    {
        $this->checkAjaxToken();

        $gid = $this->request->request->get('gid');
        $gtype = $this->request->request->get('gtype', 9999);
        $state = $this->request->request->get('state');
        $nbumax = $this->request->request->get('nbumax', 9999);
        $name = $this->request->request->get('name');
        $description = $this->request->request->get('description');

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            throw new AccessDeniedHttpException();
        }

        if (empty($name)) {
            return new AjaxResponse(array('result' => false, 'error' => true, 'gid' => $gid, 'message' => $this->__('Error! The group name is missing.')));
        }

        if (preg_match("/[\n\r\t\x0B]/", $name)) {
            $name = trim(preg_replace("/[\n\r\t\x0B]/", "", $name));
        }
        if (preg_match("/[\n\r\t\x0B]/", $description)) {
            $description = trim(preg_replace("/[\n\r\t\x0B]/", "", $description));
        }

        // Pass to API
        $res = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'update',
                        array('gid' => $gid,
                              'name' => $name,
                              'gtype' => $gtype,
                              'state' => $state,
                              'nbumax' => $nbumax,
                              'description' => $description));

        if (!$res) {
            // check for sessionvar
            $msgs = LogUtil::getStatusMessagesText();
            if (!empty($msgs)) {
                // return with msg, but not via Zikula_Exception_Fatal
                return new AjaxResponse(array('result' => false, 'error' => true, 'gid' => $gid, 'message' => $msgs));
            }
        }

        // Setting various defines
        $groupsCommon = new CommonHelper();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

        // get group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));

        // get group member count
        $group['nbuser'] = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', array('gid' => $gid));

        $group['statelbl'] = $statelabel[$group['state']];
        $group['gtypelbl'] = $typelabel[$group['gtype']];

        return new AjaxResponse($group);
    }

    /**
     * Create a blank group and return it.
     *
     * @return AjaxResponse
     */
    public function creategroupAction()
    {
        $this->checkAjaxToken();

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedHttpException();
        }

        $groupsCommon = new CommonHelper();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

        // Default values
        $obj = array(
            'name' => '',
            'gtype' => CommonHelper::GTYPE_CORE,
            'state' => CommonHelper::STATE_CLOSED,
            'nbumax' => 0,
            'description' => ''
        );

        $group_id = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'create', $obj);

        if ($group_id == false) {
            throw new Zikula_Exception_Fatal($this->__('Error! Could not create the new group.'));
        }

        // update group's name
        $group = $this->entityManager->find('Zikula\Module\GroupsModule\Entity\GroupEntity', $group_id);
        $group['name'] = $this->__f('Group %s', $group_id);
        $this->entityManager->flush();

        // convert to array
        $group = $group->toArray();

        $group['statelbl'] = $statelabel[$group['state']];
        $group['gtypelbl'] = $typelabel[$group['gtype']];
        $group['membersurl'] = ModUtil::url('ZikulaGroupsModule', 'admin', 'groupmembership', array('gid' => $group_id));

        return new AjaxResponse($group);
    }

    /**
     * Delete a group.
     *
     * @param gid the group id.
     *
     * @return AjaxResponse
     */
    public function deletegroupAction()
    {
        $this->checkAjaxToken();

        $gid = $this->request->request->get('gid');
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_DELETE)) {
            throw new AccessDeniedHttpException();
        }

        // Check if it is the default group...
        $defaultgroup = $this->getVar('defaultgroup');

        if ($group['gid'] == $defaultgroup) {
            throw new Zikula_Exception_Fatal($this->__('Error! You cannot delete the default user group.'));
        }

        if (ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'delete', array('gid' => $gid)) == true) {
            return new AjaxResponse(array('gid' => $gid));
        }

        throw new Zikula_Exception_Fatal($this->__f('Error! Could not delete the \'%s\' group.', $gid));
    }

    public function removeuserAction()
    {
        $this->checkAjaxToken();

        $gid = (int)$this->request->request->get('gid');
        $uid = (int)$this->request->request->get('uid');

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            throw new AccessDeniedHttpException();
        }

        if (!ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'removeuser', array('gid' => $gid, 'uid' => $uid))) {
            throw new Zikula_Exception_Fatal($this->__('Error! A problem occurred while attempting to remove the user. The user has not been removed from the group.'));
        }

        $result = array(
            'gid' => $gid,
            'uid' => $uid
        );

        return new AjaxResponse($result);
    }
}