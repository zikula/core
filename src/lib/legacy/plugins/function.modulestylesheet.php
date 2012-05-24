<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to provide easy access to a stylesheet.
 *
 * This function provides an easy way to include a stylesheet. The function will add the stylesheet
 * file to the 'stylesheet' pagevar by default
 *
 * This plugin is obsolete since Zikula 1.1.0. The stylesheets are loaded automatically whenever a module
 * or block is loaded. We keep this file for the sake of backwards compatibility so that themes do not break.
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param object &$smarty Reference to the Smarty object.
 *
 * @return void But Add Js header if admin
 */
function smarty_function_modulestylesheet($params, &$smarty)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated.', array('modulestylesheet')), E_USER_DEPRECATED);

    // do nothing unless we are admin
    if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
        PageUtil::addVar('javascript', 'javascript/ajax/prototype.js');
        PageUtil::addVar('header', '<script type="text/javascript">/* <![CDATA[ */ Event.observe(window, "load", function() { alert("'.__('You can safely remove the modulestylesheet plugin from your theme. It is obsolete since Zikula 1.1.0. The adding of stylesheet files has been automated and does not need user interference. This note is shown to Administrators only.').'");}); /* ]]> */</script>');
    }

    return;
}
