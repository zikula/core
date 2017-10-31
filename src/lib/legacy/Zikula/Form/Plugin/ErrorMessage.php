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
 * Error message placeholder
 *
 * Use this plugin to display error messages. It should be added in your template in the exact location where
 * you want the error message to be displayed. Then, on postback, you can do as shown here to set the
 * error message:
 * <code>
 * function handleCommand(Zikula_Form_View $view, &$args)
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
 * Beware that {@link Zikula_Form_View::getPluginById()} only works on postback.
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_ErrorMessage extends Zikula_Form_AbstractPlugin
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
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        if ('' != $this->message) {
            $cssClass = (null == $this->cssClass ? 'alert alert-danger' : $this->cssClass);
            $html = "<div class=\"$cssClass\">" . $this->message . "</div>\n";

            return $html;
        }

        return '';
    }
}
