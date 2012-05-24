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
 * FormUtil::getPassedValue().
 *
 * Available parameters:
 *   assign    The smarty variable to assign the retrieved value to.
 *   html      Wether or not to DataUtil::formatForDisplayHTML'ize the ML value.
 *   key       The key to retrieve from the input vector.
 *   name      Alias for key.
 *   default   The default value to return if the key is not set.
 *   source    The input source to retrieve the key from .
 *   noprocess If set, no processing is applied to the constant value.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 *
 */
function smarty_function_formutil_getpassedvalue($params, Zikula_View $view)
{
    if ((!isset($params['key']) || !$params['key']) &&
            (!isset($params['name']) || !$params['name'])) {
        // use name as an alias for key for programmer convenience
        $view->trigger_error('formutil_getpassedvalue: attribute key (or name) required');

        return false;
    }

    $assign = isset($params['assign']) ? $params['assign'] : null;
    $key = isset($params['key']) ? $params['key'] : null;
    $default = isset($params['default']) ? $params['default'] : null;
    $html = isset($params['html']) ? $params['html'] : null;
    $source = isset($params['source']) ? $params['source'] : null;
    $noprocess = isset($params['noprocess']) ? $params['noprocess'] : null;

    if (!$key) {
        $key = isset($params['name']) ? $params['name'] : null;
    }

    $source = isset($source) ? $source : null;
    switch ($source) {
        case 'GET':
            $val = $view->getRequest()->query->get($key, $default);
            break;
        case 'POST':
            $val = $view->getRequest()->request->get($key, $default);
            break;
        case 'SERVER':
            $val = $view->getRequest()->server->get($key, $default);
            break;
        case 'FILES':
            $val = $view->getRequest()->files->get($key, $default);
            break;
        default:
            $val = $view->getRequest()->query->get($key, $view->getRequest()->request->get($key, $default));
            break;
    }

    if ($noprocess) {
        $val = $val;
    } elseif ($html) {
        $val = DataUtil::formatForDisplayHTML($val);
    } else {
        $val = DataUtil::formatForDisplay($val);
    }

    if ($assign) {
        $view->assign($assign, $val);
    } else {
        return $val;
    }
}
