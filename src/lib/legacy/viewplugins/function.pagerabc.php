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
 * Zikula_View plugin.
 *
 * Author:   Peter Dudas <duda at bigfish dot hu>
 *
 *  Examples:
 *    code:
 *    {pagerabc posvar='letter' class='abcpager' class_num='abclink' class_numon='abclink_on' separator=' - ' names='A,B;C,D;E,F;G,H;I,J;K,L;M,N,O;P,Q,R;S,T;U,V,W,X,Y,Z'}
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
 *
 * Parameters:
 *  posvar         Name of the variable that contains the position data, eg "letter"
 *  forwardvars    Comma- semicolon- or space-delimited list of POST and GET variables to forward in the pager links. If unset, all vars are forwarded.
 *  additionalvars Comma- semicolon- or space-delimited list of additional variable and value pairs to forward in the links. eg "foo=2,bar=4"
 *  route          Name of a fixed route to use (optional, replaces modname / type / func)
 *  modname        Fixed name of the module to page (optional)
 *  type           Fixed value of the type url parameter (optional)
 *  func           Fixed value of the function url parameter (optional)
 *  class          Class for the pager
 *  class_num      Class for the pager links (<a> tags)
 *  class_numon    Class for the active page
 *  printempty     Print empty sel ('-')
 *  lang           Language
 *  names          String or array of names to select from (array or csv)
 *  values         Optional parameter for the previous names (array or cvs)
 *  skin           Use predefined values (hu - hungarian ABC)
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_pagerabc($params, Zikula_View $view)
{
    if (!isset($params['posvar'])) {
        $params['posvar'] = 'letter';
    }

    if (!isset($params['separator'])) {
        $params['separator'] = ' | ';
    }

    if (!isset($params['skin'])) {
        $params['skin'] = '';
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
            if (count($pager['values']) != count($pager['names'])) {
                LogUtil::addErrorPopup('pagerabc: Values length must be the same of the names');
                $pager['values'] = $pager['names'];
            }
        } else {
            $pager['values'] = $pager['names'];
        }
    } else {
        // predefined abc
        if (strtolower($params['skin']) == 'hu') {
            // Hungarian
            $pager['names']  = $pager['values'] = ['A', '?', 'B', 'C', 'D', 'E', '?', 'F', 'G', 'H', 'I', '?', 'J', 'K', 'L', 'M', 'N', 'O', '?', '?', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', '?', '?', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            //$params['names']  = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U'    ,'V','W','X','Y','Z'];
            //$params['values'] = ['A,?','B','C','D','E,?','F','G','H','I,?','J','K','L','M','N','O,?,?,O','P','Q','R','S','T','U,?,?,U','V','W','X','Y','Z'];
        } else {
            $alphabet = (defined('_ALPHABET')) ? constant('_ALPHABET') : 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
            $pager['names'] = $pager['values'] = explode(',', $alphabet);
        }
    }

    $pager['posvar'] = $params['posvar'];
    unset($params['posvar']);
    unset($params['names']);
    unset($params['values']);

    if (!isset($params['route'])) {
        if (isset($params['modname'])) {
            $pager['module'] = $params['modname'];
        } else {
            $module = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
            $name   = FormUtil::getPassedValue('name', null, 'GETPOST', FILTER_SANITIZE_STRING);
            $pager['module'] = !empty($module) ? $module : $name;
        }

        $pager['func'] = isset($params['func']) ? $params['func'] : FormUtil::getPassedValue('func', 'index', 'GETPOST', FILTER_SANITIZE_STRING);
        $pager['type'] = isset($params['type']) ? $params['type'] : FormUtil::getPassedValue('type', 'user', 'GETPOST', FILTER_SANITIZE_STRING);
    } else {
        $pager['route'] = $params['route'];
        unset($params['route']);
    }

    $pagerUrl = function ($pager) use ($view) {
        if (!isset($pager['route'])) {
            return ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']);
        }

        return $view->getContainer()->get('router')->generate($pager['route'], $pager['args']);
    };

    $allVars = array_merge($view->getRequest()->request->all(), $view->getRequest()->query->all(), $view->getRequest()->attributes->get('_route_params', []));

    $pager['args'] = [];
    if (empty($pager['module']) && empty($pager['route'])) {
        $pager['module'] = System::getVar('startpage');
        $starttype = System::getVar('starttype');
        $pager['type'] = !empty($starttype) ? $starttype : 'user';
        $startfunc = System::getVar('startfunc');
        $pager['func'] = !empty($startfunc) ? $startfunc : 'index';

        $startargs = explode(',', System::getVar('startargs'));
        foreach ($startargs as $arg) {
            if (!empty($arg)) {
                $argument = explode('=', $arg);
                if ($argument[0] == $pager['posvar']) {
                    $allVars[$argument[0]] = $argument[1];
                }
            }
        }
    }

    // If $forwardvars set, add only listed vars to query string, else add all POST and GET vars
    if (isset($params['forwardvars'])) {
        if (!is_array($params['forwardvars'])) {
            $params['forwardvars'] = preg_split('/[,;\s]/', $params['forwardvars'], -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ((array)$params['forwardvars'] as $key => $var) {
            if (!empty($var) && (!empty($allVars[$var]))) {
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
            $additionalvar = preg_split('/=/', $var);
            if (!empty($var) && !empty($additionalvar[1])) {
                $pager['args'][$additionalvar[0]] = $additionalvar[1];
            }
        }
    }
    unset($pager['args']['module']);
    unset($pager['args']['func']);
    unset($pager['args']['type']);
    unset($pager['args'][$pager['posvar']]);

    // begin to fill the output
    $output = '<ul class="'.$params['class'].'">'."\n";

    $style = '';
    if ($params['printempty']) {
        $active = '';
        if (!empty($params['class_numon'])) {
            if (!isset($allVars[$pager['posvar']])) {
                $style = ' class="'.$params['class_numon'].'"';
                $active = 'class="active"';
            } elseif (!empty($params['class_num'])) {
                $style = ' class="'.$params['class_num'].'"';
            } else {
                $style = '';
            }
        }
        $vars[$pager['posvar']] = '';
        $urltemp = DataUtil::formatForDisplay($pagerUrl($pager));
        $output .= '<li '.$active.'><a '.$style.' href="'.$urltemp.'"> -'."\n</a></li>";
    }

    $style = '';
    foreach (array_keys($pager['names']) as $i) {
        $active = '';
        if (!empty($params['class_numon'])) {
            if (isset($allVars[$pager['posvar']]) && $allVars[$pager['posvar']] == $pager['values'][$i]) {
                $style = ' class="'.$params['class_numon'].'"';
                $active = 'class="active"';
            } elseif (!empty($params['class_num'])) {
                $style = ' class="'.$params['class_num'].'"';
            } else {
                $style = '';
            }
        }
        $pager['args'][$pager['posvar']] = $pager['values'][$i];
        $urltemp = DataUtil::formatForDisplay($pagerUrl($pager));
        $output .= '<li '.$active.'><a '.$style.' href="'.$urltemp.'">'.$pager['names'][$i]."</a></li>\n";
    }
    $output .= "</ul>\n";

    return $output;
}
