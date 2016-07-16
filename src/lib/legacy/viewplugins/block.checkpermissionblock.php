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
 * Implement permissions checks in a template.
 *
 * Available attributes:
 *  - component (string) The component to be tested, e.g., 'ModuleName::'
 *  - comp      (string) Same as component
 *  - instance  (string) The instance to be tested, e.g., 'name::1'
 *  - inst      (string) Same as instance
 *  - level     (int)    The level of access required, e.g., ACCESS_READ
 *
 * Example:
 * <pre>
 * {checkpermissionblock component='News::' instance='1::' level=ACCESS_COMMENT}
 *   do some stuff now that we have permission
 * {/checkpermissionblock}
 * </pre>.
 *
 * @param array       $params  All attributes passed to this function from the template
 * @param string      $content The content between the block tags
 * @param Zikula_View $view    Reference to the {@link Zikula_View} object
 *
 * @return mixed The content of the block, if the user has the specified
 *               access level for the component and instance;
 *               otherwise null; false on an error
 */
function smarty_block_checkpermissionblock($params, $content, Zikula_View $view)
{
    if (is_null($content)) {
        return;
    }

    if (isset($params['component'])) {
        $comp = $params['component'];
    } elseif (isset($params['comp'])) {
        LogUtil::log(__f('Warning! The {checkpermissionblock} parameter %1$s is deprecated. Please use %2$s instead.', ['comp', 'component']), E_USER_DEPRECATED);
        $comp = $params['comp'];
    } else {
        $comp = null;
    }
    if (isset($params['instance'])) {
        $inst = $params['instance'];
    } elseif (isset($params['inst'])) {
        LogUtil::log(__f('Warning! The {checkpermissionblock} parameter %1$s is deprecated. Please use %2$s instead.', ['inst', 'instance']), E_USER_DEPRECATED);
        $inst = $params['inst'];
    } else {
        $inst = null;
    }

    // check our input
    if (!isset($comp)) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_block_checkpermissionblock', 'component']));

        return false;
    }
    if (!isset($inst)) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_block_checkpermissionblock', 'instance']));

        return false;
    }
    if (!isset($params['level'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_block_checkpermissionblock', 'level']));

        return false;
    }

    if (!SecurityUtil::checkPermission($comp, $inst, constant($params['level']))) {
        return;
    }

    return $content;
}
