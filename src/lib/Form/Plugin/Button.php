<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Form_Plugin
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Button
 *
 * Buttons can be used to fire command events in your form event handler.
 * When the user activates a button the command name and command argument
 * will be sent to the form event handlers handleCommand function.
 * Example:
 * <code>
 * function handleCommand($render, &$args)
 * {
 * if ($args['commandName'] == 'update')
 * {
 * if (!$render->isValid())
 * return false;
 *
 * $data = $render->getValues();
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
class Form_Plugin_Button extends Form_StyledPlugin
{
    /**
     * Displayed text on the button.
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
     * You can use _XXX language defines directly as the message, no need to call <!--[pnml]--> for
     * translation.
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
     * @param Form_View $render Reference to Form render object.
     *
     * @return string The rendered output
     */
    function render($render)
    {
        $idHtml = $this->getIdHtml();

        $fullName = $this->id . '_' . $this->commandName;

        $onclickHtml = '';
        $onkeypressHtml = '';
        if ($this->confirmMessage != null) {
            $msg = $render->translateForDisplay($this->confirmMessage) . '?';
            $onclickHtml = " onclick=\"return confirm('$msg');\"";
            $onkeypressHtml = " onkeypress=\"return confirm('$msg');\"";
        }

        $text = $render->translateForDisplay($this->text);

        $attributes = $this->renderAttributes($render);

        $result = "<input $idHtml type=\"submit\" name=\"$fullName\" value=\"$text\"$onclickHtml$onkeypressHtml{$attributes}/>";

        return $result;
    }

    /**
     * Decode event handler for actions that generate a postback event.
     *
     * @param Form_View $render Reference to Form render object.
     *
     * @return boolean
     */
    function decodePostBackEvent($render)
    {
        $fullName = $this->id . '_' . $this->commandName;

        if (isset($_POST[$fullName])) {
            $args = array(
                'commandName' => $this->commandName,
                'commandArgument' => $this->commandArgument);
            if (!empty($this->onCommand)) {
                if ($render->raiseEvent($this->onCommand, $args) === false) {
                    return false;
                }
            }
        }

        return true;
    }
}

