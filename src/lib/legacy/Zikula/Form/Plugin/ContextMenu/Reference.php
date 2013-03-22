<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 * @subpackage Zikula_Form_Plugin_ContextMenu
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Context menu reference
 *
 * This plugin adds a menu reference (could also be called a "placeholder").
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
        $html = "<img src=\"{$imageURL}\" alt=\"\" class=\"contextMenu\" onclick=\"Form.contextMenu.showMenu(event, '{$menuId}', '{$this->commandArgument}')\" />";

        return $html;
    }
}
