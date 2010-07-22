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
 * PostBack JavaScript function plugin
 *
 * Use this plugin to create a postback generating JavaScript function to be called from your
 * JavaScript code.
 *
 * Example:
 * <code>
 * <!--[formpostbackfunction function=startMyPostBack commandName=abc]-->
 * </code>
 * This generates a JavaScript function named startMyPostBack() that you can call from your own JavaScript.
 * When called it will generate a postback and fire an event to be handled by the $onCommand
 * method in the form event handler.
 */
class Form_Plugin_PostBackFunction extends Form_Plugin
{
    /**
     * Command name.
     *
     * This is the "commandName" parameter to pass in the event args of the command handler.
     *
     * @var string
     */
    public $commandName;

    /**
     * JavaScript function name to generate.
     *
     * This is the name of a JavaScript function you want to be created on the page. By calling this
     * function in your own JavaScript code you can initiate a postback that will call the
     * {@link FormPostBackFunction::$onCommand} event handler and pass
     * {@link FormPostBackFunction::$commandName} to it.
     *
     * @var string
     */
    public $function;

    /**
     * Name of command event handler method.
     *
     * @var string Default is "handleCommand".
     */
    public $onCommand = 'handleCommand';

    /**
     * Get filename for this plugin.
     *
     * A requirement from the framework - must be implemented like this. Used to restore plugins on postback.
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
     * @param Form_View $view Reference to Form_View object.
     *
     * @return string The rendered output
     */
    function render($view)
    {
        $html = '';

        $html .= "<script type=\"text/javascript\">\n<!--\n{$this->function} = function() { ";
        $html .= $view->getPostBackEventReference($this, $this->commandName);
        $html .= " }\n// -->\n</script>";

        return $html;
    }

    /**
     * Called by Form_View framework due to the use of Form_View::getPostBackEventReference() above.
     *
     * @param Form_View $view       Reference to Form_View object.
     * @param string      $eventArgument The event argument.
     *
     * @return void
     */
    function raisePostBackEvent($view, $eventArgument)
    {
        $args = array(
            'commandName' => $eventArgument,
            'commandArgument' => null
        );
        if (!empty($this->onCommand)) {
            $view->raiseEvent($this->onCommand, $args);
        }
    }
}
