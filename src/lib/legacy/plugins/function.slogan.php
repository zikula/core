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
 * Zikula_View function to display the slogan
 *
 * Example
 * {slogan}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    function.slogan.php::smarty_function_slogan()
 *
 * @return string The slogan.
 */
function smarty_function_slogan($params, $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('slogan', '$modvars.ZConfig.slogan')), E_USER_DEPRECATED);

    $slogan = System::getVar('slogan');

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $slogan);
    } else {
        return $slogan;
    }
}
