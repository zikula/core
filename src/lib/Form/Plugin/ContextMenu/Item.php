<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Context menu item
 *
 * This plugin represents a menu item.
 *
 * @see pnFormContextMenu
 */
class Form_Plugin_ContextMenu_Item extends Form_Plugin
{
    /**
     * Menu title
     *
     * Language constants can be used here.
     *
     * @var string
     */
    public $title;

    public $imageURL;

    /**
     * Command name passed to the event handler
     *
     * @var string
     */
    public $commandName;

    /**
     * JavaScript code to execute when menu item is selected
     *
     * Your script will be wrapped in a function that passes a parameter "commandArgument". This parameter
     * contains the command argument of the pnformcontextmenureference plugin. In this way your script
     * can work with the menu item data you clicked. Example:
     * <code>
     * <!--[formcontextmenuitem title=Preview imageURL="preview.gif" commandScript="popupPreview(commandArgument)"]-->
     *
     * <script type="text/javascript">
     * function popupPreview(commandArgument)
     * {
     * alert(commandArgument);
     * }
     * </script>
     * </code>
     *
     * @var string
     */
    public $commandScript;

    /**
     * URL to redirect to when menu item is selected
     *
     * You can place {commandArgument} (including the braces) in your URL. This will get substituted with the
     * command argument value of the pnformcontextmenureference plugin. In this way you can redirect to something
     * depending on data.
     * @var string
     */
    public $commandRedirect;

    /**
     * Confirmation message
     *
     * If you set a confirmation message then a ok/cancel dialog box pops and asks the user to confirm
     * the menu item click - very usefull for menu selections that deletes items.
     * You can use _XXX language defines directly as the message, no need to call <!--[pnml]--> for
     * translation.
     * @var string
     */
    public $confirmMessage;

    function getFilename()
    {
        return __FILE__;
    }

    function create(&$render, &$params)
    {
    }

    function render(&$render)
    {
        $contextMenu = & $this->getParentContextMenu();

        if (!$contextMenu) {
            return;
        }

        // Avoid creating menu multiple times if included in a repeated template
        if (!$contextMenu->firstTime()) {
            return '';
        }

        if (!empty($this->commandName)) {
            $click = 'javascript:' . $this->renderConfirm($render, $render->getPostBackEventReference($this, $this->commandName));

        } else if (!empty($this->commandScript)) {
            $hiddenName = "contentMenuArgument" . $contextMenu->id;
            $click = 'javascript:' . $this->renderConfirm($render, "pnForm.contextMenu.commandScript('$hiddenName', function(commandArgument){" . $this->commandScript . "})");

        } else if (!empty($this->commandRedirect)) {
            $hiddenName = "contentMenuArgument" . $contextMenu->id;
            $url = urlencode($this->commandRedirect);
            $click = 'javascript:' . $this->renderConfirm($render, "pnForm.contextMenu.redirect('$hiddenName','$url')");
        } else {
            z_exit('Missing commandName, commandScript, or commandRedirect in context menu item');
        }

        $url = $click;
        $title = $render->translateForDisplay($this->title);

        if (!empty($this->imageURL)) {
            $style = " style=\"background-image: url($this->imageURL)\"";
        } else {
            $style = '';
        }

        $html = "<li$style><a href=\"$url\">$title</a></li>";

        return $html;
    }

    function renderConfirm(&$render, $script)
    {
        if (!empty($this->confirmMessage)) {
            $msg = $render->translateForDisplay($this->confirmMessage) . '?';
            return "if (confirm('$msg')) { $script }";
        } else {
            return $script;
        }
    }

    // Called by pnForms framework due to the use of pnFormGetPostBackEventReference() above
    function raisePostBackEvent(&$render, $eventArgument)
    {
        $contextMenu = & $this->getParentContextMenu();

        $hiddenName = "contentMenuArgument" . $contextMenu->id;
        $commandArgument = FormUtil::getPassedValue($hiddenName, null, 'POST');

        $args = array(
            'commandName' => $eventArgument,
            'commandArgument' => $commandArgument);
        $render->raiseEvent($contextMenu->onCommand == null ? 'handleCommand' : $contextMenu->onCommand, $args);
    }

    function &getParentContextMenu()
    {
        // Locate parent context menu
        $contextMenu = &$this->parentPlugin;

        while ($contextMenu != null && !($contextMenu instanceof Form_Block_ContextMenu))
            $contextMenu = &$contextMenu->parentPlugin;

        return $contextMenu;
    }
}

