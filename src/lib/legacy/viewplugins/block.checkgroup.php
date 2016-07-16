<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 * @param array       $params  All attributes passed to this function from the template
 * @param string      $content The content between the block tags
 * @param Zikula_View $view    Reference to the {@link Zikula_View} object
 *
 * @return string|boolean|void The content of the matching case.
 *                             If the user is a member of the group specified by the gid,
 *                             then the content contained in the block, otherwise null,
 *                             false on error
 */
function smarty_block_checkgroup($params, $content, Zikula_View $view)
{
    // check if there is something between the tags
    if (is_null($content)) {
        return;
    }

    // check our input
    if (!isset($params['gid'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_block_checkgroup', 'component']));

        return false;
    }

    $uid = $view->getRequest()->getSession()->get('uid');
    if (empty($uid)) {
        return;
    }

    if (!ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isgroupmember', ['uid' => $uid, 'gid' => $params['gid']])) {
        return;
    }

    return $content;
}
