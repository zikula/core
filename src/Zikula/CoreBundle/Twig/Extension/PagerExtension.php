<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class PagerExtension extends AbstractExtension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        VariableApiInterface $variableApi,
        Environment $twig
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->variableApi = $variableApi;
        $this->twig = $twig;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('pager', [$this, 'pager'], ['is_safe' => ['html']]),
            new TwigFunction('pagerabc', [$this, 'pagerabc'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Pager function.
     *
     *   {{ pager({rowcount:pager.numitems, limit:pager.itemsperpage, posvar:'startnum', route:'zikulapagesmodule_admin_index', template:'pager.html.twig'}) }}
     *
     * Available parameters:
     *  route              Name of a fixed route to use (required unless homepage)
     *  rowcount           Total number of items to page in between
     *                       (if an array is assigned, it's count will be used)
     *  limit              Number of items on a page (if <0 unlimited)
     *  posvar             Name of the variable that contains the position data, eg "offset"
     *  template           Optional name of a template file (default: '@Core/Pager/pagercss.html.twig')
     *  includeStylesheet  Use predefined stylesheet file? Default is yes.
     *  anchorText         Optional text for hyperlink anchor (e.g. 'comments' for the anchor #comments) (default: '')
     *  maxpages           Optional maximum number of displayed pages, others will be hidden / suppressed
     *                       (default: 15 = show only 15 pages)
     *  display            Optional choice between 'page' or 'startnum'. Show links using page number or starting item number (default is startnum)
     *  class              Optional class to apply to the pager container (default : z-pager)
     *  processDetailLinks Should the single page links be processed? (default: false if using pagerimage.tpl, otherwise true)
     *  optimize           Only deliver page links which are actually displayed to the template (default: true)
     *  includePostVars    Whether or not to include the POST variables as GET variables in the pager URLs (default: true)
     */
    public function pager(array $params = []): string
    {
        /** @var Request $request */
        $request = $this->requestStack->getMasterRequest();

        if (empty($params['rowcount'])) {
            $params['rowcount'] = 0;
        } elseif (is_array($params['rowcount'])) {
            $params['rowcount'] = count($params['rowcount']);
        }

        // set default values - $pager is sent to template
        $pager = [
            'total' => $params['rowcount'],
            'perpage' => $params['limit'] ?? 20,
            'class' => $params['class'] ?? 'z-pager',
            'optimize' => $params['optimize'] ?? true,
            'posvar' => $params['posvar'] ?? 'pos',
            'maxPages' => $params['maxpages'] ?? 15,
            'includeStylesheet' => $params['includeStylesheet'] ?? true
        ];
        $displayType = $params['display'] ?? 'startnum';
        $includePostVars = $params['includePostVars'] ?? true;
        $routeName = $params['route'] ?? false;
        $templateName = $params['template'] ?? '@Core/Pager/pagercss.html.twig';
        $processDetailLinks = isset($params['processDetailLinks']) ? (bool)$params['processDetailLinks'] : ('@Core/Pager/pagerimage.html.twig' !== $templateName);
        $anchorText = isset($params['anchorText']) ? '#' . $params['anchorText'] : '';
        $systemVars = $this->variableApi->getAll(VariableApi::CONFIG);

        $routeParams = [];
        if ($request->attributes->has('_route_params')) {
            $routeParams = $request->attributes->get('_route_params');
            if (isset($routeParams[$pager['posvar']])) {
                $pager['pos'] = (int)$routeParams[$pager['posvar']];
            } else {
                $pager['pos'] = (int)$request->query->get($pager['posvar'], '');
            }
        } else {
            $pager['pos'] = (int)$request->query->get($pager['posvar'], '');
        }
        if ('page' === $displayType) {
            $pager['pos'] *= $pager['perpage'];
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
            if (null !== $v && $k !== $pager['posvar']) {
                switch ($k) {
                    case 'route':
                        if (!isset($routeName)) {
                            $routeName = $v;
                        }
                        break;
                    case 'lang':
                        $addCurrentLanguageToUrl = $systemVars['languageurl'];
                        if (0 === $addCurrentLanguageToUrl) {
                            $pager['args'][$k] = $v;
                        }
                        break;
                    default:
                        if (is_array($v)) {
                            foreach ($v as $kk => $vv) {
                                if (is_array($vv)) {
                                    foreach ($vv as $kkk => $vvv) {
                                        if (is_array($vvv)) {
                                            foreach ($vvv as $kkkk => $vvvv) {
                                                if ('' !== $vvvv) {
                                                    $tkey = $k . '[' . $kk . '][' . $kkk . '][' . $kkkk . ']';
                                                    $pager['args'][$tkey] = $vvvv;
                                                }
                                            }
                                        } elseif ('' !== $vvv) {
                                            $tkey = $k . '[' . $kk . '][' . $kkk . ']';
                                            $pager['args'][$tkey] = $vvv;
                                        }
                                    }
                                } elseif ('' !== $vv) {
                                    $tkey = $k . '[' . $kk . ']';
                                    $pager['args'][$tkey] = $vv;
                                }
                            }
                        } elseif ('' !== $v) {
                            $pager['args'][$k] = $v;
                        }
                }
            }
        }

        $pagerUrl = function($pager) use ($request, $routeName, $systemVars) {
            if ($routeName) {
                return $this->router->generate($routeName, $pager['args']);
            }

            // only case where this should be true is if this is the homepage
            $startPageInfo = $systemVars['startController_' . $request->getLocale()];
            if (is_array($startPageInfo) && isset($startPageInfo['controller'])) {
                parse_str($startPageInfo['query'], $pager['args']);
                parse_str($startPageInfo['attributes'], $pager['args']);

                [$route, $controller] = explode('###', $startPageInfo['controller']);
                return $this->router->generate($route, $pager['args']);
            }

            return $this->router->generate('home', $pager['args']);
        };

        // build links to items / pages
        // entries are marked as current or displayed / hidden
        $pager['pages'] = [];

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

        if ($processDetailLinks) {
            for ($currItem = 1; $currItem <= $pager['countPages']; $currItem++) {
                $currItemVisible = true;

                if (0 < $pager['maxPages'] &&
                    //(($currItem < $leftMargin && $currItem > 1) || ($currItem > $rightMargin && $currItem <= $pager['countPages']))) {
                    ($currItem < $leftMargin || $currItem > $rightMargin)
                ) {
                    if ($pager['optimize']) {
                        continue;
                    }
                    $currItemVisible = false;
                }

                if ('page' === $displayType) {
                    $pager['args'][$pager['posvar']] = $currItem;
                } else {
                    $pager['args'][$pager['posvar']] = (($currItem - 1) * $pager['perpage']) + 1;
                }

                $pager['pages'][$currItem]['pagenr'] = $currItem;
                $pager['pages'][$currItem]['isCurrentPage'] = ($pager['pages'][$currItem]['pagenr'] === $pager['currentPage']);
                $pager['pages'][$currItem]['isVisible'] = $currItemVisible;
                $pager['pages'][$currItem]['url'] = $pagerUrl($pager) . $anchorText;
            }
            unset($pager['args'][$pager['posvar']]);
        }

        // link to first & prev page
        $pager['args'][$pager['posvar']] = $pager['first'] = 1;
        $pager['firstUrl'] = $pagerUrl($pager) . $anchorText;

        if ('page' === $displayType) {
            $pager['prev'] = ($pager['currentPage'] - 1);
        } else {
            $pager['prev'] = ($leftMargin - 1) * $pager['perpage'] - $pager['perpage'] + $pager['first'];
        }
        $pager['args'][$pager['posvar']] = ($pager['prev'] > 1) ? $pager['prev'] : 1;
        $pager['prevUrl'] = $pagerUrl($pager) . $anchorText;

        // link to next & last page
        if ('page' === $displayType) {
            $pager['next'] = $pager['currentPage'] + 1;
        } else {
            $pager['next'] = $rightMargin * $pager['perpage'] + 1;
        }
        $pager['args'][$pager['posvar']] = ($pager['next'] < $pager['total']) ? $pager['next'] : $pager['next'] - $pager['perpage'];
        $pager['nextUrl'] = $pagerUrl($pager) . $anchorText;

        if ('page' === $displayType) {
            $pager['last'] = $pager['countPages'];
        } else {
            $pager['last'] = $pager['countPages'] * $pager['perpage'] - $pager['perpage'] + 1;
        }
        $pager['args'][$pager['posvar']] = $pager['last'];
        $pager['lastUrl'] = $pagerUrl($pager) . $anchorText;

        $pager['itemStart'] = ($pager['currentPage'] * $pager['perpage']) - $pager['perpage'] + 1;
        $pager['itemEnd'] = $pager['itemStart'] + $pager['perpage'] - 1;
        if ($pager['itemEnd'] > $pager['total']) {
            $pager['itemEnd'] = $pager['total'];
        }

        $templateParameters = [
            'pagerPluginArray' => $pager,
            'hiddenPageBoxOpened' => 0,
            'hiddenPageBoxClosed' => 0
        ];

        return $this->twig->render($templateName, $templateParameters);
    }

    /**
     * ABC-Pager function.
     *
     *  Example:
     *    code:
     *    {{ pagerabc({route:'acmefoomodule_user_view', posvar:'letter', class:'abcpager', class_num:'abclink', class_numon:'abclink_on', separator:' - ', names:'A,B;C,D;E,F;G,H;I,J;K,L;M,N,O;P,Q,R;S,T;U,V,W,X,Y,Z'}) }}
     *
     *    result
     * <span class="abcpager">
     * <a class="abclink_on" href="index.php?module=Example&amp;letter=A,B">&nbspA,B</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=C,D">&nbspC,D</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=E,F">&nbspE,F</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=G,H">&nbspG,H</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=I,J">&nbspI,J</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=K,L">&nbspK,L</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=M,N,O">&nbspM,N,O</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=P,Q,R">&nbspP,Q,R</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=S,T">&nbspS,T</a>
     *  - <a class="abclink" href="index.php?module=Example&amp;letter=U,V,W,X,Y,Z">&nbspU,V,W,X,Y,Z</a>
     * </span>
     *
     * Available parameters:
     *  route          Name of a fixed route to use (required)
     *  posvar         Name of the variable that contains the position data, eg "letter"
     *  forwardvars    Comma- semicolon- or space-delimited list of POST and GET variables to forward in the pager links. If unset, all vars are forwarded.
     *  additionalvars Comma- semicolon- or space-delimited list of additional variable and value pairs to forward in the links. eg "foo=2,bar=4"
     *  class          Class for the pager
     *  class_num      Class for the pager links (<a> tags)
     *  class_numon    Class for the active page
     *  printempty     Print empty sel ('-')
     *  lang           Language
     *  names          String or array of names to select from (array or csv)
     *  values         Optional parameter for the previous names (array or cvs)
     */
    public function pagerabc(array $params = []): string
    {
        if (empty($params['route'])) {
            throw new InvalidArgumentException('route is a required parameter.');
        }
        /** @var Request $request */
        $request = $this->requestStack->getMasterRequest();
        if (!isset($params['posvar'])) {
            $params['posvar'] = 'letter';
        }
        if (!isset($params['separator'])) {
            $params['separator'] = ' | ';
        }
        if (!isset($params['printempty']) || !is_bool($params['printempty'])) {
            $params['printempty'] = false;
        }
        // set a default class
        if (!isset($params['class'])) {
            $params['class'] = 'pagination pagination-sm';
        }
        if (!isset($params['class_num'])) {
            $params['class_num'] = '';
        }
        if (!isset($params['class_numon'])) {
            $params['class_numon'] = ' ';
        }
        $pager = [];
        if (!empty($params['names'])) {
            if (!is_array($params['names'])) {
                $pager['names'] = explode(';', $params['names']);
            } else {
                $pager['names'] = $params['names'];
            }
            if (!empty($params['values'])) {
                if (!is_array($params['values'])) {
                    $pager['values'] = explode(';', $params['values']);
                } else {
                    $pager['values'] = $params['values'];
                }
                if (count($pager['values']) !== count($pager['names'])) {
                    $pager['values'] = $pager['names'];
                }
            } else {
                $pager['values'] = $pager['names'];
            }
        } else {
            $alphabet = defined('_ALPHABET') ? constant('_ALPHABET') : 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
            $pager['names'] = $pager['values'] = explode(',', $alphabet);
        }
        $pager['posvar'] = $params['posvar'];
        $pager['route'] = $params['route'];
        unset($params['posvar'], $params['names'], $params['values'], $params['route']);

        $pagerUrl = function($pager) {
            return $this->router->generate($pager['route'], $pager['args']);
        };
        $allVars = array_merge($request->request->all(), $request->query->all(), $request->attributes->get('_route_params', []));
        $pager['args'] = [];
        // If $forwardvars set, add only listed vars to query string, else add all POST and GET vars
        if (isset($params['forwardvars'])) {
            if (!is_array($params['forwardvars'])) {
                $params['forwardvars'] = preg_split('/[,;\s]/', $params['forwardvars'], -1, PREG_SPLIT_NO_EMPTY);
            }
            foreach ((array)$params['forwardvars'] as $key => $var) {
                if (!empty($var) && !empty($allVars[$var])) {
                    $pager['args'][$var] = $allVars[$var];
                }
            }
        } else {
            $pager['args'] = array_merge($pager['args'], $allVars);
        }
        if (isset($params['additionalvars'])) {
            if (!is_array($params['additionalvars'])) {
                $params['additionalvars'] = preg_split('/[,;\s]/', $params['additionalvars'], -1, PREG_SPLIT_NO_EMPTY);
            }
            foreach ((array)$params['additionalvars'] as $var) {
                $additionalvar = explode('=', $var);
                if (!empty($var) && !empty($additionalvar[1])) {
                    $pager['args'][$additionalvar[0]] = $additionalvar[1];
                }
            }
        }
        unset($pager['args'][$pager['posvar']]);

        // begin to fill the output
        $output = '<ul class="' . $params['class'] . '">' . "\n";
        $customClass = '';
        if ($params['printempty']) {
            $active = '';
            if (!empty($params['class_numon'])) {
                if (!isset($allVars[$pager['posvar']])) {
                    $customClass = ' ' . $params['class_numon'];
                    $active = ' active';
                } elseif (!empty($params['class_num'])) {
                    $customClass = ' ' . $params['class_num'];
                }
            }
            $output .= '<li class="page-item' . $active . '"><a href="' . $pagerUrl($pager) . '" class="page-link ' . $customClass . '"> -' . "\n</a></li>";
        }

        $customClass = '';
        foreach (array_keys($pager['names']) as $i) {
            $active = '';
            if (!empty($params['class_numon'])) {
                if (isset($allVars[$pager['posvar']]) && $pager['values'][$i] === $allVars[$pager['posvar']]) {
                    $customClass = ' ' . $params['class_numon'];
                    $active = ' active';
                } elseif (!empty($params['class_num'])) {
                    $customClass = ' ' . $params['class_num'];
                } else {
                    $customClass = '';
                }
            }
            $pager['args'][$pager['posvar']] = $pager['values'][$i];
            $output .= '<li class="page-item' . $active . '"><a href="' . $pagerUrl($pager) . '" class="page-link ' . $customClass . '">' . $pager['names'][$i] . "</a></li>\n";
        }
        $output .= "</ul>\n";

        return $output;
    }
}
