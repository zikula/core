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
 * Date input for Zikula_Form_View.
 *
 * The date input plugin is a text input plugin that only allows dates to be posted. The value
 * returned from {@link Zikula_Form_View::getValues()} is although a string of the format 'YYYY-MM-DD'
 * since this is the standard internal Zikula format for dates.
 *
 * You can also use all of the features from the Zikula_Form_Plugin_TextInput plugin since the date input
 * inherits from it.
 *
 * @param array            $params Parameters passed in the block tag
 * @param Zikula_Form_View $view   Reference to Zikula_Form_View object
 *
 * @return string The rendered output
 */
function smarty_function_formdateinput($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_DateInput', $params);
}
