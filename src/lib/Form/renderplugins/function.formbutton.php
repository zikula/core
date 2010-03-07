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
 * Button
 *
 * Buttons can be used to fire command events in your form event handler.
 * When the user activates a button the command name and command argument
 * will be sent to the form event handlers handleCommand function.
 * Example:
 * <code>
 *  function handleCommand(&$render, &$args)
 *  {
 *    if ($args['commandName'] == 'update')
 *    {
 *      if (!$render->IsValid())
 *        return false;
 *
 *      $data = $render->GetValues();
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
 * @package pnForm
 * @subpackage Plugins
 */
function smarty_function_formbutton($params, &$render)
{
    return $render->RegisterPlugin('Form_Plugin_Button', $params);
}
