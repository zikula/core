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
 *  function handleCommand($render, &$args)
 *  {
 *    if ($args['commandName'] == 'update')
 *    {
 *      if (!$render->isValid())
 *        return false;
 *
 *      $data = $render->getValues();
 *
 *      DBUtil::updateObject($data, 'demo_data');
 *    }
 *
 *    return true;
 *  }
 * </code>
 *
 * The command arguments ($args) passed to the handler contains 'commandName' and
 * 'commandArgument' with the values you passed to the button in the template.
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formlinkbutton($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_LinkButton', $params);
}
