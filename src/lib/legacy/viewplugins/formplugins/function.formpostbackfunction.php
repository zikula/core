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
 * PostBack JavaScript function plugin
 *
 * Use this plugin to create a postback generating JavaScript function to be called from your
 * JavaScript code.
 *
 * Example:
 * <code>
 * {formpostbackfunction function='startMyPostBack' commandName='abc'}
 * </code>
 * This generates a JavaScript function named startMyPostBack() that you can call from your own JavaScript.
 * When called it will generate a postback and fire an event to be handled by the $onCommand
 * method in the form event handler.
 *
 * @param array            $params Parameters passed in the block tag
 * @param Zikula_Form_View $view   Reference to Zikula_Form_View object
 *
 * @return string The rendered output
 */
function smarty_function_formpostbackfunction($params, $view)
{
    // Let the Zikula_Form_Plugin class do all the hard work
    return $view->registerPlugin('Zikula_Form_Plugin_PostBackFunction', $params);
}
