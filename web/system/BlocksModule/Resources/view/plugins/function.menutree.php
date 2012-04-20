<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to parse structured tree array to html unordered list
 *
 * This plugin is parsing list in two modes. Default generate simple unordered list.
 * Second, called below as "extend" return list where elements are described by following class names:
 *  - first:        li element is first in node
 *  - last:         li element is last in node
 *  - single:       li element is the only element in node
 *  - parent:       li element has children
 *  - childless:    li element has not children
 *  - level:        ul element nesting level, starting from 1
 * This is supposed to help in formatting list by css
 *
 * Available parameters:
 *   - data:        array with tree data (array, required)
 *   - id:          id for main UL element (string, optional)
 *   - class:       class for main UL element (string, optional)
 *   - nodeprefix:  prefix LI elements id (if empty - LI element wont have ids) (string, optional)
 *   - classprefix: if provided - LI elements will get class with this prefix and node id (string, optional)
 *   - ext:         extended parsing (bool, optional)
 *   - extopt:      allow to overwrite default options for extended parsing,
 *                  this params have to be given in this order:
 *                  'first,last,single,parent,childless,level'
 *                  (string - coma separated list of values, optional)
 *   - assign:      if set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {menutree data=$menutree_content id='listid' class='menutree'}
 *
 * will generate unordered list with id "listid" and class "menutree"
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $smarty     Reference to the Smarty object
 * @return       string      unordered html list
 */

function smarty_function_menutree($params, $smarty)
{
    $treeArray          = isset($params['data'])       ? $params['data'] : '';
    $treeId             = isset($params['id'])         ? $params['id'] : '';
    $treeClass          = isset($params['class'])      ? $params['class'] : '';
    $treeNodePrefix     = isset($params['nodeprefix']) ? $params['nodeprefix'] : '';
    $treeClassPrefix    = isset($params['classprefix']) ? $params['classprefix'] : '';
    $extended           = isset($params['ext'])        ? $params['ext'] : false;
    $extendedOpt        = isset($params['extopt'])     ? $params['extopt'] : '';
    if ($extended) {
        $ext_tmp = explode(',', $extendedOpt);
        $ext = array();
        $ext['first']       = !empty($ext_tmp[0]) ? $ext_tmp[0] : 'first';
        $ext['last']        = !empty($ext_tmp[1]) ? $ext_tmp[1] : 'last';
        $ext['single']      = !empty($ext_tmp[2]) ? $ext_tmp[2] : 'single';
        $ext['parent']      = !empty($ext_tmp[3]) ? $ext_tmp[3] : 'parent';
        $ext['childless']   = !empty($ext_tmp[4]) ? $ext_tmp[4] : 'childless';
        $ext['level']       = !empty($ext_tmp[5]) ? $ext_tmp[5] : 'level';
        $depth = 1;
        $html = _htmlListExt($treeArray,$treeNodePrefix,$treeClassPrefix,$ext,$depth,$treeId,$treeClass);
    } else {
        $html = _htmlList($treeArray,$treeNodePrefix,$treeClassPrefix,$treeId,$treeClass);
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $html);
    } else {
        return $html;
    }

}

function _htmlList($tree,$treeNodePrefix,$treeClassPrefix,$treeId = '',$treeClass = '')
{
    $html = '<ul';
    $html .= !empty($treeId) ? ' id="'.$treeId.'"' : '';
    $html .= !empty($treeClass) ? ' class="'.$treeClass.'"' : '';
    $html .= '>';

    foreach ($tree as $tab) {
        $html .= '<li';
        $html .= !empty($treeNodePrefix) ? ' id="'.$treeNodePrefix.$tab['item']['id'].'"' : '';
        $html .= !empty($treeClassPrefix) ? ' class="'.$treeClassPrefix.$tab['item']['id'].'"' : '';
        $html .= '>';
        $attr  = !empty($tab['item']['title']) ? ' title="'.$tab['item']['title'].'"' : '';
        $attr .= !empty($tab['item']['class']) ? ' class="'.$tab['item']['class'].'"' : '';
        if(!empty($tab['item']['href'])) {
            $html .= '<a href="'.DataUtil::formatForDisplay($tab['item']['href']).'"'.$attr.'>'.$tab['item']['name'].'</a>';
        } else {
            $html .= '<span'.$attr.'>'.$tab['item']['name'].'</span>';
        }
        $html .= !empty($tab['nodes']) ? _htmlList($tab['nodes'],$treeNodePrefix,$treeClassPrefix) : '';
        $html .= '</li>';
    }

    $html .= '</ul>';

    return $html;
}
function _htmlListExt($tree,$treeNodePrefix,$treeClassPrefix,$ext,$depth,$treeId = '',$treeClass = '')
{
    $html = '<ul';
    $html .= !empty($treeId) ? ' id="'.$treeId.'"' : '';
    $html .= !empty($treeClass) ? ' class="'.$treeClass.' '.$ext['level'].$depth.'"' : ' class="'.$ext['level'].$depth.'"';
    $html .= '>';

    $size = count($tree);
    $i = 1;
    foreach ($tree as $tab) {
        $class = array();
        $class[] = $size == 1 ? $ext['single'] : '';
        $class[] = ($i == 1 && $size > 1) ? $ext['first'] : '';
        $class[] = ($i == $size && $size > 1) ? $ext['last'] : '';
        $class[] = !empty($tab['nodes']) ? $ext['parent'] : $ext['childless'];
        $class[] = !empty($treeClassPrefix) ? $treeClassPrefix.$tab['item']['id'] : '';
        $class = trim(implode(' ', $class));
        $i++;

        $html .= '<li';
        $html .= !empty($treeNodePrefix) ? ' id="'.$treeNodePrefix.$tab['item']['id'].'"' : '';
        $html .= ' class="'.$class.'">';
        $attr  = !empty($tab['item']['title']) ? ' title="'.$tab['item']['title'].'"' : '';
        $attr .= !empty($tab['item']['class']) ? ' class="'.$tab['item']['class'].'"' : '';
        if(!empty($tab['item']['href'])) {
            $html .= '<a href="'.DataUtil::formatForDisplay($tab['item']['href']).'"'.$attr.'>'.$tab['item']['name'].'</a>';
        } else {
            $html .= '<span'.$attr.'>'.$tab['item']['name'].'</span>';
        }
        $html .= !empty($tab['nodes']) ? _htmlListExt($tab['nodes'],$treeNodePrefix,$treeClassPrefix,$ext,$depth+1) : '';
        $html .= '</li>';

    }

    $html .= '</ul>';

    return $html;
}
