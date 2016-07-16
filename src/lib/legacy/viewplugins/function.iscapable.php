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
 * Wrapper for ModUtil::isCapable().
 *
 * Param takes 'modules' and 'capability' keys.
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string Translation if it was available
 */
function smarty_function_iscapable($params, Zikula_View $view)
{
    if (!isset($params['module'])) {
        $view->trigger_error(__('Error! "module" parameter must be specified.'));
    }
    if (!isset($params['capability'])) {
        $view->trigger_error(__('Error! "module" parameter must be specified.'));
    }

    $result = ModUtil::isCapable($module, $params['capability']);

    // assign or return
    if (isset($params['assign'])) {
        $view->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
