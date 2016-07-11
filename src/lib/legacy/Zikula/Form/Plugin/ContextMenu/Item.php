<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context menu item
 *
 * This plugin represents a menu item.
 * See also Zikula_Form_Block_ContextMenu.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_ContextMenu_Item extends Zikula_Form_AbstractPlugin
{
    /**
     * Menu title.
     *
     * Language constants can be used here.
     *
     * @var string
     */
    public $title;

    /**
     * URL to the item's image.
     *
     * @var string
     */
    public $imageURL;

    /**
     * Command name passed to the event handler.
     *
     * @var string
     */
    public $commandName;

    /**
     * JavaScript code to execute when menu item is selected.
     *
     * Your script will be wrapped in a function that passes a parameter "commandArgument". This parameter
     * contains the command argument of the formcontextmenureference plugin. In this way your script
     * can work with the menu item data you clicked. Example:
     * <code>
     * {formcontextmenuitem __title='Preview' imageURL='preview.png' commandScript='popupPreview(commandArgument)'}
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
     * URL to redirect to when menu item is selected.
     *
     * You can place {commandArgument} (including the braces) in your URL. This will get substituted with the
     * command argument value of the formcontextmenureference plugin. In this way you can redirect to something
     * depending on data.
     *
     * @var string
     */
    public $commandRedirect;

    /**
     * Confirmation message.
     *
     * If you set a confirmation message then a ok/cancel dialog box pops and asks the user to confirm
     * the menu item click - very usefull for menu selections that deletes items.
     *
     * @var string
     */
    public $confirmMessage;

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
     * @param Zikula_Form_View $view Reference to Form render object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
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

        if (!empty($this->commandName)) {
            $click = 'javascript:' . $this->renderConfirm($view, $view->getPostBackEventReference($this, $this->commandName));
        } elseif (!empty($this->commandScript)) {
            $hiddenName = "contentMenuArgument" . $contextMenu->id;
            $click = 'javascript:' . $this->renderConfirm($view, "FormContextMenu.commandScript('{$hiddenName}', function(commandArgument){{$this->commandScript}})");
        } elseif (!empty($this->commandRedirect)) {
            $hiddenName = "contentMenuArgument" . $contextMenu->id;
            $url = urlencode($this->commandRedirect);
            $click = 'javascript:' . $this->renderConfirm($view, "FormContextMenu.redirect('{$hiddenName}','{$url}')");
        } else {
            LogUtil::registerError('Missing commandName, commandScript, or commandRedirect in context menu item');
        }

        $url = $click;
        $title = $view->translateForDisplay($this->title);

        if (!empty($this->imageURL)) {
            $style = " style=\"background-image: url({$this->imageURL})\"";
        } else {
            $style = '';
        }

        $html = "<li{$style}><a href=\"{$url}\">{$title}</a></li>";

        return $html;
    }

    /**
     * Renders the confirmation action.
     *
     * @param Zikula_Form_View $view   Reference to Form render object.
     * @param string           $script JavaScript code to run.
     *
     * @return string The rendered output.
     */
    public function renderConfirm(Zikula_Form_View $view, $script)
    {
        if (!empty($this->confirmMessage)) {
            $msg = $view->translateForDisplay($this->confirmMessage) . '?';

            return "if (confirm('{$msg}')) { {$script} }";
        } else {
            return $script;
        }
    }

    /**
     * Called by Forms framework due to the use of getPostBackEventReference() above.
     *
     * @param Zikula_Form_View $view          Reference to Form render object.
     * @param string           $eventArgument The event argument.
     *
     * @return void
     */
    public function raisePostBackEvent(Zikula_Form_View $view, $eventArgument)
    {
        $contextMenu = $this->getParentContextMenu();

        $hiddenName = "contentMenuArgument" . $contextMenu->id;
        $commandArgument = $this->request->request->get($hiddenName, null);

        $args = [
            'commandName' => $eventArgument,
            'commandArgument' => $commandArgument
        ];

        $view->raiseEvent(null == $contextMenu->onCommand ? 'handleCommand' : $contextMenu->onCommand, $args);
    }

    /**
     * Get parent context menu.
     *
     * @return Zikula_Form_Block_ContextMenu Parent context menu.
     */
    public function getParentContextMenu()
    {
        // Locate parent context menu
        $contextMenu = $this->parentPlugin;

        while (null != $contextMenu && !($contextMenu instanceof Zikula_Form_Block_ContextMenu)) {
            $contextMenu = $contextMenu->parentPlugin;
        }

        return $contextMenu;
    }
}
