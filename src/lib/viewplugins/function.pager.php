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
 * Zikula_View pager plugin
 *
 * Examples (see also the demo page)
 *   {pager rowcount="400" limit="50"}
 *   {pager rowcount="400" limit="35" template="pageritems.tpl"}
 *   {pager rowcount="480" limit="90" template="pagerintervals.tpl" posvar="myposvar"}
 *   {pager rowcount="500" limit="47" template="pagerimage.tpl"}
 *   {pager rowcount="432" limit="25" template="pagercss.tpl"}
 *   {pager rowcount="1200" limit="40" maxpages="10"}
 *   {pager rowcount="1200" limit="40" template="pagercss.tpl" maxpages="7"}
 *   {pager rowcount="1200" limit="40" template="pagerjs.tpl" maxpages="10"}
 *   {pager rowcount="1200" limit="40" template="pagercss2.tpl" maxpages="20"}
 *   {pager rowcount="1200" limit="40" template="pagercss2.tpl" maxpages="20" optimize=true}
 *
 * Available parameters:
 *  modname            Fixed name of the module to page (optional)
 *  type               Fixed value of the type url parameter (optional)
 *  func               Fixed value of the function url parameter (optional)
 *  rowcount           Total number of items to page in between
 *                       (if an array is assigned, it's count will be used)
 *  limit              Number of items on a page (if <0 unlimited)
 *  posvar             Name of the variable that contains the position data, eg "offset"
 *  owner              If set uses it as the module owner of the Zikula_View instance. Default owner is the Theme module
 *  template           Optional name of a template file
 *  includeStylesheet  Use predefined stylesheet file? Default is yes.
 *  anchorText         Optional text for hyperlink anchor (e.g. 'comments' for the anchor #comments) (default: '')
 *  maxpages           Optional maximum number of displayed pages, others will be hidden / suppressed
 *                       (default: 0 = show all pages)
 *  display            Optional choice between 'page' or 'startnum'. Show links using page number or starting item number (default is startnum)
 *  class              Optional class to apply to the pager container (default : z-pager)
 *  processDetailLinks Should the single page links be processed? (default: false if using pagerimage.tpl, otherwise true)
 *  processUrls        Should urls be processed or assign the arguments? (default: true)
 *  optimize           Only deliver page links which are actually displayed to the template (default: true)
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_pager($params, Zikula_View $view)
{
    if (!isset($params['rowcount'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pager', 'rowcount')));
    }

    if (!isset($params['limit'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pager', 'limit')));
    }

    if (is_array($params['rowcount'])) {
        $params['rowcount'] = count($params['rowcount']);
    } elseif ($params['rowcount'] == 0) {
        return '';
    }

    if ($params['limit'] == 0) {
        $params['limit'] = 5;
    }

    if (!isset($params['display'])) {
        $params['display'] = 'startnum';
    }

    if (!isset($params['class'])) {
        $params['class'] = 'z-pager';
    }

    if (!isset($params['optimize'])) {
        $params['optimize'] = true;
    }

    if (!isset($params['owner'])) {
        $params['owner'] = false;
    }

    $pager = array();
    $pager['total']    = $params['rowcount'];
    $pager['perpage']  = $params['limit'];
    $pager['class']    = $params['class'];
    $pager['optimize'] = $params['optimize'];
    unset($params['rowcount']);
    unset($params['limit']);
    unset($params['class']);
    unset($params['optimize']);

    // current position
    $pager['posvar'] = (isset($params['posvar']) ? $params['posvar'] : 'pos');
    $pager['pos'] = (int)FormUtil::getPassedValue($pager['posvar'], '', 'GET');
    if ($params['display'] == 'page') {
        $pager['pos'] = $pager['pos'] * $pager['perpage'];
        $pager['increment'] = 1;
    } else {
        $pager['increment'] = $pager['perpage'];
    }

    if ($pager['pos'] < 1) {
        $pager['pos'] = 1;
    }
    if ($pager['pos'] > $pager['total']) {
        $pager['pos'] = $pager['total'];
    }
    unset($params['posvar']);

    // number of pages
    $pager['countPages'] = (isset($pager['total']) && $pager['total'] > 0 ? ceil($pager['total'] / $pager['perpage']) : 1);
    if ($pager['countPages'] < 2) {
        return '';
    }

    // current page
    $pager['currentPage'] = ceil($pager['pos'] / $pager['perpage']);
    if ($pager['currentPage'] > $pager['countPages']) {
        $pager['currentPage'] = $pager['countPages'];
    }

    $template = (isset($params['template'])) ? $params['template'] : 'pagercss.tpl';
    $pager['includeStylesheet'] = isset($params['includeStylesheet']) ? $params['includeStylesheet'] : true;
    $anchorText = (isset($params['anchorText']) ? '#' . $params['anchorText'] : '');
    $pager['maxPages'] = (isset($params['maxpages']) ? $params['maxpages'] : 0);
    unset($params['template']);
    unset($params['anchorText']);
    unset($params['maxpages']);

    if (isset($params['modname'])) {
        $pager['module'] = $params['modname'];
    } else {
        $module = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $name   = FormUtil::getPassedValue('name', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $pager['module'] = !empty($module) ? $module : $name;
    }

    $pager['func'] = isset($params['func']) ? $params['func'] : FormUtil::getPassedValue('func', 'main', 'GETPOST', FILTER_SANITIZE_STRING);
    $pager['type'] = isset($params['type']) ? $params['type'] : FormUtil::getPassedValue('type', 'user', 'GETPOST', FILTER_SANITIZE_STRING);

    $pager['args'] = array();
    if (empty($pager['module'])) {
        $pager['module'] = System::getVar('startpage');
        $starttype = System::getVar('starttype');
        $pager['type'] = !empty($starttype) ? $starttype : 'user';
        $startfunc = System::getVar('startfunc');
        $pager['func'] = !empty($startfunc) ? $startfunc : 'main';

        $startargs   = explode(',', System::getVar('startargs'));
        foreach ($startargs as $arg) {
            if (!empty($arg)) {
                $argument = explode('=', $arg);
                $pager['args'][$argument[0]] = $argument[1];
            }
        }
    }

    //also $_POST vars have to be considered, i.e. for search results
    $allVars = array_merge($_POST, $_GET);
    foreach ($allVars as $k => $v) {
        if ($k != $pager['posvar'] && !is_null($v)) {
            switch ($k) {
                case 'module':
                    if (!isset($params['modname'])) {
                        $pager['module'] = $v;
                    }
                    break;
                case 'func':
                    if (!isset($params['func'])) {
                        $pager['func'] = $v;
                    }
                    break;
                case 'type':
                    if (!isset($params['type'])) {
                        $pager['type'] = $v;
                    }
                    break;
                case 'lang':
                    $addcurrentlang2url = System::getVar('languageurl');
                    if ($addcurrentlang2url == 0) {
                        $pager['args'][$k] =  $v;
                    }
                    break;
                default:
                    if (is_array($v)) {
                        foreach ($v as $kk => $vv) {
                            if (is_array($vv)) {
                                foreach ($vv as $kkk => $vvv) {
                                    if (is_array($vvv)) {
                                        foreach ($vvv as $kkkk => $vvvv) {
                                            if (strlen($vvvv)) {
                                                $tkey = $k . '[' . $kk . '][' . $kkk . '][' . $kkkk . ']';
                                                $pager['args'][$tkey] = $vvvv;
                                            }
                                        }
                                    } elseif (strlen($vvv)) {
                                        $tkey = $k . '[' . $kk . '][' . $kkk . ']';
                                        $pager['args'][$tkey] = $vvv;
                                    }
                                }
                            } elseif (strlen($vv)) {
                                $tkey = $k . '[' . $kk . ']';
                                $pager['args'][$tkey] =  $vv;
                            }
                        }
                    } else {
                        if (strlen($v)) {
                            $pager['args'][$k] =  $v;
                        }
                    }
            }
        }
    }

    unset($params['modname']);
    unset($params['type']);
    unset($params['func']);

    // build links to items / pages
    // entries are marked as current or displayed / hidden
    $pager['pages'] = array();
    if ($pager['maxPages'] > 0) {
        $pageInterval = floor($pager['maxPages'] / 2);

        $leftMargin = $pager['currentPage'] - $pageInterval;
        $rightMargin = $pager['currentPage'] + $pageInterval;

        if ($leftMargin < 1) {
            $rightMargin += abs($leftMargin) + 1;
            $leftMargin = 1;
        }
        if ($rightMargin > $pager['countPages']) {
            $leftMargin -= $rightMargin - $pager['countPages'];
            $rightMargin = $pager['countPages'];
        }
    }

    $params['processUrls']        = isset($params['processUrls']) ? (bool)$params['processUrls'] : true;
    $params['processDetailLinks'] = isset($params['processDetailLinks']) ? (bool)$params['processDetailLinks'] : ($template != 'pagerimage.tpl');
    if ($params['processDetailLinks']) {
        for ($currItem = 1; $currItem <= $pager['countPages']; $currItem++) {
            $currItemVisible = true;

            if ($pager['maxPages'] > 0 &&
                //(($currItem < $leftMargin && $currItem > 1) || ($currItem > $rightMargin && $currItem <= $pager['countPages']))) {
                (($currItem < $leftMargin) || ($currItem > $rightMargin))) {

                if ($pager['optimize']) {
                    continue;
                } else {
                    $currItemVisible = false;
                }
            }

            if ($params['display'] == 'page') {
                $pager['args'][$pager['posvar']] = $currItem;
            } else {
                $pager['args'][$pager['posvar']] = (($currItem - 1) * $pager['perpage']) + 1;
            }

            $pager['pages'][$currItem]['pagenr'] = $currItem;
            $pager['pages'][$currItem]['isCurrentPage'] = ($pager['pages'][$currItem]['pagenr'] == $pager['currentPage']);
            $pager['pages'][$currItem]['isVisible'] = $currItemVisible;

            if ($params['processUrls']) {
                $pager['pages'][$currItem]['url'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);
            } else {
                $pager['pages'][$currItem]['url'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
            }
        }
        unset($pager['args'][$pager['posvar']]);
    }

    // link to first & prev page
    $pager['args'][$pager['posvar']] = $pager['first'] = '1';
    if ($params['processUrls']) {
        $pager['firstUrl'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);
    } else {
        $pager['firstUrl'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
    }

    if ($params['display'] == 'page') {
         $pager['prev'] = ($pager['currentPage'] - 1);
    } else {
         $pager['prev'] = ($pager['currentPage'] - 1) * $pager['perpage'] - $pager['perpage'] + $pager['first'];
    }
    $pager['args'][$pager['posvar']] = $pager['prev'];
    if ($params['processUrls']) {
        $pager['prevUrl'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);
    } else {
        $pager['prevUrl'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
    }

    // link to next & last page
    if ($params['display'] == 'page') {
         $pager['next'] = $pager['currentPage'] + 1;
    } else {
         $pager['next'] = $pager['currentPage'] * $pager['perpage'] + 1;
    }
    $pager['args'][$pager['posvar']] = $pager['next'];
    if ($params['processUrls']) {
        $pager['nextUrl'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);
    } else {
        $pager['nextUrl'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
    }

    if ($params['display'] == 'page') {
        $pager['last'] = $pager['countPages'];
    } else {
        $pager['last'] = $pager['countPages'] * $pager['perpage'] - $pager['perpage'] + 1;
    }
    $pager['args'][$pager['posvar']] = $pager['last'];
    if ($params['processUrls']) {
        $pager['lastUrl'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);
    } else {
        $pager['lastUrl'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
    }

    $pager['itemStart'] = ($pager['currentPage'] * $pager['perpage']) - $pager['perpage'] + 1;
    $pager['itemEnd'] = $pager['itemStart'] + $pager['perpage'] - 1;
    if ($pager['itemEnd'] > $pager['total']) {
        $pager['itemEnd'] = $pager['total'];
    }

    $modview = $params['owner'] && ModUtil::available($params['owner']) ? $params['owner'] : 'Theme';

    $renderer = Zikula_View::getInstance($modview);
    $renderer->setCaching(Zikula_View::CACHE_DISABLED);

    $renderer->assign('pagerPluginArray', $pager);
    $renderer->assign('hiddenPageBoxOpened', 0);
    $renderer->assign('hiddenPageBoxClosed', 0);

    return $renderer->fetch($template);
}
