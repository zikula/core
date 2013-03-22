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
 * Renders and displays all active blocks assigned to the specified position.
 *
 * Available attributes:
 * - name       (string)    name of the block position to render and display; to
 *                          support legacy templates, the strings 'l', 'r', and
 *                          'c' will be translated to 'left', 'right' and 'center'
 * - implode    (bool|int)  if set, the indiviual blocks in the position will be
 *                          'imploded' to a single string (optional, default == true)
 * - assign     (string)    if set, the rendered output will be assigned to this
 *                          variable instead of being returned to the template (optional)
 *
 * Example:
 *
 * <samp>{blockposition name='left'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string The rendered ouput of all of the blocks assigned to this position.
 */
function smarty_function_blockposition($params, Zikula_View $view)
{
    // fix the core positions for a better name
    if ($params['name'] == 'l') $params['name'] = 'left';
    if ($params['name'] == 'r') $params['name'] = 'right';
    if ($params['name'] == 'c') $params['name'] = 'center';

    if (!isset($params['name'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('blockposition', 'name')));

        return false;
    }

    $implode = (isset($params['implode']) && isset($params['assign'])) ? (bool)$params['implode'] : true;

    $return = BlockUtil::displayPosition($params['name'], false, $implode);
    if (isset($params['assign'])) {
        $view->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
