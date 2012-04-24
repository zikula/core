<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Context menu item.
 *
 * This plugin represents a menu item.
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Form render object.
 *
 * @see    Zikula_Form_Plugin_ContextMenu
 * @return string The rendered output.
 */
function smarty_function_formcontextmenuitem($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_ContextMenu_Item', $params);
}
