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
 * Display the list of allowed HTML tags.
 *
 * Available parameters:
 *  - assign    (string)    (optional) If set, the results are assigned to the
 *                          corresponding variable instead of printed out
 *
 * Example:
 *
 * <samp>{allowedhtml}</samp>
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the {@link Zikula_View} object.
 *
 * @return string A string containing the allowable HTML tags.
 */
function smarty_function_allowedhtml($params, &$smarty)
{
    LogUtil::log(__f('Warning! Template plugin {%s} is deprecated.', array('allowedhtml')), E_USER_DEPRECATED);

    $AllowableHTML = System::getVar('AllowableHTML');
    $allowedhtml = '';
    foreach ($AllowableHTML as $key => $access) {
        if ($access > 0) {
            $allowedhtml .= '&lt;' . htmlspecialchars($key) . '&gt; ';
        }
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $allowedhtml);
    } else {
        return $allowedhtml;
    }
}
