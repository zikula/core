<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2010 Zikula Foundation
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

function smarty_function_extension_loaded($params, &$smarty)
{
    if (!isset($params['extension'])) {
        return false;
    }

    // check if the file exists
    $result = extension_loaded($params['extension']);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
