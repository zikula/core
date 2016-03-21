<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class PagerExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pager', [$this, 'pager'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @TODO SIMPLIFY THIS AND REMOVE ALL LEGACY!
     * template pager plugin
     *
     *   {{ pager({rowcount:pager.numitems, limit:pager.itemsperpage, posvar:'startnum', route:'zikulapagesmodule_admin_index', template:'pager.html.twig'}) }}
     *
     * Available parameters:
     *  route              Name of a fixed route to use (required unless homepage)
     *  rowcount           Total number of items to page in between
     *                       (if an array is assigned, it's count will be used)
     *  limit              Number of items on a page (if <0 unlimited)
     *  posvar             Name of the variable that contains the position data, eg "offset"
     *  template           Optional name of a template file (default: 'CoreBundle:Pager:pagercss.html.twig')
     *  includeStylesheet  Use predefined stylesheet file? Default is yes.
     *  anchorText         Optional text for hyperlink anchor (e.g. 'comments' for the anchor #comments) (default: '')
     *  maxpages           Optional maximum number of displayed pages, others will be hidden / suppressed
     *                       (default: 15 = show only 15 pages)
     *  display            Optional choice between 'page' or 'startnum'. Show links using page number or starting item number (default is startnum)
     *  class              Optional class to apply to the pager container (default : z-pager)
     *  processDetailLinks Should the single page links be processed? (default: false if using pagerimage.tpl, otherwise true)
     *  optimize           Only deliver page links which are actually displayed to the template (default: true)
     *  includePostVars    Whether or not to include the POST variables as GET variables in the pager URLs (default: true)
     *
     * @param array $params All attributes passed to this function from the template.
     * @return string
     */
    public function pager($params)
    {
        /** @var Request $request */
        $request = $this->container->get('request');

        if (empty($params['rowcount'])) {
            $params['rowcount'] = 0;
        } elseif (is_array($params['rowcount'])) {
            $params['rowcount'] = count($params['rowcount']);
        }

        // set default values - $pager is sent to template
        $pager = [];
        $pager['total'] = $params['rowcount'];
        $pager['perpage'] = isset($params['limit']) ? $params['limit'] : 20;
        $pager['class'] = isset($params['class']) ? $params['class'] : 'z-pager';
        $pager['optimize'] = isset($params['optimize']) ? $params['optimize'] : true;
        $pager['posvar'] = isset($params['posvar']) ? $params['posvar'] : 'pos';
        $pager['maxPages'] = isset($params['maxpages']) ? $params['maxpages'] : 15;
        $pager['includeStylesheet'] = isset($params['includeStylesheet']) ? $params['includeStylesheet'] : true;
        $displayType = isset($params['display']) ? $params['display'] : 'startnum';
        $includePostVars = isset($params['includePostVars']) ? $params['includePostVars'] : true;
        $routeName = isset($params['route']) ? $params['route'] : false;
        $templateName = (isset($params['template'])) ? $params['template'] : 'CoreBundle:Pager:pagercss.html.twig';
        $processDetailLinks = isset($params['processDetailLinks']) ? (bool)$params['processDetailLinks'] : ($templateName != 'CoreBundle:Pager:pagerimage.html.twig');
        $anchorText = isset($params['anchorText']) ? '#' . $params['anchorText'] : '';

        $routeParams = [];
        if ($request->attributes->has('_route_params')) {
            $routeParams = $request->attributes->get('_route_params');
            if (isset($routeParams[$pager['posvar']])) {
                $pager['pos'] = (int)($routeParams[$pager['posvar']]);
            } else {
                $pager['pos'] = (int)$request->query->get($pager['posvar'], '');
            }
        } else {
            $pager['pos'] = (int)$request->query->get($pager['posvar'], '');
        }
        if ($displayType == 'page') {
            $pager['pos'] = $pager['pos'] * $pager['perpage'];
            $pager['increment'] = 1;
        } else {
            $pager['increment'] = $pager['perpage'];
        }
        $pager['pos'] = $pager['pos'] >= 1 ? $pager['pos'] : 1;
        $pager['pos'] = $pager['pos'] <= $pager['total'] ? $pager['pos'] : $pager['total'];

        // number of pages
        $pager['countPages'] = (isset($pager['total']) && $pager['total'] > 0 ? ceil($pager['total'] / $pager['perpage']) : 1);
        if ($pager['countPages'] < 2) {
            return '';
        }

        // current page
        $pager['currentPage'] = ceil($pager['pos'] / $pager['perpage']);
        $pager['currentPage'] = $pager['currentPage'] > $pager['countPages'] ? $pager['countPages'] : $pager['currentPage'];

        $pager['args'] = [];

        // Include POST vars as requested, i.e. for search results
        $allVars = $includePostVars ? array_merge($request->request->all(), $request->query->all(), $routeParams) : array_merge($request->query->all(), $routeParams);
        foreach ($allVars as $k => $v) {
            if ($k != $pager['posvar'] && !is_null($v)) {
                switch ($k) {
                    case 'route':
                        if (!isset($routeName)) {
                            $routeName = $v;
                        }
                        break;
                    case 'lang':
                        $addcurrentlang2url = \System::getVar('languageurl');
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

        $pagerUrl = function ($pager) use ($routeName) {
            if (!$routeName) {
                // only case where this should be true is if this is the homepage
                $startargs   = explode(',', \System::getVar('startargs'));
                foreach ($startargs as $arg) {
                    if (!empty($arg)) {
                        $argument = explode('=', $arg);
                        $pager['args'][$argument[0]] = $argument[1];
                    }
                }

                return \ModUtil::url(\System::getVar('startpage'), \System::getVar('starttype'), \System::getVar('startfunc'), $pager['args']);
            }

            return $this->container->get('router')->generate($routeName, $pager['args']);
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

        if ($processDetailLinks) {
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

                if ($displayType == 'page') {
                    $pager['args'][$pager['posvar']] = $currItem;
                } else {
                    $pager['args'][$pager['posvar']] = (($currItem - 1) * $pager['perpage']) + 1;
                }

                $pager['pages'][$currItem]['pagenr'] = $currItem;
                $pager['pages'][$currItem]['isCurrentPage'] = ($pager['pages'][$currItem]['pagenr'] == $pager['currentPage']);
                $pager['pages'][$currItem]['isVisible'] = $currItemVisible;
                $pager['pages'][$currItem]['url'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
            }
            unset($pager['args'][$pager['posvar']]);
        }

        // link to first & prev page
        $pager['args'][$pager['posvar']] = $pager['first'] = '1';
        $pager['firstUrl'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);

        if ($displayType == 'page') {
            $pager['prev'] = ($pager['currentPage'] - 1);
        } else {
            $pager['prev'] = ($leftMargin - 1) * $pager['perpage'] - $pager['perpage'] + $pager['first'];
        }
        $pager['args'][$pager['posvar']] = ($pager['prev'] > 1) ? $pager['prev'] : 1;
        $pager['prevUrl'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);

        // link to next & last page
        if ($displayType == 'page') {
            $pager['next'] = $pager['currentPage'] + 1;
        } else {
            $pager['next'] = $rightMargin * $pager['perpage'] + 1;
        }
        $pager['args'][$pager['posvar']] = ($pager['next'] < $pager['total']) ? $pager['next'] : $pager['next'] - $pager['perpage'];
        $pager['nextUrl'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);

        if ($displayType == 'page') {
            $pager['last'] = $pager['countPages'];
        } else {
            $pager['last'] = $pager['countPages'] * $pager['perpage'] - $pager['perpage'] + 1;
        }
        $pager['args'][$pager['posvar']] = $pager['last'];
        $pager['lastUrl'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);

        $pager['itemStart'] = ($pager['currentPage'] * $pager['perpage']) - $pager['perpage'] + 1;
        $pager['itemEnd'] = $pager['itemStart'] + $pager['perpage'] - 1;
        if ($pager['itemEnd'] > $pager['total']) {
            $pager['itemEnd'] = $pager['total'];
        }

        $templateParameters = [];
        $templateParameters['pagerPluginArray'] = $pager;
        $templateParameters['hiddenPageBoxOpened'] = 0;
        $templateParameters['hiddenPageBoxClosed'] = 0;

        return $this->container->get('twig')->renderResponse($templateName, $templateParameters)->getContent();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulacore.pager';
    }
}
