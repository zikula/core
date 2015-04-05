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
 * Insert a CSRF protection nonce.
 *
 * Available parameters:
 *   - assign: Assign rather the output.
 *
 * Example:
 * <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_insert_adminpanelmenu($params, $view)
{
    $modules = ModUtil::getModulesCapableOf('admin');
    echo "<pre style='text-align:left'>"; var_dump($modules); echo "</pre>";

}
