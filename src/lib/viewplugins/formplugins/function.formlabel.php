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
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Zikula_Form_View object.
 *
 * @return string The rendered output.
 */
function smarty_function_formlabel($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_Label', $params);
}
