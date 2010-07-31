<?php
/**
 * Smarty block to implement group checks in a template
 * 
 *
 * Example
 * <!--[checkgroup gid="1" ]-->
 * do some stuff now we have permission
 * <!--[/checkgroup]-->
 *
 * @author   Andre Bergues 
 * @param    array    $params     All attributes passed to this function from the template
 * @param    string   $content    The content between the block tags
 * @param    object   $smarty     Reference to the Smarty object
 * @return   mixed    the content if permission is held, null if no permissions is held (or on the opening tag), false on an error
 */
function smarty_block_checkgroup($params, $content, &$smarty)
{
	// check if there is something between the tags
    if (is_null($content)) {
        return;
    }
 
    // check our input
    if (!isset($params['gid'])) {
        $smarty->trigger_error('checkgroup: attribute component required');
        return false;
    }
 
    // look in the session to see if there is a UID
    $uid = SessionUtil::getVar('uid'); 
    if (empty($uid)) {
        return; // if not return ... no sql request to db are done
    }
   

   // group check ...
    if (!ModUtil::apiFunc('Groups', 'user', 'isgroupmember', array('uid' => $uid, 'gid' => $params['gid']))){
		return;
	}

    return $content;
}