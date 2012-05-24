<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View block to implement group checks in a template.
 *
 * Available attributes:
 *  - gid (numeric) The ID number of the group to be tested.
 *
 * Example:
 * <pre>
 * {checkgroup gid='1'}
 *   do some stuff now we have permission
 * {/checkgroup}
 * </pre>.
 *
 * @param array       $params  All attributes passed to this function from the template.
 * @param string      $content The content between the block tags.
 * @param Zikula_View $view    Reference to the {@link Zikula_View} object.
 *
 * @return string|boolean|void The content of the matching case.
 *                             If the user is a member of the group specified by the gid,
 *                             then the content contained in the block, otherwise null,
 *                             false on error.
 */
function smarty_block_checkgroup($params, $content, Zikula_View $view)
{
    // check if there is something between the tags
    if (is_null($content)) {
        return;
    }

    // check our input
    if (!isset($params['gid'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_block_checkgroup', 'component')));

        return false;
    }

    $uid = SessionUtil::getVar('uid');
    if (empty($uid)) {
        return;
    }

    if (!ModUtil::apiFunc('Groups', 'user', 'isgroupmember', array('uid' => $uid, 'gid' => $params['gid']))) {
        return;
    }

    return $content;
}
