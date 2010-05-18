<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package      Xanthia_Templating_Environment
 * @subpackage   pnRender
 */

function smarty_function_iswriteable($params, &$smarty)
{
    if (!isset($params['file'])) {
        return false;
    }
    $file = $params['file'];

    // is_writable() is not reliable enough - drak
    if (is_dir($file)) {
        $result = is_writable($file);
    } else {
        $result = @fopen($file, 'a');
        if ($result === true) {
            fclose($result);
        }
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
