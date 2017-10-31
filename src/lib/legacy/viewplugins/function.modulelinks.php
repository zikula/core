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
 * Zikula_View function to display menulinks in an unordered list
 *
 * Example
 * {modulelinks data=$links id='listid' class='navbar navbar-default' itemclass='z-ml-item' first='z-ml-first' last='z-ml-last'}
 *
 * Available parameters:
 *  links     Array with menulinks (text, url, title, id, class, disabled) (optional)
 *  modname   Module name to display links for (optional)
 *  type      Function type where the getLinks-function is located (optional)
 *  menuid    ID for the unordered list (optional)
 *  menuclass Class for the unordered list (optional)
 *  itemclass Array with menulinks (text, url, title, class, disabled) (optional)
 *  first     Class for the first element (optional)
 *  last      Class for the last element (optional)
 *  seperator Link seperator (optional)
 *  class     CSS class (optional).
 *  returnAsArray     return results as array, not as formatted html - MUST set assign
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string A formatted string containing navigation for the module admin panel
 */
function smarty_function_modulelinks($params, Zikula_View $view)
{
    $menuLinks          = isset($params['links']) ? $params['links'] : '';
    $menuId             = isset($params['menuid']) ? $params['menuid'] : '';
    $menuClass          = isset($params['menuclass']) ? $params['menuclass'] : 'navbar-nav';
    $menuItemClass      = isset($params['itemclass']) ? $params['itemclass'] : '';
    $menuItemFirst      = isset($params['first']) ? $params['first'] : '';
    $menuItemLast       = isset($params['last']) ? $params['last'] : '';
    $returnAsArray      = isset($params['returnAsArray']) ? (bool) $params['returnAsArray'] : false;

    if (empty($menuLinks)) {
        if (!isset($params['modname']) || !ModUtil::available($params['modname'])) {
            $params['modname'] = ModUtil::getName();
        }

        // check our module name
        if (!ModUtil::available($params['modname'])) {
            $view->trigger_error('modulelinks: '.__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($params['modname'])));

            return false;
        }

        $params['type'] = isset($params['type']) ? $params['type'] : 'admin';

        // get the menu links
        // try the Core-2.0 way first, then try the legacy way.
        $menuLinks = $view->getContainer()->get('zikula.link_container_collector')->getLinks($params['modname'], $params['type']);
        if (empty($menuLinks)) {
            $menuLinks = ModUtil::apiFunc($params['modname'], $params['type'], 'getLinks', $params);
        }
    }

    // return if there are no links to print or template has requested to returnAsArray
    if ((!$menuLinks) || ($returnAsArray && isset($params['assign']))) {
        if (isset($params['assign'])) {
            $view->assign($params['assign'], $menuLinks);
        }

        return '';
    }

    $html = '';

    if (!empty($menuLinks)) {
        $html = '<div class="navbar navbar-default navbar-modulelinks navbar-modulelinks-main"><ul';
        $html .= !empty($menuId) ? ' id="'.$menuId.'"' : '';
        $html .= !empty($menuClass) ? ' class="'.$menuClass.'"' : '';
        $html .= '>';

        $i = 1;
        $size = count($menuLinks);
        foreach ($menuLinks as $menuitem) {
            $class = [];
            $class[] = 1 == $size ? 'z-ml-single' : '';
            $class[] = (1 == $i && $size > 1) ? $menuItemFirst : '';
            $class[] = ($i == $size && $size > 1) ? $menuItemLast : '';
            $class[] = !empty($menuItemClass) ? $menuItemClass : '';
            $class[] = (isset($menuitem['disabled']) && true == $menuitem['disabled']) ? 'z-ml-disabled' : '';
            $class = trim(implode(' ', $class));
            $i++;

            if (System::isLegacyMode() && !empty($class) && isset($menuitem['class'])) {
                if ('z-icon-es-add' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'plus';
                } elseif ('z-icon-es-back' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'arrow-left';
                } elseif ('z-icon-es-cancel' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'times';
                } elseif ('z-icon-es-config' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'wrench';
                } elseif ('z-icon-es-copy' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'files-o';
                } elseif ('z-icon-es-cubes' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'th';
                } elseif ('z-icon-es-cut' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'scissors';
                } elseif ('z-icon-es-delete' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'trash-o';
                } elseif ('z-icon-es-display' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'eye';
                } elseif ('z-icon-es-edit' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'pencil-square-o';
                } elseif ('z-icon-es-error' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'exclamation-triangle';
                } elseif ('z-icon-es-export' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'upload';
                } elseif ('z-icon-es-gears' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'gears';
                } elseif ('z-icon-es-filter' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'filter';
                } elseif ('z-icon-es-group' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'users';
                } elseif ('z-icon-es-help' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'info';
                } elseif ('z-icon-es-home' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'home';
                } elseif ('z-icon-es-hook' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'paperclip';
                } elseif ('z-icon-es-import' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'download';
                } elseif ('z-icon-es-info' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'info';
                } elseif ('z-icon-es-locale' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'globe';
                } elseif ('z-icon-es-locked' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'lock';
                } elseif ('z-icon-es-log' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'archive';
                } elseif ('z-icon-es-mail' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'inbox';
                } elseif ('z-icon-es-new' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'file-o';
                } elseif ('z-icon-es-ok' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'check';
                } elseif ('z-icon-es-options' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'th-list';
                } elseif ('z-icon-es-preview' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'cog';
                } elseif ('z-icon-es-print' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'print';
                } elseif ('z-icon-es-profile' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'user';
                } elseif ('z-icon-es-regenerate' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'refresh';
                } elseif ('z-icon-es-remove' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'times';
                } elseif ('z-icon-es-save' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'floppy-o';
                } elseif ('z-icon-es-saveas' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'floppy-o';
                } elseif ('z-icon-es-search' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'search';
                } elseif ('z-icon-es-url' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'globe';
                } elseif ('z-icon-es-user' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'user';
                } elseif ('z-icon-es-view' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'eye';
                } elseif ('z-icon-es-warning' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'exclamation-triangle';
                } elseif ('z-icon-es-rss' == $menuitem['class']) {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'rss-square';
                }
            }

            $active = '';
            if (!empty($menuitem['url']) && System::getBaseUrl().$menuitem['url'] === System::getCurrentUrl()) {
                $active = 'active ';
            }

            $dropdown = '';
            if (isset($menuitem['links'])) {
                $dropdown = 'dropdown';
            }

            $html .= '<li';
            $html .= !empty($menuitem['id']) ? ' id="'.$menuitem['id'].'"' : '';
            if (!empty($active) || !empty($dropdown) || !empty($class)) {
                $html .= ' class="' . $active . $dropdown . $class . '"';
            }
            $html .= '>';
            $attr  = !empty($menuitem['title']) ? ' title="'.$menuitem['title'].'"' : '';
            $attr .= !empty($menuitem['class']) ? ' class="'.$menuitem['class'].'"' : '';

            if (isset($menuitem['disabled']) && true == $menuitem['disabled']) {
                $html .= '<a '.$attr.'>'.$menuitem['text'].'</a>';
            } elseif (!empty($menuitem['url'])) {
                $icon = '';
                if (!empty($menuitem['icon'])) {
                    $icon = '<span class="fa fa-'.$menuitem['icon'].'"></span> ';
                }
                if (isset($menuitem['links'])) {
                    $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="text-decoration: none;">' . $icon . $menuitem['text'] . '&nbsp;<b class="caret"></b></a>';
                } else {
                    $html .= '<a href="'.DataUtil::formatForDisplay($menuitem['url']).'"'.$attr.'>'.$icon.$menuitem['text'].'</a>';
                }
            } else {
                $html .= '<span'.$attr.'>'.$menuitem['text'].'</span>';
            }
            if (isset($menuitem['links'])) {
                $html .= '<ul class="dropdown-menu">';
                foreach ($menuitem['links'] as $submenuitem) {
                    $html .= '<li>';
                    if (isset($submenuitem['url'])) {
                        $html .= '<a href="'.DataUtil::formatForDisplay($submenuitem['url']).'">'.$submenuitem['text'].'</a>';
                    } else {
                        $html .= $submenuitem['text'];
                    }
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '</li>';
        }

        $html .= '</ul></div>';
    }

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $html);
    } else {
        return $html;
    }
}
