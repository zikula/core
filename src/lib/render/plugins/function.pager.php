<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * pnRender pager plugin
 *
 * Examples (see also the demo page)
 *   <!--[pager rowcount="400" limit="50"]-->
 *   <!--[pager rowcount="400" limit="35" template="pageritems.html"]-->
 *   <!--[pager rowcount="480" limit="90" template="pagerintervals.html" posvar="myposvar"]-->
 *   <!--[pager rowcount="500" limit="47" template="pagerimage.html"]-->
 *   <!--[pager rowcount="432" limit="25" template="pagercss.html"]-->
 *   <!--[pager rowcount="1200" limit="40" maxpages="10"]-->
 *   <!--[pager rowcount="1200" limit="40" template="pagercss.html" maxpages="7"]-->
 *   <!--[pager rowcount="1200" limit="40" template="pagerjs.html" maxpages="10"]-->
 *   <!--[pager rowcount="1200" limit="40" template="pagercss2.html" maxpages="20"]-->
 *   <!--[pager rowcount="1200" limit="40" template="pagercss2.html" maxpages="20" optimize=true]-->
 *
 * @param    string    $modname                 - fixed name of the module to page (optional)
 * @param    string    $type                    - fixed value of the type url parameter (optional)
 * @param    string    $func                    - fixed value of the function url parameter (optional)
 * @param    int       $rowcount                - total number of items to page in between
 *                                                (if an array is assigned, it's count will be used)
 * @param    int       $limit                   - number of items on a page (if <0 unlimited)
 * @param    string    $posvar                  - name of the variable that contains the position data, eg "offset"
 * @param    string    $template                - optional name of a template file
 * @param    string    $includeStylesheet       - use predefined stylesheet file? Default is yes.
 * @param    string    $anchorText              - optional text for hyperlink anchor (e.g. 'comments' for the anchor #comments) (default: '')
 * @param    string    $maxpages                - optional maximum number of displayed pages, others will be hidden / suppressed
 *                                                   (default: 0 = show all pages)
 * @param    string    $display                 - optional choice between 'page' or 'startnum'. Show links using page number or starting item number (default is startnum)
 * @param    string    $class                   - optional class to apply to the pager container (default : z-pager)
 * @param    bool      $processDetailLinks      - should the single page links be processed? (default: false if using pagerimage.html, otherwise true)
 * @param    bool      $optimize                - only deliver page links which are actually displayed to the template (default: false)
 */
function smarty_function_pager($params, &$smarty)
{
    if (!isset($params['rowcount'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pager', 'rowcount')));
    }

    if (!isset($params['limit'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pager', 'limit')));
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
        $params['optimize'] = false;
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

    $template = (isset($params['template'])) ? $params['template'] : 'pagerimage.html';
    $pager['includeStylesheet'] = isset($params['includeStylesheet']) ? $params['includeStylesheet'] : true;
    $anchorText = (isset($params['anchorText']) ? '#' . $params['anchorText'] : '');
    $pager['maxPages'] = (isset($params['maxpages']) ? $params['maxpages'] : 0);
    unset($params['template']);
    unset($params['anchorText']);
    unset($params['maxpages']);

    if (isset($params['modname'])) {
        $pager['module'] = $params['modname'];
    } else {
        $module = FormUtil::getPassedValue('module', null, 'GETPOST');
        $name   = FormUtil::getPassedValue('name', null, 'GETPOST');
        $pager['module'] = !empty($module) ? $module : $name;
    }

    $pager['func'] = isset($params['func']) ? $params['func'] : FormUtil::getPassedValue('func', 'main', 'GETPOST');
    $pager['type'] = isset($params['type']) ? $params['type'] : FormUtil::getPassedValue('type', 'user', 'GETPOST');

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
    foreach ($allVars as $k => $v)
    {
        if ($k != $pager['posvar'] && !is_null($v)) {
            switch ($k)
            {
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
                default:
                    if (is_array($v)) {
                        foreach ($v as $kk=>$vv) {
                            if (is_array($vv)) {
                                foreach ($vv as $kkk=>$vvv) {
                                    if (strlen($vvv)) {
                                        $tkey = $k . '[' . $kk . '][' . $kkk . ']';
                                        $pager['args'][$tkey] = $vvv;
                                    }
                                }
                            }
                            else if (strlen($vv)) {
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

        if ($leftMargin <= 1) {
            $rightMargin += abs($leftMargin);
            $leftMargin = 1;
        }
        if ($rightMargin >= ($pager['countPages'] - 1)) {
            $leftMargin -= abs($rightMargin - ($pager['countPages'] - 1));
            $rightMargin = ($pager['countPages'] - 1);
        }
    }

    $pager['processDetailLinks'] = isset($params['processDetailLinks']) ? (bool) $params['processDetailLinks'] : ($template != 'pagerimage.html');
    if ($pager['processDetailLinks']) {
        for ($currItem = 1; $currItem <= $pager['countPages']; $currItem++) {
            if ($pager['maxPages'] > 0 &&
                (($currItem < $leftMargin && $currItem > 1) || ($currItem > $rightMargin && $currItem <= $pager['countPages']))) {
                if ($pager['optimize']) {
                    continue;
                } else {
                    $pager['isVisible'] = false;
                }
            }

            $pager['pages'][$currItem]['pagenr'] = $currItem;
            $pager['pages'][$currItem]['url'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);
            $pager['pages'][$currItem]['isCurrentPage'] = ($pager['pages'][$currItem]['pagenr'] == $pager['currentPage']);

            if (!isset($pager['isVisible'])) {
                $pager['isVisible'] = true;
            }

            if ($params['display'] == 'page') {
                $pager['args'][$pager['posvar']] = $currItem;
            } else {
                $pager['args'][$pager['posvar']] = (($currItem - 1) * $pager['perpage']) + 1;
            }
        }
        unset($pager['args'][$pager['posvar']]);
    }

    // link to first & prev page
    $pager['first'] = DataUtil::formatForDisplay('1');
    $pager['args'][$pager['posvar']] = $pager['first'];
    $pager['firstUrl'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);
    if ($params['display'] == 'page') {
         $pager['prev'] = ($pager['currentPage'] - 1);
    } else {
         $pager['prev'] = ($pager['currentPage'] - 1) * $pager['perpage'] - $pager['perpage'] + $pager['first'];
    }
    $pager['args'][$pager['posvar']] = $pager['prev'];
    $pager['prevUrl'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);

    // link to next & last page
    if ($params['display'] == 'page') {
         $pager['next'] = $pager['currentPage'] + 1;
    } else {
         $pager['next'] = $pager['currentPage'] * $pager['perpage'] + 1;
    }
    $pager['args'][$pager['posvar']] = $pager['next'];
    $pager['nextUrl'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);
    if ($params['display'] == 'page') {
         $pager['last'] = $pager['countPages'];
    } else {
         $pager['last'] = $pager['countPages'] * $pager['perpage'] - $pager['perpage'] + 1;
    }
    $pager['args'][$pager['posvar']] = $pager['last'];
    $pager['lastUrl'] = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']) . $anchorText);

    $pager['itemStart'] = ($pager['currentPage'] * $pager['perpage']) - $pager['perpage'] + 1;
    $pager['itemEnd'] = $pager['itemStart'] + $pager['perpage'] - 1;
    if ($pager['itemEnd'] > $pager['total']) {
        $pager['itemEnd'] = $pager['total'];
    }

    $pnr = Renderer::getInstance('Theme');
    $pnr->cache_id = md5($template . '-' . $pager['firstUrl'] . '-' . $pager['prevUrl'] . $pager['nextUrl'] . '-' . $pager['lastUrl']);

    $pnr->assign('pagerPluginArray', $pager);
    $pnr->assign('hiddenPageBoxOpened', 0);
    $pnr->assign('hiddenPageBoxClosed', 0);

    return $pnr->fetch($template);
}
