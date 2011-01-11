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
 * Error message placeholder
 *
 * Use this plugin to display error messages. It should be added in your template in the exact location where
 * you want the error message to be displayed. Then, on postback, you can do as shown here to set the
 * error message:
 * <code>
 * function handleCommand($view, &$args)
 * {
 * if ($args['commandName'] == 'update')
 * {
 * if (!$view->isValid())
 * return false;
 *
 * $data = $view->getValues();
 * if (... something is wrong ...)
 * {
 * $errorPlugin = $view->getPluginById('MyPluginId');
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
 * Beware that {@link Form_View::getPluginById()} only works on postback.
 */
class Form_Plugin_ErrorMessage extends Form_Plugin
{
    /**
     * Displayed error message.
     *
     * @var string
     */
    public $message;

    /**
     * CSS class for styling.
     *
     * @var string
     */
    public $cssClass;

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
     * @param Form_View $view Reference to Form render object.
     *
     * @return string The rendered output
     */
    function render($view)
    {
        if ($this->message != '') {
            $cssClass = ($this->cssClass == null ? 'z-errormsg' : $this->cssClass);
            $html = "<div class=\"$cssClass\">" . $this->message . "</div>\n";

            return $html;
        }

        return '';
    }
}
