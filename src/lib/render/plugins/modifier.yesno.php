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
 * Smarty modifier to turn a boolean value into a suitable language string
 *
 * Example
 *
 *   <!--[$myvar|yesno|varprepfordisplay]--> returns Yes if $myvar = 1 and No if $myvar = 0
 *
 * @param        string    $string     the contents to transform
 * @param        string    $images    display the yes/no response as tick/cross
 * @return       string   the modified output
 */
function smarty_modifier_yesno($string, $images = false)
{
    if ($string != '0' && $string != '1') return $string;

    if ($images) {
        $smarty = Renderer::getInstance();
        require_once $smarty->_get_plugin_filepath('function','img');
        $params = array('modname' => 'core', 'set' => 'icons/extrasmall');
    }

    if ((bool)$string) {
        if ($images) {
            $params['src'] = 'button_ok.gif';
            $params['alt'] = $params['title'] = __('Yes');
            return smarty_function_img($params, $smarty);
        } else {
            return __('Yes');
        }
    } else {
        if ($images) {
            $params['src'] = 'button_cancel.gif';
            $params['alt'] = $params['title'] = __('No');
            return smarty_function_img($params, $smarty);
        } else {
            return __('No');
        }
    }
}
