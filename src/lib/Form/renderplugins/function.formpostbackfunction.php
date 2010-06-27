<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Template_Plugins
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
 *
 * @param array       $params  Parameters passed in the block tag.
 * @param Form_Render &$render Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formpostbackfunction($params, &$render)
{
    // Let the pnFormPlugin class do all the hard work
    return $render->registerPlugin('Form_Plugin_PostBackFunction', $params);
}
