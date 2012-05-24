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
 * Retrieve and display the site's charset.
 *
 * Available attributes:
 *  - assign    (string)    the name of a template variable to assign the
 *                          output to, instead of returning it to the template. (optional)
 *
 * Example:
 *
 * <samp><meta http-equiv="Content-Type" content="text/html; charset={charset}"></samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string The value of the charset.
 */
function smarty_function_charset($params, Zikula_View $view)
{
    $return = ZLanguage::getEncoding();

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
