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
 * Dropdown relation list.
 *
 * This plugin creates a drop down list from a relation.
 *
 * This plugin supports doctrine.
 * Example:
 * <code>
 * {formdropdownrelationlist recordClass="MyModule_Model_User" where="active = true"
 *  num=15 oderby="registrationDate DESC"}
 * </code>
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formdropdownrelationlist($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_DropdownRelationlist', $params);
}
