<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Context menu reference
 *
 * This plugin adds a menu reference (could also be called a "placeholder").
 *
 * @see pnFormContextMenu
 *
 * @package pnForm
 * @subpackage Plugins
 */
class Form_Plugin_ContextMenu_Reference extends Form_Plugin
{
    protected $imageURL;
    protected $menuId;
    protected $commandArgument;

    function getFilename()
    {
        return __FILE__;
    }

    function render(&$render)
    {
        $imageURL = ($this->imageURL == null ? 'images/icons/extrasmall/tab_right.gif' : $this->imageURL);

        $menuPlugin = & $render->GetPluginById($this->menuId);
        $menuId = $menuPlugin->id;
        $html = "<img src=\"$imageURL\" alt=\"\" class=\"contextMenu\" onclick=\"pnForm.contextMenu.showMenu(event, '$menuId', '$this->commandArgument')\"/>";

        return $html;
    }
}

