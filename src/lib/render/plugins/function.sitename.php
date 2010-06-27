<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display the sitename
 *
 * Available parameters:
 *  - assign     if set, the title will be assigned to this variable
 *
 * Example
 * {sitename}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 *
 * @see    function.sitename.php::smarty_function_sitename()
 * @return string The sitename.
 */
function smarty_function_sitename($params, &$smarty)
{
    $sitename = System::getVar('sitename');

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $sitename);
    } else {
        return $sitename;
    }
}
