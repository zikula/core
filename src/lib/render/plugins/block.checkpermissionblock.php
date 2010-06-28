<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Implement permissions checks in a template.
 *
 * Available attributes:
 *  - component (string) The component to be tested, e.g., 'ModuleName::'
 *  - instance  (string) The instance to be tested, e.g., 'name::1'
 *  - level     (int)    The level of access required, e.g., ACCESS_READ
 *
 * Example:
 * <pre>
 * {checkpermissionblock component='News::' instance='1::' level=ACCESS_COMMENT}
 *   do some stuff now that we have permission
 * {/checkpermissionblock}
 * </pre>.
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param string $content The content between the block tags.
 * @param Smarty &$smarty Reference to the {@link Renderer} object.
 *
 * @return mixed The content of the block, if the user has the specified
 *               access level for the component and instance;
 *               otherwise null; false on an error.
 */
function smarty_block_checkpermissionblock($params, $content, &$smarty)
{
    if (is_null($content)) {
        return;
    }

    // check our input
    if (!isset($params['component'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_block_checkpermissionblock', 'component')));
        return false;
    }
    if (!isset($params['instance'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_block_checkpermissionblock', 'instance')));
        return false;
    }
    if (!isset($params['level'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_block_checkpermissionblock', 'level')));
        return false;
    }

    if (!SecurityUtil::checkPermission($params['component'], $params['instance'], constant($params['level']))) {
        return;
    }

    return $content;
}