<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 * @subpackage Template_Plugins
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
 *  function handleCommand($render, &$args)
 *  {
 *    if ($args['commandName'] == 'update')
 *    {
 *      if (!$render->isValid())
 *        return false;
 *
 *      $data = $render->getValues();
 *      if (... something is wrong ...)
 *      {
 *        $errorPlugin = $render->getPluginById('MyPluginId');
 *        $errorPlugin->message = 'Something happend';
 *        return false;
 *      }
 *
 *      ... handle data ...
 *    }
 *
 *    return true;
 *  }
 * </code>
 * Beware that {@link Zikula_Form_View::getPluginById()} only works on postback.
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Zikula_Form_View object.
 *
 * @return string The rendered output.
 */
function smarty_function_formerrormessage($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_ErrorMessage', $params);
}
