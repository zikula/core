<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
