<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * render plugin for fetching a list of module objects
 *
 * Examples
 *   {selectmodobjectarray module="AutoCustomer" objecttype="customer" assign="myCustomers"}
 *   {selectmodobjectarray module="AutoCocktails" objecttype="recipe" orderby="name desc" assign="myRecipes"}
 *
 * Parameters:
 *  module     Name of the module storing the desired object
 *  objecttype Name of object type
 *  where      Filter value
 *  orderby    Sorting field and direction
 *  pos        Start offset
 *  num        Amount of selected objects
 *  prefix     Optional prefix for class names (defaults to PN)
 *  assign     Name of the returned object
 * 
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 * 
 * @return void
 */
function smarty_function_selectmodobjectarray($params, &$smarty)
{
    if (!isset($params['module']) || empty($params['module'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobjectarray', 'module')));
    }
    if (!isset($params['objecttype']) || empty($params['objecttype'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobjectarray', 'objecttype')));
    }
    if (!isset($params['prefix'])) {
        $params['prefix'] = 'PN';
    }
    if (!isset($params['assign'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobjectarray', 'assign')));
    }
    if (!ModUtil::available($params['module'])) {
        $smarty->trigger_error(__f('Invalid %1$s passed to %2$s.', array('module', 'selectmodobjectarray')));
    }

    ModUtil::dbInfoLoad($params['module']);

    $classname = "{$params['module']}_DBObject_".StringUtil::camelize($params['objecttype']).'Array';
    if (!class_exists($classname) && System::isLegacyMode()) {
        // BC check for PNObjectArray old style.
        // load the object class corresponding to $params['objecttype']
        if (!($class = Loader::loadArrayClassFromModule($params['module'], $params['objecttype'], false, $params['prefix']))) {
            z_exit(__f('Error! Cannot load module array class %1$s for module %2$s.', array(DataUtil::formatForDisplay($params['module']), DataUtil::formatForDisplay($params['objecttype']))));
        }
    }

    // instantiate the object-array
    $objectArray = new $class();

    // convenience vars to make code clearer
    $where = $sort = '';
    if (isset($params['where']) && !empty($params['where'])) {
        $where = $params['where'];
    }
    // TODO: add FilterUtil support here in 2.0

    if (isset($params['orderby']) && !empty($params['orderby'])) {
        $sort = $params['orderby'];
    }

    $pos = 1;
    if (isset($params['pos']) && !empty($params['pos']) && is_numeric($params['pos'])) {
        $pos = $params['pos'];
    }
    $num = 10;
    if (isset($params['num']) && !empty($params['num']) && is_numeric($params['num'])) {
        $num = $params['num'];
    }

    // get() returns the cached object fetched from the DB during object instantiation
    // get() with parameters always performs a new select
    // while the result will be saved in the object, we assign in to a local variable for convenience.
    $objectData = $objectArray->get($where, $sort, $pos-1, $num);

    $smarty->assign($params['assign'], $objectData);
}
