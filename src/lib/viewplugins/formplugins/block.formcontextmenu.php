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
 * Context menu plugin
 *
 * This plugin creates a popup menu to be used as a right-click context menu. To use it you must do three things:
 *
 * - Create a menu
 * - Add menu items (as sub-pugins of the menu)
 * - Add a reference to the menu (there can be more than one of these)
 *
 * Example usage with two menu items:
 * <code>
 * {formcontextmenu id='MyMenu' width='150px'}
 *   {formcontextmenuitem commandName='edit' __title='Edit'}
 *   {formcontextmenuitem commandName='new' __title='New'}
 * {/formcontextmenu}
 *
 * {foreach from=items item=item}
 *   {$item.title} {formcontextmenureference menuId='MyMenu' commandArgument=$item.id}
 * {/foreach}
 * </code>
 * As you can see it is possible to reuse the same menu more than once on a page - in the example above it is
 * used as a context menu for each of the "items" (for instance articles or webshop goods). Where ever you
 * insert a "formcontextmenureference" you will get a small clickable arrow indicating the menu. Clicking
 * on the reference will bring op the menu.
 *
 * In your event handler (which defaults to "handleCommand") you should check for both commandName and
 * commandArgument:
 * <code>
 * function handleCommand($view, &$args)
 * {
 *   echo "Command: $args[commandName], $args[commandArgument]. ";
 * }
 * </code>
 * The commandName value indicates the menu item which was clicked and the commandArgument is the value set
 * at the menu reference. The use of commandArgument makes it easy to identify which $item the menu was
 * activated for.
 *
 * @param array            $params  Parameters passed in the block tag.
 * @param string           $content Content of the block.
 * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
 *
 * @return string The rendered output.
 */
function smarty_block_formcontextmenu($params, $content, $view)
{
    return $view->registerBlock('Zikula_Form_Block_ContextMenu', $params, $content);
}
