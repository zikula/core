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
 * Date input for pnForms.
 *
 * The date input plugin is a text input plugin that only allows dates to be posted. The value
 * returned from {@link pnForm::pnFormGetValues()} is although a string of the format 'YYYY-MM-DD'
 * since this is the standard internal Zikula format for dates.
 *
 * You can also use all of the features from the pnFormTextInput plugin since the date input
 * inherits from it.
 *
 * @param array       $params  Parameters passed in the block tag.
 * @param Form_View &$render Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formdateinput($params, &$render)
{
    return $render->registerPlugin('Form_Plugin_DateInput', $params);
}
