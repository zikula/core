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
 * function handleCommand(Zikula_Form_View $view, &$args)
 * {
 * echo "Command: $args[commandName], $args[commandArgument]. ";
 * }
 * </code>
 * The commandName value indicates the menu item which was clicked and the commandArgument is the value set
 * at the menu reference. The use of commandArgument makes it easy to identify which $item the menu was
 * activated for.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Block_ContextMenu extends Zikula_Form_AbstractStyledPlugin
{
    /**
     * CSS class name.
     *
     * The class name is applied to the div element that surrounds the entire menu. Defaults to "contextMenu".
     *
     * @var string
     */
    public $cssClass;

    /**
     * Name of command event handler method.
     *
     * Defaults to "handleCommand".
     *
     * @var string
     */
    public $onCommand;

    /**
     * Z-index for absolute positioning.
     *
     * No need to change or set this unless there's a conflict with other libraries (for instance prototype).
     *
     * @var integer
     */
    public $zIndex;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Zikula_View plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        $this->styleAttributes['display'] = 'none';
        $this->styleAttributes['z-index'] = ($this->zIndex === null ? 10 : $this->zIndex);
    }

    /**
     * DataBound event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Zikula_View plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function dataBound(Zikula_Form_View $view, &$params)
    {
        PageUtil::AddVar('javascript', 'javascript/helpers/form/form.js');
        PageUtil::AddVar('javascript', 'javascript/ajax/prototype.js');
    }

    /**
     * RenderBegin event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    public function renderBegin(Zikula_Form_View $view)
    {
        if ($this->firstTime(false)) {
            $cssClass = ($this->cssClass == null ? "contextMenu" : $this->cssClass);
            $attributes = $this->renderAttributes($view);
            $hiddenName = "contentMenuArgument" . $this->id;
            $html = "<input type=\"hidden\" name=\"{$hiddenName}\" id=\"{$hiddenName}\" /><div id=\"{$this->id}\" class=\"{$cssClass}\"{$attributes}><ul>";

            return $html;
        } else {
            return '';
        }
    }

    /**
     * RenderEnd event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    public function renderEnd(Zikula_Form_View $view)
    {
        if ($this->firstTime(true)) {
            $html = '</ul></div>';

            return $html;
        } else {
            return '';
        }
    }

    /**
     * Check if it's the first time.
     *
     * @param boolean $doSet Whether or not to set the check variable.
     *
     * @return boolean
     */
    public function firstTime($doSet = false)
    {
        static $createdMenus = [];
        if (isset($createdMenus[$this->id])) {
            return false;
        }
        if ($doSet) {
            $createdMenus[$this->id] = true;
        }

        return true;
    }
}
