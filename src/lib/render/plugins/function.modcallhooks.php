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
 * Smarty function call hooks
 *
 * This function calls a specific module function.  It returns whatever the return
 * value of the resultant function is if it succeeds.
 * Note that in contrast to the API function modcallhooks you need not to load the
 * module with ModUtil::load.
 *
 *
 * Available parameters:
 * - 'hookobject' the object the hook is called for - either 'item' or 'category'
 * - 'hookaction' the action the hook is called for - one of 'create', 'delete', 'transform', or 'display'
 * - 'hookid'     the id of the object the hook is called for (module-specific)
 * - 'subject'    the calling subject (usually $controller)
 * - 'args'       extra arguments.
 * - 'implode'    Implode collapses all display hooks into a single string.
 * - 'assign'     If set, the results are assigned to the corresponding variable instead of printed out
 * - all remaining parameters are passed to the ModUtil::callHooks API via the extrainfo array
 *
 * Example
 * {modcallhooks hookobject='item' hookaction='modify' hookid=$tid $modname='ThisModule' $objectid=$tid}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 *
 * @see    function.modcallhooks.php::smarty_function_modcallhooks()
 * @return string The results of the module function.
 */
function smarty_function_modcallhooks($params, &$smarty)
{
    $assign     = isset($params['assign'])     ? $params['assign']        : null;
    $hookid     = isset($params['hookid'])     ? $params['hookid']        : '';
    $hookaction = isset($params['hookaction']) ? $params['hookaction']    : null;
    $hookobject = isset($params['hookobject']) ? $params['hookobject']    : null;
    $implode    = isset($params['implode'])    ? (bool)$params['implode'] : true;
    $subject    = isset($params['subject'])    ? $params['subject']       : null;
    $args       = isset($params['args'])       ? $params['args']          : array();

    // avoid sending these to ModUtil::callHooks
    unset($params['hookobject']);
    unset($params['hookaction']);
    unset($params['hookid']);
    unset($params['assign']);
    unset($params['implode']);
    unset($params['subject']);
    unset($params['args']);

    if (!$hookobject) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modcallhooks', 'hookobject')));
        return false;
    }
    if (!$hookaction) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modcallhooks', 'hookaction')));
        return false;
    }
    if (!$hookid) {
        $hookid = '';
    }
    if (is_null($subject) && isset($smarty->controller)) {
        $subject = $smarty->controller;
    }

    // create returnurl if not supplied (= this page)
    if (!isset($params['returnurl']) || empty($params['returnurl'])) {
        $params['returnurl'] = str_replace('&amp;', '&', 'http://' . System::getHost() . System::getCurrentUri());
    }

    // if the implode flag is true then we must always assign the result to a template variable
    // outputing the erray is no use....
    if (!$implode) {
        $assign = 'hooks';
    }

    $result = ModUtil::callHooks($hookobject, $hookaction, $hookid, $params, $implode, $subject, $args);

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
