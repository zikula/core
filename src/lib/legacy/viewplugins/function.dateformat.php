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
 * DateFormat.
 *
 * Params can be:
 *  format   The date format we wish to convert to (optional) (default='Y-m-d').
 *  datetime The datetime we wish to convert.
 *  assign   The smarty variable we wish to assign the result to (optional).
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string
 */
function smarty_function_dateformat($params, Zikula_View $view)
{
    if (!isset($params['datetime'])) {
        $params['datetime'] = null;
    }

    if (!isset($params['format']) || empty($params['format'])) {
        $params['format'] = null;
    }

    $res = DateUtil::getDatetime($params['datetime'], $params['format']);

    if (isset($params['assign']) && $params['assign']) {
        $view->assign($params['assign'], $res);
    } else {
        return $res;
    }
}
