<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: function.installtypes.php 19171 2006-05-30 12:06:21Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

function smarty_function_fileexists($params, &$smarty)
{
    if (!isset($params['file'])) {
        return false;
    }

    // check if the file exists
    $result = file_exists($params['file']);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
