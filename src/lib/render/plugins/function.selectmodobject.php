<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * render plugin for fetching a particular module object
 *
 * Examples
 *   <!--[selectmodobject module="AutoCustomer" objecttype="customer" id=4 assign="myCustomer"]-->
 *   <!--[selectmodobject module="AutoCocktails" objecttype="recipe" id=12 assign="myRecipe"]-->
 *
 * @param    module     string              name of the module storing the desired object
 * @param    objecttype string              name of object type
 * @param    id         int                 identifier of desired object
 * @param    prefix     string              optional prefix for class names (defaults to PN)
 * @param    assign     string              name of the returned object
 */
function smarty_function_selectmodobject($params, &$smarty)
{
    if (!isset($params['module']) || empty($params['module'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobject', 'module')));
    }
    if (!isset($params['objecttype']) || empty($params['objecttype'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobject', 'objecttype')));
    }
    if (!isset($params['id']) || empty($params['id']) || !is_numeric($params['id'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobject', 'id')));
    }
    if (!isset($params['prefix'])) {
        $params['prefix'] = 'PN';
    }
    if (!isset($params['assign']) || empty($params['assign'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobject', 'assign')));
    }
    if (!pnModAvailable($params['module'])) {
        $smarty->trigger_error(__f('Invalid %1$s passed to %2$s.', array('module', 'selectmodobject')));
    }

    pnModDBInfoLoad($params['module']);

    // load the object class corresponding to $params['objecttype']
    if (!($class = Loader::loadClassFromModule($params['module'], $params['objecttype'], false, false, $params['prefix']))) {
        pn_exit(__f('Unable to load class [%s] for module [%s]', array(DataUtil::formatForDisplay($params['objecttype']), DataUtil::formatForDisplay($params['module']))));
    }

    // intantiate object model
    $object = new $class();
    $idField = $object->getIDField();

    // assign object data
    // this performs a new database select operation
    // while the result will be saved within the object, we assign it to a local variable for convenience
    $objectData = $object->get(intval($params['id']), $idField);
    if (!is_array($objectData) || !isset($objectData[$idField]) || !is_numeric($objectData[$idField])) {
        $smarty->trigger_error(__('Sorry! No such item found.'));
    }

    $smarty->assign($params['assign'], $objectData);
}
