<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2010, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/LGPL - http://www.gnu.org/copyleft/lgpl.html
 */

function smarty_function_ini_get($params, &$smarty)
{
    if (!isset($params['varname'])) {
        $smarty->trigger_error("ini_get: parameter 'name' required");
        return false;
    }

    // check if the file exists
    $result = ini_get($params['varname']);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
