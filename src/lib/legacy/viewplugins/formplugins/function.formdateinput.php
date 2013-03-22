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
 * Date input for Zikula_Form_View.
 *
 * The date input plugin is a text input plugin that only allows dates to be posted. The value
 * returned from {@link Zikula_Form_View::getValues()} is although a string of the format 'YYYY-MM-DD'
 * since this is the standard internal Zikula format for dates.
 *
 * You can also use all of the features from the Zikula_Form_Plugin_TextInput plugin since the date input
 * inherits from it.
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Zikula_Form_View object.
 *
 * @return string The rendered output.
 */
function smarty_function_formdateinput($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_DateInput', $params);
}
