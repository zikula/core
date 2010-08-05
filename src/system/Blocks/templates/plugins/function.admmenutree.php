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
 * This plugin is parsing list in admin mode - all menu langs are outputed.
 *
 * Available parameters:
 *   - data:        array with tree data (array, required)
 *   - id:          id for main UL element (string, optional)
 *   - class:       class for main UL element (string, optional)
 *   - nodeprefix:  prefix LI elements id (if empty - LI element wont have ids) (string, optional)
 *   - assign:      if set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   <!--[menutree data=$menutree_content id='listid' class='menutree']-->
 *
 * will generate unordered list with id "listid" and class "menutree"
 *
 *
 * @author       Jusuff
 * @since        28/07/2005
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $smarty     Reference to the Smarty object
 * @return       string      unordered html list
 */

function smarty_function_admmenutree($params, $smarty)
{
    $treeArray      = isset($params['data'])       ? $params['data'] : '';
    $treeId         = isset($params['id'])         ? $params['id'] : '';
    $treeClass      = isset($params['class'])      ? $params['class'] : '';
    $treeNodePrefix = isset($params['nodeprefix']) ? $params['nodeprefix'] : '';

    $html = _admHtmlList($treeArray,$treeNodePrefix,$treeId,$treeClass);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $html);
    } else {
        return $html;
    }

}

function _admHtmlList($tree,$treeNodePrefix,$treeId = '',$treeClass = '')
{
    $liHtml = array();
    foreach ($tree as $id => $tab) {
        $isDynamic = false;
        $links = array();
        foreach((array)$tab['item'] as $lang => $item) {
            $item = $tab['item'][$lang];
            $isDynamic = $item['dynamic'];

            $class = $item['state'] == 1 ? $item['className'] : 'unactive '.$item['className'];
            $linkClass = !empty($class) ? ' class="'.$class.'"' : '';
            $linkHref = 'href="'.DataUtil::formatForDisplay($item['href']).'"';
            $linkLang = 'lang="'.$item['lang'].'"';
            $linkTitle = !empty($item['title']) ? ' title="'.$item['title'].'"' : '';

            $links[] = "<a {$linkHref} {$linkLang} {$linkTitle} {$linkClass}>{$item['name']}</a>";
        }
        $subhtml = !empty($tab['nodes']) ? _admHtmlList($tab['nodes'],$treeNodePrefix) : '';

        $liId = !empty($treeNodePrefix) ? ' id="'.$treeNodePrefix.$id.'"' : '';
        $liClass = $isDynamic ? 'class="dynamic"' : '';
        $links = implode('',$links);
        $liHtml[] = "<li {$liId} {$liClass}>{$links}{$subhtml}</li>";
    }

    $ulID = !empty($treeId) ? ' id="'.$treeId.'"' : '';
    $ulClass = !empty($treeClass) ? ' class="'.$treeClass.'"' : '';
    $liHtml = implode('',$liHtml);
    $html = "<ul {$ulID} {$ulClass}>{$liHtml}</ul>";

    return $html;
}
