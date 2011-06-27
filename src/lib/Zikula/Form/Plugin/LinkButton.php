<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 * @subpackage Zikula_Form_AbstractPlugin
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * LinkButton
 *
 * Link buttons can be used instead of normal buttons to fire command events in
 * your form event handler. A link button is simply a link (anchor tag with
 * some JavaScript) that can be used exactly like a normal button - but with
 * a different visualization.
 *
 * When the user activates a link button the command name and command argument
 * will be sent to the form event handlers handleCommand function.
 * Example:
 * <code>
 * function handleCommand(Zikula_Form_View $view, &$args)
 * {
 * if ($args['commandName'] == 'update')
 * {
 * if (!$view->isValid())
 * return false;
 *
 * $data = $view->getValues();
 *
 * DBUtil::updateObject($data, 'demo_data');
 * }
 *
 * return true;
 * }
 * </code>
 *
 * The command arguments ($args) passed to the handler contains 'commandName' and
 * 'commandArgument' with the values you passed to the button in the template.
 */
class Zikula_Form_Plugin_LinkButton extends Zikula_Form_AbstractStyledPlugin
{
    /**
     * Displayed text in the link.
     *
     * @var string
     */
    public $text;

    /**
     * Name of command event handler method.
     *
     * @var string Default is "handleCommand".
     */
    public $onCommand = 'handleCommand';

    /**
     * Command name.
     *
     * This is the "commandName" parameter to pass in the event args of the command handler.
     *
     * @var string
     */
    public $commandName;

    /**
     * Command argument.
     *
     * This value is passed in the event arguments to the form event handler as the commandArgument value.
     *
     * @var string
     */
    public $commandArgument;

    /**
     * Confirmation message.
     *
     * If you set a confirmation message then a ok/cancel dialog box pops and asks the user to confirm
     * the button click - very usefull for buttons that deletes items.
     *
     * @var string
     */
    public $confirmMessage;

    /**
     * CSS styling.
     *
     * Please ignore - to be changed.
     *
     * @var string
     */
    public $styleHtml;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    function render(Zikula_Form_View $view)
    {
        $idHtml = $this->getIdHtml();

        $text = $view->translateForDisplay($this->text);

        $onclickHtml = '';
        if ($this->confirmMessage != null) {
            $msg = $view->translateForDisplay($this->confirmMessage) . '?';
            $onclickHtml = " onclick=\"return confirm('$msg');\"";
        }

        $imageHtml = '';
        if (isset($this->attributes['imgsrc']) && !empty($this->attributes['imgsrc'])) {
            if (!isset($this->attributes['imgset']) || empty($this->attributes['imgset'])) {
                $this->attributes['imgset'] = 'icons/extrasmall';
            }
            // we're going to make use of pnimg for path searching
            require_once $view->_get_plugin_filepath('function', 'img');

            // call the pnimg plugin and work out the src from the assigned template vars
            $args = array('src' => $this->attributes['imgsrc'],
                          'set' => $this->attributes['imgset'],
                          'title' => $text,
                          'alt' => $text,
                          'modname' => 'core');

            $imageHtml = smarty_function_img($args, $view);
            $imageHtml .= !empty($imageHtml) ? ' ' : '';
        }
        if (isset($this->attributes['imgsrc'])) {
            unset($this->attributes['imgsrc']);
        }
        if (isset($this->attributes['imgset'])) {
            unset($this->attributes['imgset']);
        }

        $attributes = $this->renderAttributes($view);

        $carg = serialize(array('cname' => $this->commandName, 'carg' => $this->commandArgument));
        $href = $view->getPostBackEventReference($this, $carg);
        $href = htmlspecialchars($href);

        $result = "<a{$idHtml} href=\"javascript:{$href}\"{$onclickHtml}{$attributes}>{$imageHtml}$text</a>";

        return $result;
    }

    /**
     * Called by Zikula_Form_View framework due to the use of getPostBackEventReference() above.
     *
     * @param Zikula_Form_View $view          Reference to Zikula_Form_View object.
     * @param string           $eventArgument The event argument.
     *
     * @return void
     */
    function raisePostBackEvent(Zikula_Form_View $view, $eventArgument)
    {
        $carg = unserialize($eventArgument);
        $args = array(
            'commandName' => $carg['cname'],
            'commandArgument' => $carg['carg']
        );
        if (!empty($this->onCommand)) {
            $view->raiseEvent($this->onCommand, $args);
        }
    }
}
