<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
