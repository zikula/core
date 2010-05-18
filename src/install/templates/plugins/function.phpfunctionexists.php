<?php
/**
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the Zikula implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   pnRender
 * @version      $Id: function.themelist.php 20025 2006-09-15 07:31:06Z markwest $
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

function smarty_function_phpfunctionexists($params, &$smarty)
{
    if (!isset($params['func'])) {
        return false;
    }

    $funcexists = false;
    if (function_exists($params['func'])) {
        $funcexists = true;
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $funcexists);
    } else {
        return $funcexists;
    }
}
