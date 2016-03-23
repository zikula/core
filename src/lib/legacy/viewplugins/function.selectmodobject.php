<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * render plugin for fetching a particular module object
 *
 * Examples
 *   {selectmodobject module="AutoCustomer" objecttype="customer" id=4 assign="myCustomer"}
 *   {selectmodobject module="AutoCocktails" objecttype="recipe" id=12 assign="myRecipe"}
 *   {selectmodobject recordClass="AutoCocktails_Model_Recipe" id=12 assign="myRecipe"}
 *
 * Parameters:
 *  module      Name of the module storing the desired object (in DBObject mode)
 *  objecttype  Name of object type (in DBObject mode)
 *  recordClass Class name of an doctrine record. (in Doctrine mode)
 *  id          Identifier of desired object
 *  prefix      Optional prefix for class names (defaults to PN) (in DBObject mode)
 *  assign      Name of the returned object
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return void
 */
function smarty_function_selectmodobject($params, Zikula_View $view)
{
    if (isset($params['recordClass']) && !empty($params['recordClass'])) {
        $doctrineMode = true;
    } else {
        // DBObject checks

        if (!isset($params['module']) || empty($params['module'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobject', 'module')));
        }
        if (!isset($params['objecttype']) || empty($params['objecttype'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobject', 'objecttype')));
        }
        if (!isset($params['prefix'])) {
            $params['prefix'] = 'PN';
        }

        $doctrineMode = false;
    }

    if (!isset($params['id']) || empty($params['id']) || !is_numeric($params['id'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobject', 'id')));
    }

    if (!isset($params['assign']) || empty($params['assign'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('selectmodobject', 'assign')));
    }

    // load object depending on mode: doctrine or dbobject
    if (!$doctrineMode) {
        if (!ModUtil::available($params['module'])) {
            $view->trigger_error(__f('Invalid %1$s passed to %2$s.', array('module', 'selectmodobject')));
        }

        ModUtil::dbInfoLoad($params['module']);

        $class = "{$params['module']}_DBObject_".StringUtil::camelize($params['objecttype']);

        // intantiate object model
        $object = new $class();
        $idField = $object->getIDField();

        // assign object data
        // this performs a new database select operation
        // while the result will be saved within the object, we assign it to a local variable for convenience
        $objectData = $object->get(intval($params['id']), $idField);
        if (!is_array($objectData) || !isset($objectData[$idField]) || !is_numeric($objectData[$idField])) {
            $view->trigger_error(__('Sorry! No such item found.'));
        }
    } else {
        if ($params['recordClass'] instanceof \Doctrine_Record) {
            $objectData = Doctrine_Core::getTable($params['recordClass'])->find($params['id']);
            if ($objectData === false) {
                $view->trigger_error(__('Sorry! No such item found.'));
            }
        } else {
            /** @var $em Doctrine\ORM\EntityManager */
            $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');
            $result = $em->getRepository($params['recordClass'])->find($params['id']);
            $objectData = $result->toArray();
        }
    }

    $view->assign($params['assign'], $objectData);
}
