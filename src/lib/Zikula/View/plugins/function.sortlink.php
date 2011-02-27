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
 * Zikula_View function to the module lists header links
 *
 * This function returns the sort link for one of the columns of a list.
 *
 *
 * Available parameters:
 *   - linktext: Text of the link
 *   - currentsort: Current column being sorted on the list
 *   - sort:     Column to sort with this link
 *   - sortdir:  Sort direction of the link (default: ASC)
 *   - assign:   If set, the results are assigned to the corresponding
 *               variable instead of printed out
 *   - modname:  Module name for the link
 *   - type:     Function type for the link (default: user)
 *   - func:     Function name for the link (default: main)
 *
 * Additional parameters will be passed to ModUtil::url directly.
 *
 * Example
 *   {sortlink __linktext='Column name' sort='colname' currentsort=$sort sortdir=$sortdir modname='ModName' type='admin' func='view'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The link output.
 */
function smarty_function_sortlink($params, Zikula_View $view)
{
    if (!isset($params['currentsort'])) {
        trigger_error(__f('Error! "%1$s" must be set in %2$s', array('currentsort', 'sortlink')));
    }
    if (!isset($params['sort'])) {
        trigger_error(__f('Error! "%1$s" must be set in %2$s', array('sort', 'sortlink')));
    }

    $modname = isset($params['modname']) ? $params['modname'] : $view->getTopLevelModule();
    $type    = isset($params['type']) ? $params['type'] : 'user';
    $func    = isset($params['func']) ? $params['func'] : 'main';

    $text    = isset($params['linktext']) ? $params['linktext'] : '&nbsp;';
    $sortdir = isset($params['sortdir']) ? strtoupper($params['sortdir']) : 'ASC';
    $assign  = isset($params['assign']) ? $params['assign'] : null;

    // defines the CSS class and revert the order for current field
    if ($params['currentsort'] == $params['sort']) {
        $cssclass = 'z-order-'.strtolower($sortdir);
        // reverse the direction
        $params['sortdir'] = ($sortdir == 'ASC') ? 'DESC' : 'ASC';
    } else {
        $cssclass = 'z-order-unsorted';
        // defaults the direction to ASC
        $params['sortdir'] = 'ASC';
    }

    // unset non link parameters
    $unsets = array('linktext', 'currentsort', 'assign', 'modname', 'type', 'func');
    foreach ($unsets as $unset) {
        unset($params[$unset]);
    }

    // build the link output
    $link = ModUtil::url($modname, $type, $func, $params);

    $output = '<a class="' . DataUtil::formatForDisplay($cssclass) . '" href="' . DataUtil::formatForDisplay($link) . '">' . DataUtil::formatForDisplay($text) . '</a>';

    if ($assign) {
        $view->assign($assign, $output);
    } else {
        return $output;
    }
}
