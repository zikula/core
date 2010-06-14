<?php
/**
 * renderer plugin
 *
 * This file is a plugin for renderer, the Zikula implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   renderer
 * @version      $Id$
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

function smarty_function_phpversion($params, &$smarty)
{
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], phpversion());
    } else {
        return phpversion();
    }
}
