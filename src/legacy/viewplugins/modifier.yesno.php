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
 * Zikula_View modifier to turn a boolean value into a suitable language string
 *
 * Example
 *
 *   {$myvar|yesno|safetext} returns Yes if $myvar = 1 and No if $myvar = 0
 *
 * @param string  $string The contents to transform.
 * @param boolean $images Display the yes/no response as tick/cross.
 *
 * @return string Rhe modified output.
 */
function smarty_modifier_yesno($string, $images = false)
{
    if ($string != '0' && $string != '1') return $string;

    if ($images) {
        $view = Zikula_View::getInstance();
        require_once $view->_get_plugin_filepath('function','img');
        $params = array('modname' => 'core', 'set' => 'icons/extrasmall');
    }

    if ((bool)$string) {
        if ($images) {
            $params['src'] = 'button_ok.png';
            $params['alt'] = $params['title'] = __('Yes');

            return smarty_function_img($params, $view);
        } else {
            return __('Yes');
        }
    } else {
        if ($images) {
            $params['src'] = 'button_cancel.png';
            $params['alt'] = $params['title'] = __('No');

            return smarty_function_img($params, $view);
        } else {
            return __('No');
        }
    }
}
