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
 *   - route:   the routename
 *
 * Additional parameters will be passed to ModUtil::url directly.
 *
 * Example
 *   {sortlink __linktext='Column name' sort='colname' currentsort=$sort sortdir=$sortdir modname='ModName' type='admin' func='view'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The link output
 */
function smarty_function_sortlink($params, Zikula_View $view)
{
    if (!isset($params['currentsort'])) {
        trigger_error(__f('Error! "%1$s" must be set in %2$s', ['currentsort', 'sortlink']));
    }
    if (!isset($params['sort'])) {
        trigger_error(__f('Error! "%1$s" must be set in %2$s', ['sort', 'sortlink']));
    }

    $modname = isset($params['modname']) ? $params['modname'] : $view->getTopLevelModule();
    $type    = isset($params['type']) ? $params['type'] : 'user';
    $func    = isset($params['func']) ? $params['func'] : 'index';
    $route   = isset($params['route']) ? $params['route'] : null;

    $text    = isset($params['linktext']) ? $params['linktext'] : '&nbsp;';
    $sortdir = isset($params['sortdir']) ? strtoupper($params['sortdir']) : 'ASC';
    $assign  = isset($params['assign']) ? $params['assign'] : null;

    // defines the CSS class and revert the order for current field
    if ($params['currentsort'] == $params['sort']) {
        $cssclass = 'z-order-'.strtolower($sortdir);
        // reverse the direction
        $params['sortdir'] = ('ASC' == $sortdir) ? 'DESC' : 'ASC';
    } else {
        $cssclass = 'z-order-unsorted';
        // defaults the direction to ASC
        $params['sortdir'] = 'ASC';
    }

    // unset non link parameters
    $unsets = ['linktext', 'currentsort', 'assign', 'modname', 'type', 'func', 'route'];
    foreach ($unsets as $unset) {
        unset($params[$unset]);
    }

    // build the link output
    if (!empty($route)) {
        $link = $view->getContainer()->get('router')->generate($route, $params);
    } else {
        $link = ModUtil::url($modname, $type, $func, $params);
    }

    $output = '<a class="' . DataUtil::formatForDisplay($cssclass) . '" href="' . DataUtil::formatForDisplay($link) . '">' . DataUtil::formatForDisplay($text) . '</a>';

    if ($assign) {
        $view->assign($assign, $output);
    } else {
        return $output;
    }
}
