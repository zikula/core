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
 * Button
 *
 * Buttons can be used to fire command events in your form event handler.
 * When the user activates a button the command name and command argument
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
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_Button extends Zikula_Form_AbstractStyledPlugin
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
        $idHtml = $this->getIdHtml();

        $fullName = $this->id . '_' . $this->commandName;

        $onclickHtml = '';
        $onkeypressHtml = '';
        if ($this->confirmMessage != null) {
            $msg = $view->translateForDisplay($this->confirmMessage) . '?';
            $onclickHtml = " onclick=\"return confirm('$msg');\"";
            $onkeypressHtml = " onkeypress=\"return confirm('$msg');\"";
        }

        $text = $view->translateForDisplay($this->text);

        $attributes = $this->renderAttributes($view);

        $result = "<input{$idHtml} name=\"{$fullName}\" value=\"{$text}\" type=\"submit\"{$onclickHtml}{$onkeypressHtml}{$attributes} />";

        return $result;
    }

    /**
     * Decode event handler for actions that generate a postback event.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return boolean
     */
    public function decodePostBackEvent(Zikula_Form_View $view)
    {
        $fullName = $this->id . '_' . $this->commandName;

        if (isset($_POST[$fullName])) {
            $args = array(
                'commandName' => $this->commandName,
                'commandArgument' => $this->commandArgument
            );
            if (!empty($this->onCommand)) {
                if ($view->raiseEvent($this->onCommand, $args) === false) {
                    return false;
                }
            }
        }

        return true;
    }
}
