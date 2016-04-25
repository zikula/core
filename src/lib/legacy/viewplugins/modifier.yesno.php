<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_View modifier to turn a boolean value into a suitable language string
 *
 * Example
 *
 *   {$myVar|yesno|safetext} returns Yes if $myVar = 1 and No if $myVar = 0
 *
 * @param string  $string The contents to transform.
 * @param boolean $images Display the yes/no response as tick/cross.
 *
 * @return string Rhe modified output.
 */
function smarty_modifier_yesno($string, $images = false)
{
    if ($string != '0' && $string != '1') {
        return $string;
    }

    if ($images) {
        $view = Zikula_View::getInstance();
        require_once $view->_get_plugin_filepath('function', 'img');
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
