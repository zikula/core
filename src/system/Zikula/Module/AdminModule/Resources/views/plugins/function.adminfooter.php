<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to close the admin container.
 *
 * Admin
 * {adminfooter}
 *
 * @see          function.adminfooter.php::smarty_function_adminfooter()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $view        Reference to the Zikula_View object
 * @return       string      the results of the module function
 */
function smarty_function_adminfooter($params, $view)
{
    return ModUtil::func('ZikulaAdminModule', 'admin', 'adminfooter');
}
