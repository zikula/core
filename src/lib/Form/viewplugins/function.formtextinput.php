<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * TextInput plugin for Form_View
 *
 * The Form_Plugin_TextInput plugin is a general purpose input plugin that allows the user to enter any kind of character based data,
 * including text, numbers, dates and more.
 *
 * Typical use in template file:
 * <code>
 * <!--[formtextinput id="title" maxLength="100" width="30em"]-->
 * </code>
 *
 * The Form_Plugin_TextInput plugin supports basic CSS styling through attributes like "width", "color" and "font_weight". See
 * {@link Form_StyledPlugin} for more info.
 *
 * @param array     $params Parameters passed in the block tag.
 * @param Form_View $view   Reference to Form_View object.
 *
 * @return string The rendered output.
 */
function smarty_function_formtextinput($params, $view)
{
    // Let the Form_Plugin class do all the hard work
    return $view->registerPlugin('Form_Plugin_TextInput', $params);
}
