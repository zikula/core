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
 * Web form label.
 *
 * Use this to create labels for your input fields in a web form. Example:
 * <code>
 *   {formlabel __text='Title' for='title'}
 *   {formtextinput id='title'}
 * </code>
 * The rendered output is an HTML label element with the "for" value
 * set to the supplied id. In addition to this, the Zikula_Form_Plugin_Label plugin also sets
 * "myLabel" on the "pointed-to" plugin to the supplied label text. This enables
 * the validation summary to display the label text.
 *
 * @param array            $params Parameters passed in the block tag
 * @param Zikula_Form_View $view   Reference to Zikula_Form_View object
 *
 * @return string The rendered output
 */
function smarty_function_formlabel($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_Label', $params);
}
