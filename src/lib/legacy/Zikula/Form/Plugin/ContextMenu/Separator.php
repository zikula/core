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
 * Context menu separator
 *
 * This plugin represents a menu item.
 * See Zikula_Form_Plugin_ContextMenu.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_ContextMenu_Separator extends Zikula_Form_AbstractPlugin
{
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
        $contextMenu = $this->getParentContextMenu();

        if (!$contextMenu) {
            return '';
        }

        // Avoid creating menu multiple times if included in a repeated template
        if (!$contextMenu->firstTime()) {
            return '';
        }

        return "<li class=\"separator\">&nbsp;</li>";
    }

    /**
     * Get the parent content menu.
     *
     * @return Zikula_Form_Block_ContextMenu
     */
    public function &getParentContextMenu()
    {
        // Locate parent context menu
        $contextMenu = $this->parentPlugin;

        while ($contextMenu != null && !($contextMenu instanceof Zikula_Form_Block_ContextMenu)) {
            $contextMenu = $contextMenu->parentPlugin;
        }

        return $contextMenu;
    }
}
