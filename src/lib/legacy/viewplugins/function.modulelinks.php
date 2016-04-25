<?php
/**
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
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string A formatted string containing navigation for the module admin panel.
 */
function smarty_function_modulelinks($params, Zikula_View $view)
{
    $menuLinks          = isset($params['links'])       ? $params['links'] : '';
    $menuId             = isset($params['menuid'])      ? $params['menuid'] : '';
    $menuClass          = isset($params['menuclass'])   ? $params['menuclass'] : 'navbar-nav';
    $menuItemClass      = isset($params['itemclass'])   ? $params['itemclass'] : '';
    $menuItemFirst      = isset($params['first'])       ? $params['first'] : '';
    $menuItemLast       = isset($params['last'])        ? $params['last'] : '';
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
            $class = array();
            $class[] = $size == 1 ? 'z-ml-single' : '';
            $class[] = ($i == 1 && $size > 1) ? $menuItemFirst : '';
            $class[] = ($i == $size && $size > 1) ? $menuItemLast : '';
            $class[] = !empty($menuItemClass) ? $menuItemClass : '';
            $class[] = (isset($menuitem['disabled']) && $menuitem['disabled'] == true) ? 'z-ml-disabled' : '';
            $class = trim(implode(' ', $class));
            $i++;

            if (System::isLegacyMode() && !empty($class) && isset($menuitem['class'])) {
                if ($menuitem['class'] == 'z-icon-es-add') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'plus';
                } elseif ($menuitem['class'] == 'z-icon-es-back') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'arrow-left';
                } elseif ($menuitem['class'] == 'z-icon-es-cancel') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'times';
                } elseif ($menuitem['class'] == 'z-icon-es-config') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'wrench';
                } elseif ($menuitem['class'] == 'z-icon-es-copy') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'files-o';
                } elseif ($menuitem['class'] == 'z-icon-es-cubes') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'th';
                } elseif ($menuitem['class'] == 'z-icon-es-cut') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'scissors';
                } elseif ($menuitem['class'] == 'z-icon-es-delete') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'trash-o';
                } elseif ($menuitem['class'] == 'z-icon-es-display') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'eye';
                } elseif ($menuitem['class'] == 'z-icon-es-edit') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'pencil-square-o';
                } elseif ($menuitem['class'] == 'z-icon-es-error') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'exclamation-triangle';
                } elseif ($menuitem['class'] == 'z-icon-es-export') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'upload';
                } elseif ($menuitem['class'] == 'z-icon-es-gears') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'gears';
                } elseif ($menuitem['class'] == 'z-icon-es-filter') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'filter';
                } elseif ($menuitem['class'] == 'z-icon-es-group') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'users';
                } elseif ($menuitem['class'] == 'z-icon-es-help') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'info';
                } elseif ($menuitem['class'] == 'z-icon-es-home') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'home';
                } elseif ($menuitem['class'] == 'z-icon-es-hook') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'paperclip';
                } elseif ($menuitem['class'] == 'z-icon-es-import') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'download';
                } elseif ($menuitem['class'] == 'z-icon-es-info') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'info';
                } elseif ($menuitem['class'] == 'z-icon-es-locale') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'globe';
                } elseif ($menuitem['class'] == 'z-icon-es-locked') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'lock';
                } elseif ($menuitem['class'] == 'z-icon-es-log') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'archive';
                } elseif ($menuitem['class'] == 'z-icon-es-mail') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'inbox';
                } elseif ($menuitem['class'] == 'z-icon-es-new') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'file-o';
                } elseif ($menuitem['class'] == 'z-icon-es-ok') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'check';
                } elseif ($menuitem['class'] == 'z-icon-es-options') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'th-list';
                } elseif ($menuitem['class'] == 'z-icon-es-preview') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'cog';
                } elseif ($menuitem['class'] == 'z-icon-es-print') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'print';
                } elseif ($menuitem['class'] == 'z-icon-es-profile') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'user';
                } elseif ($menuitem['class'] == 'z-icon-es-regenerate') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'refresh';
                } elseif ($menuitem['class'] == 'z-icon-es-remove') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'times';
                } elseif ($menuitem['class'] == 'z-icon-es-save') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'floppy-o';
                } elseif ($menuitem['class'] == 'z-icon-es-saveas') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'floppy-o';
                } elseif ($menuitem['class'] == 'z-icon-es-search') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'search';
                } elseif ($menuitem['class'] == 'z-icon-es-url') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'globe';
                } elseif ($menuitem['class'] == 'z-icon-es-user') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'user';
                } elseif ($menuitem['class'] == 'z-icon-es-view') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'eye';
                } elseif ($menuitem['class'] == 'z-icon-es-warning') {
                    $menuitem['class'] = null;
                    $menuitem['icon'] = 'exclamation-triangle';
                } elseif ($menuitem['class'] == 'z-icon-es-rss') {
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

            if (isset($menuitem['disabled']) && $menuitem['disabled'] == true) {
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
