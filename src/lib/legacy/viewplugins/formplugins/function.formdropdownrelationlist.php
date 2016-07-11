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
