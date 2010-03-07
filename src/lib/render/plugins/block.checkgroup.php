<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty block to implement group checks in a template
 *
 * available parameters:
 *  - component   the component under test
 *  - instance    the instance under test
 *  - level       the level of access required
 *
 * Example
 * <!--[checkgroup gid='1']-->
 * do some stuff now we have permission
 * <!--[/checkgroup]-->
 *
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
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_block_checkgroup', 'component')));
        return false;
    }

    $uid = SessionUtil::getVar('uid');
    if (empty($uid)) {
        return;
    }

    if (!pnModAPIFunc('Groups', 'user', 'isgroupmember', array('uid' => $uid, 'gid' => $params['gid']))){
        return;
    }

    return $content;
}
