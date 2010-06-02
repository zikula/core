<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Error message placeholder
 *
 * Use this plugin to display error messages. It should be added in your template in the exact location where
 * you want the error message to be displayed. Then, on postback, you can do as shown here to set the
 * error message:
 * <code>
 * function handleCommand(&$render, &$args)
 * {
 * if ($args['commandName'] == 'update')
 * {
 * if (!$render->isValid())
 * return false;
 *
 * $data = $render->getValues();
 * if (... something is wrong ...)
 * {
 * $errorPlugin = $render->GetPluginById('MyPluginId');
 * $errorPlugin->message = 'Something happend';
 * return false;
 * }
 *
 * ... handle data ...
 * }
 *
 * return true;
 * }
 * </code>
 * Beware that {@link pnFormRender::pnFormGetPluginById()} only works on postback.
 */
class Form_Plugin_ErrorMessage extends Form_Plugin
{
    /**
     * Displayed error message
     * @var string
     */
    public $message;

    /**
     * CSS class for styling
     * @var string
     */
    public $cssClass;

    function getFilename()
    {
        return __FILE__;
    }

    function render(&$render)
    {
        if ($this->message != '') {
            $cssClass = ($this->cssClass == null ? 'z-errormsg' : $this->cssClass);
            $html = "<div class=\"$cssClass\">" . $this->message . "</div>\n";

            return $html;
        }

        return '';
    }
}

