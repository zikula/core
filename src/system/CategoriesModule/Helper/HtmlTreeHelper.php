<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Helper;

use DataUtil;
use StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * HTML Tree helper functions for the categories module.
 */
class HtmlTreeHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CategorySortingHelper
     * /
     private $sortingHelper;*/

    /**
     * HtmlTreeHelper constructor.
     *
     * @param TranslatorInterface $translator   TranslatorInterface service instance
     * @param RouterInterface     $router       RouterInterface service instance
     * @param RequestStack        $requestStack RequestStack service instance
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, RequestStack $requestStack/*, CategorySortingHelper $sortingHelper*/)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
        //$this->sortingHelper = $sortingHelper;
    }

    /**
     * Return an array of folders the user has at least access/view rights to.
     *
     * @param array $cats List of categories
     *
     * @return array The resulting folder path array
     * @deprecated
     */
    public function getCategoryTreeStructure($cats)
    {
        $menuString = '';
        $params = [];
        $params['mode'] = 'edit';

        //$cats = $this->sortingHelper->sortCategories($cats, 'sort_value');
        $request = $this->requestStack->getCurrentRequest();
        $lang = $request->getLocale();

        foreach ($cats as $c) {
            $path = $c['path'];
            $depth = StringUtil::countInstances($path, '/');
            // account for the fact that a single slash is a valid root
            // path but subfolders only have a single slash as well
            if (strlen($path) > 1) {
                $depth++;
            }
            $ds = str_repeat('.', $depth);

            $params['cid'] = $c['id'];
            $url = DataUtil::formatForDisplay($this->router->generate('zikulacategoriesmodule_category_edit', $params));

            if ($request->attributes->get('_zkType') == 'admin') {
                $url .= '#top';
            }

            if (isset($c['display_name'][$lang]) && !empty($c['display_name'][$lang])) {
                $name = DataUtil::formatForDisplay($c['display_name'][$lang]);
            } else {
                $name = DataUtil::formatForDisplay($c['name']);
            }

            $menuLine = "$ds|$name|$url||||\n";

            $menuString .= $menuLine;
        }

        return $menuString;
    }

    /**
     * Return the HTML selector code for the given category hierarchy.
     *
     * @param array        $cats             The category hierarchy to generate a HTML selector for
     * @param string       $field            The field value to return (optional) (default='id')
     * @param string|array $selectedValue    The selected category (optional) (default=0)
     * @param string       $name             The name of the selector field to generate (optional) (default='category[parent_id]')
     * @param integer      $defaultValue     The default value to present to the user (optional) (default=0)
     * @param string       $defaultText      The default text to present to the user (optional) (default='')
     * @param integer      $allValue         The value to assign to the "all" option (optional) (default=0)
     * @param string       $allText          The text to assign to the "all" option (optional) (default='')
     * @param boolean      $submit           Whether or not to submit the form upon change (optional) (default=false)
     * @param boolean      $displayPath      If false, the path is simulated, if true, the full path is shown (optional) (default=false)
     * @param boolean      $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true)
     * @param integer      $multipleSize     If > 1, a multiple selector box is built, otherwise a normal/single selector box is build (optional) (default=1)
     * @param boolean      $fieldIsAttribute True if the field is attribute (optional) (default=false)
     *
     * @return string The HTML selector code for the given category hierarchy
     * @deprecated
     */
    public function getSelector_Categories($cats, $field = 'id', $selectedValue = '0', $name = 'category[parent_id]', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $submit = false, $displayPath = false, $doReplaceRootCat = true, $multipleSize = 1, $fieldIsAttribute = false, $cssClass = '', $lang = null)
    {
        $line = '---------------------------------------------------------------------';

        if ($multipleSize > 1 && strpos($name, '[]') === false) {
            $name .= '[]';
        }
        if (!is_array($selectedValue)) {
            $selectedValue = [
                (string)$selectedValue
            ];
        }

        $id = strtr($name, '[]', '__');
        $multiple = $multipleSize > 1 ? ' multiple="multiple"' : '';
        $multipleSize = $multipleSize > 1 ? " size=\"$multipleSize\"" : '';
        $submit = $submit ? ' onchange="this.form.submit();"' : '';
        $cssClass = $cssClass ? " class=\"$cssClass\"" : '';
        $lang = (isset($lang)) ? $lang : $this->requestStack->getCurrentRequest()->getLocale();

        $html = "<select name=\"$name\" id=\"$id\"{$multipleSize}{$multiple}{$submit}{$cssClass}>";

        if (!empty($defaultText)) {
            $sel = (in_array((string)$defaultValue, $selectedValue) ? ' selected="selected"' : '');
            $html .= "<option value=\"$defaultValue\"$sel>$defaultText</option>";
        }

        if ($allText) {
            $sel = (in_array((string)$allValue, $selectedValue) ? ' selected="selected"' : '');
            $html .= "<option value=\"$allValue\"$sel>$allText</option>";
        }

        $count = 0;
        if (!isset($cats) || empty($cats)) {
            $cats = [];
        }

        foreach ($cats as $cat) {
            if ($fieldIsAttribute) {
                $sel = in_array((string)$cat['__ATTRIBUTES__'][$field], $selectedValue) ? ' selected="selected"' : '';
            } else {
                $sel = in_array((string)$cat[$field], $selectedValue) ? ' selected="selected"' : '';
            }
            if ($displayPath) {
                if ($fieldIsAttribute) {
                    $v = $cat['__ATTRIBUTES__'][$field];
                    $html .= "<option value=\"$v\"$sel>$cat[path]</option>";
                } else {
                    $html .= "<option value=\"$cat[$field]\"$sel>$cat[path]</option>";
                }
            } else {
                $cslash = StringUtil::countInstances(isset($cat['ipath_relative']) ? $cat['ipath_relative'] : $cat['ipath'], '/');
                $indent = '';
                if ($cslash > 0) {
                    $indent = substr($line, 0, $cslash * 2);
                }

                $indent = '|' . $indent;

                if (isset($cat['display_name'][$lang]) && !empty($cat['display_name'][$lang])) {
                    $catName = $cat['display_name'][$lang];
                } else {
                    $catName = $cat['name'];
                }

                if ($fieldIsAttribute) {
                    $v = $cat['__ATTRIBUTES__'][$field];
                    $html .= "<option value=\"$v\"$sel>$indent " . DataUtil::formatForDisplayHtml($catName) . "</option>";
                } else {
                    $html .= "<option value=\"$cat[$field]\"$sel>$indent " . DataUtil::formatForDisplayHtml($catName) . "</option>";
                }
            }
            $count++;
        }

        $html .= '</select>';

        if ($doReplaceRootCat) {
            $html = str_replace('__SYSTEM__', $this->translator->__('Root category'), $html);
        }

        return $html;
    }
}
