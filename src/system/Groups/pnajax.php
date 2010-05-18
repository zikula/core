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
function Groups_ajax_updategroup($args)
{
    if (!SecurityUtil::confirmAuthKey()) {
        AjaxUtil::error(FormUtil::getPassedValue('authid') . ' : ' . __("Sorry! Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."));
    }

    $gid          = FormUtil::getPassedValue('gid', null,    'post');
    $gtype        = FormUtil::getPassedValue('gtype', 9999,  'post');
    $state        = FormUtil::getPassedValue('state', null,  'post');
    $nbumax       = FormUtil::getPassedValue('nbumax', 9999, 'post');
    $name         = DataUtil::convertFromUTF8(FormUtil::getPassedValue('name',        null, 'post'));
    $description  = DataUtil::convertFromUTF8(FormUtil::getPassedValue('description', null, 'post'));

    if (!SecurityUtil::checkPermission('Groups::', $gid.'::', ACCESS_EDIT)) {
        AjaxUtil::error(__('Sorry! You have not been granted access to this page.'));
    }

    if (empty($name)) {
        return array('error'   => true,
                     'gid'     => $gid,
                     'message' => __('Error! The group name is missing.'));
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
    $typelabel = array(0  => __('Core'),
                       1  => __('Public'),
                       2  => __('Private'));

    $statelabel = array(0 => __('Closed'),
                        1 => __('Open'));

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
function Groups_ajax_creategroup()
{
    if (!SecurityUtil::checkPermission('Groups::', '::', ACCESS_ADD)) {
        AjaxUtil::error(__('Sorry! You have not been granted access to this page.'));
    }

    if (!SecurityUtil::confirmAuthKey()) {
        AjaxUtil::error(FormUtil::getPassedValue('authid') . ' : ' . __("Sorry! Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."));
    }

    $typelabel = array(0  => __('Core'),
                       1  => __('Public'),
                       2  => __('Private'));

    $statelabel = array(0 => __('Closed'),
                        1 => __('Open'));

    // Default values
    $obj = array('name'        => '',
                 'gtype'       => 0,
                 'state'       => 0,
                 'nbumax'      => 0,
                 'description' => '');

    $newgroup = ModUtil::apiFunc('Groups', 'admin', 'create', $obj);

    if ($newgroup == false) {
        AjaxUtil::error(__('Error! Could not create the new group.'));
    }

    // temporary group name
    $updobj = array('name' => __f('Group %s', $newgroup),
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
function Groups_ajax_deletegroup()
{
    if (!SecurityUtil::confirmAuthKey()) {
        AjaxUtil::error(__("Sorry! Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."));
    }

    $gid   = FormUtil::getPassedValue('gid', null, 'get');
    $group = DBUtil::selectObjectByID('groups', $gid, 'gid');

    if (!SecurityUtil::checkPermission('Groups::', $gid.'::', ACCESS_DELETE)) {
        AjaxUtil::error(__('Sorry! You have not been granted access to this page.'));
    }

    // Check if it is the default group...
    $defaultgroup = ModUtil::getVar('Groups', 'defaultgroup');

    if ($group['gid'] == $defaultgroup) {
        AjaxUtil::error(__('Error! You cannot delete the default user group.'));
    }

    if (ModUtil::apiFunc('Groups', 'admin', 'delete', array('gid' => $gid)) == true) {
        return array('gid' => $gid);
    }

    AjaxUtil::error(__f('Error! Could not delete the \'%s\' group.', $gid));
}
