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
 * render plugin for fetching a list of module objects
 *
 * Examples
 *   {selectmodobjectarray module="AutoCustomer" objecttype="customer" assign="myCustomers"}
 *   {selectmodobjectarray module="AutoCocktails" objecttype="recipe" orderby="name desc" assign="myRecipes"}
 *   {selectmodobjectarray recordClass="AutoCocktails_Model_Recipe" orderby="name desc" assign="myRecipes"}
 *
 * Parameters:
 *  module      Name of the module storing the desired object (in DBObject mode)
 *  objecttype  Name of object type (in DBObject mode)
 *  recordClass Class name of an doctrine record. (in Doctrine mode)
 *  useArrays   true to fetch arrays and false to fetch objects (default is true) (in Doctrine mode)
 *  where       Filter value
 *  orderby     Sorting field and direction
 *  pos         Start offset
 *  num         Amount of selected objects
 *  prefix      Optional prefix for class names (defaults to PN) (in DBObject mode)
 *  assign      Name of the returned object
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return void
 */
function smarty_function_selectmodobjectarray($params, Zikula_View $view)
{
    if (isset($params['recordClass']) && !empty($params['recordClass'])) {
        $doctrineMode = true;
    } else {
        // DBObject checks
        if (!isset($params['module']) || empty($params['module'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['selectmodobjectarray', 'module']));
        }
        if (!isset($params['objecttype']) || empty($params['objecttype'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['selectmodobjectarray', 'objecttype']));
        }
        if (!isset($params['prefix'])) {
            $params['prefix'] = 'PN';
        }

        $doctrineMode = false;
    }

    if (!isset($params['assign'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['selectmodobjectarray', 'assign']));
    }

     // load object depending on mode: doctrine or dbobject
    if (!$doctrineMode) {
        if (!ModUtil::available($params['module'])) {
            $view->trigger_error(__f('Invalid %1$s passed to %2$s.', ['module', 'selectmodobjectarray']));
        }

        ModUtil::dbInfoLoad($params['module']);

        $class = "{$params['module']}_DBObject_".StringUtil::camelize($params['objecttype']).'Array';

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
        $objectData = $objectArray->get($where, $sort, $pos - 1, $num);
    } else {
        $query = Doctrine_Core::getTable($params['recordClass'])->createQuery();

        if (isset($params['where']) && !empty($params['where'])) {
            if (is_array($params['where'])) {
                $query->where($params['where'][0], $params['where'][1]);
            } else {
                $query->where($params['where']);
            }
        }

        if (isset($params['orderby']) && !empty($params['orderby'])) {
            $query->orderBy($params['orderby']);
        }

        $pos = 0;
        if (isset($params['pos']) && !empty($params['pos']) && is_numeric($params['pos'])) {
            $pos = $params['pos'];
        }

        $num = 10;
        if (isset($params['num']) && !empty($params['num']) && is_numeric($params['num'])) {
            $num = $params['num'];
        }

        $query->offset($pos);
        $query->limit($num);

        if (isset($params['useArrays']) && !$params['useArrays']) {
            $objectData = $query->execute();
        } else {
            $objectData = $query->fetchArray();
        }
    }

    $view->assign($params['assign'], $objectData);
}
