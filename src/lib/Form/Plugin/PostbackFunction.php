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
     * Command name
     *
     * This is the "commandName" parameter to pass in the event args of the command handler.
     * @var string
     */
    protected $commandName;

    /**
     * JavaScript function name to generate
     *
     * This is the name of a JavaScript function you want to be created on the page. By calling this
     * function in your own JavaScript code you can initiate a postback that will call the
     * {@link pnFormPostBackFunction::$onCommand} event handler and pass
     * {@link pnFormPostBackFunction::$commandName} to it.
     */
    protected $function;

    /**
     * Name of command event handler method
     * @var string Default is "handleCommand"
     */
    protected $onCommand = 'handleCommand';

    /**
     * Get filename for this plugin
     *
     * A requirement from the framework - must be implemented like this. Used to restore plugins on postback.
     * @internal
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    function render(&$render)
    {
        $html = '';

        $html .= "<script type=\"text/javascript\">\n<!--\n{$this->function} = function() { ";
        $html .= $render->GetPostBackEventReference($this, $this->commandName);
        $html .= " }\n// -->\n</script>";

        return $html;
    }

    function raisePostBackEvent(&$render, $eventArgument)
    {
        $args = array(
            'commandName' => $eventArgument,
            'commandArgument' => null);
        if (!empty($this->onCommand))
            $render->RaiseEvent($this->onCommand, $args);
    }
}


