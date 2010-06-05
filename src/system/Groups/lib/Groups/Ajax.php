<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Permissions
 */

class Groups_Ajax extends AbstractController
{
    /**
     * Updates a group in the database
     *
     * @author Frank Schummertz - Frank Chestnut
     * @param gid the group id
     * @param gtype the group type
     * @param state the group state
     * @param nbumax the maximum of users
     * @param name the group name
     * @param description the group description
     * @return mixed updated group as array or Ajax error
     */
    public function updategroup($args)
    {
        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError());
        }

        $gid          = FormUtil::getPassedValue('gid', null,    'post');
        $gtype        = FormUtil::getPassedValue('gtype', 9999,  'post');
        $state        = FormUtil::getPassedValue('state', null,  'post');
        $nbumax       = FormUtil::getPassedValue('nbumax', 9999, 'post');
        $name         = DataUtil::convertFromUTF8(FormUtil::getPassedValue('name',        null, 'post'));
        $description  = DataUtil::convertFromUTF8(FormUtil::getPassedValue('description', null, 'post'));

        if (!SecurityUtil::checkPermission('Groups::', $gid.'::', ACCESS_EDIT)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true));
        }

        if (empty($name)) {
            return array('error'   => true,
                    'gid'     => $gid,
                    'message' => $this->__('Error! The group name is missing.'));
        }

        if (preg_match("/[\n\r\t\x0B]/", $name)) {
            $name = trim(preg_replace("/[\n\r\t\x0B]/", "", $name));
        }
        if (preg_match("/[\n\r\t\x0B]/", $description)) {
            $description = trim(preg_replace("/[\n\r\t\x0B]/", "", $description));
        }

        // Pass to API
        $res = ModUtil::apiFunc('Groups',
                'admin',
                'update',
                array('gid'         => $gid,
                'name'        => $name,
                'gtype'       => $gtype,
                'state'       => $state,
                'nbumax'      => $nbumax,
                'description' => $description));

        if ($res == false) {
            // check for sessionvar
            $msgs = LogUtil::getStatusMessagesText();
            if (!empty($msgs)) {
                // return with msg, but not via AjaxUtil::error
                return array(   'error'   => true,
                        'gid'     => $gid,
                        'message' => $msgs);
            }
        }

        // Setting various defines
        $typelabel = array(0  => $this->__('Core'),
                1  => $this->__('Public'),
                2  => $this->__('Private'));

        $statelabel = array(0 => $this->__('Closed'),
                1 => $this->__('Open'));

        // Using uncached query here as it was returning the unupdated group
        $group = DBUtil::selectObjectByID('groups', $gid, 'gid', null, null, null, false);

        // get group member count
        $group['nbuser'] = ModUtil::apiFunc('Groups', 'user', 'countgroupmembers', array('gid' => $gid));

        $group['statelbl'] = $statelabel[$group['state']];
        $group['gtypelbl'] = $typelabel[$group['gtype']];

        return $group;
    }

    /**
     * Create a blank group and return it
     *
     * @author Frank Schummertz - Frank Chestnut
     * @param none
     * @return mixed array with new permission or Ajax error
     */
    public function creategroup()
    {
        if (!SecurityUtil::checkPermission('Groups::', '::', ACCESS_ADD)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true));
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError());
        }

        $typelabel = array(0  => $this->__('Core'),
                1  => $this->__('Public'),
                2  => $this->__('Private'));

        $statelabel = array(0 => $this->__('Closed'),
                1 => $this->__('Open'));

        // Default values
        $obj = array('name'        => '',
                'gtype'       => 0,
                'state'       => 0,
                'nbumax'      => 0,
                'description' => '');

        $newgroup = ModUtil::apiFunc('Groups', 'admin', 'create', $obj);

        if ($newgroup == false) {
            return AjaxUtil::error(LogUtil::registerError($this->__('Error! Could not create the new group.')));
        }

        // temporary group name
        $updobj = array('name' => $this->__f('Group %s', $newgroup),
                'gid'  => $newgroup);

        DBUtil::updateObject($updobj, 'groups', null, 'gid');

        // finally select the new group
        $obj = DBUtil::selectObjectByID('groups', $newgroup, 'gid', null, null, null, false);

        $obj['statelbl']   = $statelabel[$obj['state']];
        $obj['gtypelbl']   = $typelabel[$obj['gtype']];
        $obj['membersurl'] = ModUtil::url('Groups', 'admin', 'groupmembership', array('gid' => $newgroup));

        return $obj;
    }

    /**
     * Delete a group
     *
     * @author Frank Schummertz - Frank Chestnut
     * @param gid the group id
     * @return mixed the id of the group that has been deleted or Ajax error
     */
    public function deletegroup()
    {
        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError());
        }

        $gid   = FormUtil::getPassedValue('gid', null, 'get');
        $group = DBUtil::selectObjectByID('groups', $gid, 'gid');

        if (!SecurityUtil::checkPermission('Groups::', $gid.'::', ACCESS_DELETE)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true));
        }

        // Check if it is the default group...
        $defaultgroup = ModUtil::getVar('Groups', 'defaultgroup');

        if ($group['gid'] == $defaultgroup) {
            return AjaxUtil::error(LogUtil::registerError($this->__('Error! You cannot delete the default user group.')));
        }

        if (ModUtil::apiFunc('Groups', 'admin', 'delete', array('gid' => $gid)) == true) {
            return array('gid' => $gid);
        }

        return AjaxUtil::error(LogUtil::registerError($this->__f('Error! Could not delete the \'%s\' group.', $gid)));
    }
}