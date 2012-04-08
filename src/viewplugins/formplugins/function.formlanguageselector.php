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
 * Language selector.
 *
 * This plugin creates a language selector using a dropdown list.
 * The selected value of the base dropdown list will be set to the 3-letter language code of
 * the selected language.
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formlanguageselector($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_LanguageSelector', $params);
}
