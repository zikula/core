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
 *  route              Name of a fixed route to use (optional, replaces modname / type / func)
 *  rowcount           Total number of items to page in between
 *                       (if an array is assigned, it's count will be used)
 *  limit              Number of items on a page (if <0 unlimited)
 *  posvar             Name of the variable that contains the position data, eg "offset"
 *  owner              If set uses it as the module owner of the Zikula_View instance. Default owner is the Theme module
 *  template           Optional name of a template file
 *  includeStylesheet  Use predefined stylesheet file? Default is yes.
 *  anchorText         Optional text for hyperlink anchor (e.g. 'comments' for the anchor #comments) (default: '')
 *  maxpages           Optional maximum number of displayed pages, others will be hidden / suppressed
 *                       (default: 15 = show only 15 pages)
 *  display            Optional choice between 'page' or 'startnum'. Show links using page number or starting item number (default is startnum)
 *  class              Optional class to apply to the pager container (default : z-pager)
 *  processDetailLinks Should the single page links be processed? (default: false if using pagerimage.tpl, otherwise true)
 *  processUrls        Should urls be processed or assign the arguments? (default: true)
 *  optimize           Only deliver page links which are actually displayed to the template (default: true)
 *  includePostVars    Whether or not to include the POST variables as GET variables in the pager URLs (default: true)
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string
 */
function smarty_function_pager($params, Zikula_View $view)
{
    if (!isset($params['rowcount'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['pager', 'rowcount']));
    }

    if (!isset($params['limit'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['pager', 'limit']));
    }

    if (is_array($params['rowcount'])) {
        $params['rowcount'] = count($params['rowcount']);
    } elseif (0 == $params['rowcount']) {
        return '';
    }

    if (0 == $params['limit']) {
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

    if (!isset($params['includePostVars'])) {
        $params['includePostVars'] = true;
    }

    if (!isset($params['route'])) {
        $params['route'] = false;
    }

    $pager = [];
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

    $routeParams = [];
    if ($view->getRequest()->attributes->has('_route_params')) {
        $routeParams = $view->getRequest()->attributes->get('_route_params');
        if (isset($routeParams[$pager['posvar']])) {
            $pager['pos'] = (int)($routeParams[$pager['posvar']]);
        } else {
            $pager['pos'] = (int)$view->getRequest()->query->get($pager['posvar'], '');
        }
    } else {
        $pager['pos'] = (int)$view->getRequest()->query->get($pager['posvar'], '');
    }
    if ('page' == $params['display']) {
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
    $pager['maxPages'] = (isset($params['maxpages']) ? $params['maxpages'] : 15);
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

    $pager['func'] = isset($params['func']) ? $params['func'] : FormUtil::getPassedValue('func', 'index', 'GETPOST', FILTER_SANITIZE_STRING);
    $pager['type'] = isset($params['type']) ? $params['type'] : FormUtil::getPassedValue('type', 'user', 'GETPOST', FILTER_SANITIZE_STRING);

    $pager['route'] = $params['route'];

    $pager['args'] = [];
    if (empty($pager['module'])) {
        $pager['module'] = System::getVar('startpage');
        $starttype = System::getVar('starttype');
        $pager['type'] = !empty($starttype) ? $starttype : 'user';
        $startfunc = System::getVar('startfunc');
        $pager['func'] = !empty($startfunc) ? $startfunc : 'index';

        $startargs   = explode(',', System::getVar('startargs'));
        foreach ($startargs as $arg) {
            if (!empty($arg)) {
                $argument = explode('=', $arg);
                $pager['args'][$argument[0]] = $argument[1];
            }
        }
    }

    //also $_POST vars have to be considered, i.e. for search results
    $allVars = ($params['includePostVars']) ? array_merge($_POST, $_GET, $routeParams) : array_merge($_GET, $routeParams);
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
                    if (0 == $addcurrentlang2url) {
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
    unset($params['route']);

    $pagerUrl = function ($pager) use ($view) {
        if (!$pager['route']) {
            return ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']);
        }

        return $view->getContainer()->get('router')->generate($pager['route'], $pager['args']);
    };

    // build links to items / pages
    // entries are marked as current or displayed / hidden
    $pager['pages'] = [];
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
    $params['processDetailLinks'] = isset($params['processDetailLinks']) ? (bool)$params['processDetailLinks'] : ('pagerimage.tpl' != $template);
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

            if ('page' == $params['display']) {
                $pager['args'][$pager['posvar']] = $currItem;
            } else {
                $pager['args'][$pager['posvar']] = (($currItem - 1) * $pager['perpage']) + 1;
            }

            $pager['pages'][$currItem]['pagenr'] = $currItem;
            $pager['pages'][$currItem]['isCurrentPage'] = ($pager['pages'][$currItem]['pagenr'] == $pager['currentPage']);
            $pager['pages'][$currItem]['isVisible'] = $currItemVisible;

            if ($params['processUrls']) {
                $pager['pages'][$currItem]['url'] = DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
            } else {
                $pager['pages'][$currItem]['url'] = [
                    'module' => $pager['module'],
                    'type' => $pager['type'],
                    'func' => $pager['func'],
                    'args' => $pager['args'],
                    'fragment' => $anchorText
                ];
            }
        }
        unset($pager['args'][$pager['posvar']]);
    }

    // link to first & prev page
    $pager['args'][$pager['posvar']] = $pager['first'] = '1';
    if ($params['processUrls']) {
        $pager['firstUrl'] = DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
    } else {
        $pager['firstUrl'] = [
            'module' => $pager['module'],
            'type' => $pager['type'],
            'func' => $pager['func'],
            'args' => $pager['args'],
            'fragment' => $anchorText
        ];
    }

    if ('page' == $params['display']) {
        $pager['prev'] = ($pager['currentPage'] - 1);
    } else {
        $pager['prev'] = ($leftMargin - 1) * $pager['perpage'] - $pager['perpage'] + $pager['first'];
    }
    $pager['args'][$pager['posvar']] = ($pager['prev'] > 1) ? $pager['prev'] : 1;
    if ($params['processUrls']) {
        $pager['prevUrl'] = DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
    } else {
        $pager['prevUrl'] = [
            'module' => $pager['module'],
            'type' => $pager['type'],
            'func' => $pager['func'],
            'args' => $pager['args'],
            'fragment' => $anchorText
        ];
    }

    // link to next & last page
    if ('page' == $params['display']) {
        $pager['next'] = $pager['currentPage'] + 1;
    } else {
        $pager['next'] = $rightMargin * $pager['perpage'] + 1;
    }
    $pager['args'][$pager['posvar']] = ($pager['next'] < $pager['total']) ? $pager['next'] : $pager['next'] - $pager['perpage'];
    if ($params['processUrls']) {
        $pager['nextUrl'] = DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
    } else {
        $pager['nextUrl'] = [
            'module' => $pager['module'],
            'type' => $pager['type'],
            'func' => $pager['func'],
            'args' => $pager['args'],
            'fragment' => $anchorText
        ];
    }

    if ('page' == $params['display']) {
        $pager['last'] = $pager['countPages'];
    } else {
        $pager['last'] = $pager['countPages'] * $pager['perpage'] - $pager['perpage'] + 1;
    }
    $pager['args'][$pager['posvar']] = $pager['last'];
    if ($params['processUrls']) {
        $pager['lastUrl'] = DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
    } else {
        $pager['lastUrl'] = [
            'module' => $pager['module'],
            'type' => $pager['type'],
            'func' => $pager['func'],
            'args' => $pager['args'],
            'fragment' => $anchorText
        ];
    }

    $pager['itemStart'] = ($pager['currentPage'] * $pager['perpage']) - $pager['perpage'] + 1;
    $pager['itemEnd'] = $pager['itemStart'] + $pager['perpage'] - 1;
    if ($pager['itemEnd'] > $pager['total']) {
        $pager['itemEnd'] = $pager['total'];
    }

    $modview = $params['owner'] && ModUtil::available($params['owner']) ? $params['owner'] : 'ZikulaThemeModule';

    $renderer = Zikula_View::getInstance($modview);
    $renderer->setCaching(Zikula_View::CACHE_DISABLED);

    $renderer->assign('pagerPluginArray', $pager);
    $renderer->assign('hiddenPageBoxOpened', 0);
    $renderer->assign('hiddenPageBoxClosed', 0);

    return $renderer->fetch($template);
}
