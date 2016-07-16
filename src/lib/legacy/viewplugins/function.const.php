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
 * Zikula_View plugin to convert string to PHP constant (required to support class constants).
 *
 * Example:
 *   {const name="ModUtil::TYPE_SYSTEM"}
 *
 * Argument $params may contain:
 *   name      The constant name.
 *   assign    The smarty variable to assign the resulting menu HTML to.
 *   noprocess If set the resulting string constant is not processed.
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The language constant
 */
function smarty_function_const($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $name = isset($params['name']) ? $params['name'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['const', 'name']));

        return false;
    }

    $result = constant($name);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
