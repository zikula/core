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
 * Context menu reference
 *
 * This plugin adds a menu reference (could also be called a "placeholder").
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_ContextMenu_Reference extends Zikula_Form_AbstractPlugin
{
    /**
     * URL to the item image.
     *
     * @var string
     */
    public $imageURL;

    /**
     * Menu ID.
     *
     * @var string
     */
    public $menuId;

    /**
     * Context menu command argument.
     *
     * @var string
     */
    public $commandArgument;

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
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $imageURL = ($this->imageURL == null ? 'images/icons/extrasmall/tab_right.png' : $this->imageURL);

        $menuPlugin = $view->getPluginById($this->menuId);
        $menuId = $menuPlugin->id;
        $html = "<img src=\"{$imageURL}\" alt=\"\" class=\"contextMenu\" onclick=\"FormContextMenu.showMenu(event, '{$menuId}', '{$this->commandArgument}')\" />";

        return $html;
    }
}
