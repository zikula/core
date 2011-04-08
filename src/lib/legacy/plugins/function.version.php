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
 * Zikula_View function to get the site's charset.
 *
 * This function will return the Zikula version number
 *
 * available parameters:
 *  - assign      if set, the language will be assigned to this variable
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The version string.
 */
function smarty_function_version($params, $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('version', '$coredata.version_num')), E_USER_DEPRECATED);

    $assign = isset($params['assign']) ? $params['assign'] : null;

    $return = Zikula_Core::VERSION_NUM;

    if ($assign) {
        $view->assign($assign, $return);
    } else {
        return $return;
    }
}
