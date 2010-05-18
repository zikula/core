<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

function smarty_function_versioncompare($params, &$smarty)
{
    if (!isset($params['minversion'])) {
        return false;
    }

    // check if version is sufficient
    if (version_compare(phpversion(), $params['minversion'], ">=")) {
        $return = true;
    } else {
        $return = false;
    } 

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
