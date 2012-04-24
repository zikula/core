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
 * TextInput plugin for Zikula_Form_View
 *
 * The Zikula_Form_Plugin_TextInput plugin is a general purpose input plugin that allows the user to enter any kind of character based data,
 * including text, numbers, dates and more.
 *
 * Typical use in template file:
 * <code>
 * {formtextinput id='title' maxLength='100' width='30em'}
 * </code>
 *
 * The Zikula_Form_Plugin_TextInput plugin supports basic CSS styling through attributes like "width", "color" and "font_weight". See
 * {@link Zikula_Form_StyledPlugin} for more info.
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Zikula_Form_View object.
 *
 * @return string The rendered output.
 */
function smarty_function_formtextinput($params, $view)
{
    // Let the Zikula_Form_Plugin class do all the hard work
    return $view->registerPlugin('Zikula_Form_Plugin_TextInput', $params);
}
