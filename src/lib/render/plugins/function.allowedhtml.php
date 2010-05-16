<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
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
 * @param Smarty &$smarty Reference to the {@link Renderer} object.
 *
 * @return string A string containing the allowable HTML tags.
 */
function smarty_function_allowedhtml($params, &$smarty)
{
    $AllowableHTML = pnConfigGetVar('AllowableHTML');
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