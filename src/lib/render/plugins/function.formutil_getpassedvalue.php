<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: function.formutil_getpassedvalue.php 27368 2009-11-02 20:19:51Z mateo $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch
 * @package Zikula_Template_Plugins
 * @subpackage Functions
 */

/**
 * pnml: same as pnml() but with the added option to assign the result to a smarty variable
 *
 * @author   Robert Gasch
 * @version  $Id: function.formutil_getpassedvalue.php 27368 2009-11-02 20:19:51Z mateo $
 * @param    assign      The smarty variable to assign the retrieved value to
 * @param    html        Wether or not to pnVarPrepHTMLDisplay'ize the ML value
 * @param    key         The key to retrieve from the input vector
 * @param    default     The default value to return if the key is not set
 * @param    source      The input source to retrieve the key from 
 * @param    noprocess   If set, no processing is applied to the constant value
 *
 */
function smarty_function_formutil_getpassedvalue ($params, &$smarty)
{
    if ((!isset($params['key']) || !$params['key']) && 
        (!isset($params['name']) || !$params['name'])) { // use name as an alias for key for programmer convenience
        $smarty->trigger_error('formutil_getpassedvalue: attribute key (or name) required');
        return false;
    }

    $assign    = isset($params['assign'])    ? $params['assign']    : null;
    $key       = isset($params['key'])       ? $params['key']       : null;
    $default   = isset($params['default'])   ? $params['default']   : null;
    $html      = isset($params['html'])      ? $params['html']      : null;
    $source    = isset($params['source'])    ? $params['source']    : null;
    $noprocess = isset($params['noprocess']) ? $params['noprocess'] : null;

    if (!$key) {
        $key = isset($params['name']) ? $params['name'] : null;
    }

    $val = FormUtil::getPassedValue ($key, $default, $source);

    if ($noprocess) {
        $val = $val;
    } elseif ($html) {
        $val = DataUtil::formatForDisplayHTML($val);
    } else {
        $val = DataUtil::formatForDisplay($val);
    }

    if ($assign) {
        $smarty->assign ($assign, $val);
    } else {
        return $val;
    }
}
