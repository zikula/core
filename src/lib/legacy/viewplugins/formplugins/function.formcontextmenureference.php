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
 * Context menu reference
 *
 * This plugin creates a context menu reference.
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formcontextmenureference($params, $view)
{
    $output = $view->registerPlugin('Zikula_Form_Plugin_ContextMenu_Reference', $params);
    if (array_key_exists('assign', $params)) {
        $view->assign($params['assign'], $output);
    } else {
        return $output;
    }
}
