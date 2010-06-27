<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Form_Plugin
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Category selector
 *
 * This plugin creates a category selector using a dropdown list.
 * The selected value of the base dropdown list will be set to ID of the selected category.
 */
class Form_Plugin_CategorySelector extends Form_Plugin_DropdownList
{
    /**
     * Whether or not to show an edit link.
     *
     * @var boolean
     */
    public $editLink;

    /**
     * Base category.
     *
     * May be the id, the category array or the path.
     *
     * @var mixed
     */
    public $category;

    /**
     * Enable inclusion of an empty null value element.
     *
     * @var boolean (default false)
     */
    public $includeEmptyElement;

    /**
     * Enable save/load of values in separate __CATEGORIES_ field for use in DBUtil.
     *
     * If enabled then selected category is returned in a sub-array named __CATEGORIES__
     * such that it can be used directly with DBUtils standard categorization of
     * data items. Example code:
     * <code>
     * // Template: <!--[formcategoryselector id=myCat category=xxx enableDBUtil=1]-->
     * // Result:
     * array('title' => 'Item title',
     * '__CATEGORIES__' => array('myCat' => zzz)
     * )
     * </code>
     *
     * @var boolean (default false)
     */
    public $enableDBUtil;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Load the parameters.
     *
     * Shared by other category plugins.
     * FIXME: Should this method be static?
     *
     * @param object  &$list               The list object (here: $this).
     * @param boolean $includeEmptyElement Whether or not to include an empty null item.
     * @param array   $params              The parameters passed from the Smarty plugin.
     *
     * @return void
     */
    function loadParameters(&$list, $includeEmptyElement, $params)
    {
        $list->category = isset($params['category']) ? $params['category'] : 0;
        $path = isset($params['path']) ? $params['path'] : '';
        $pathfield = isset($params['pathfield']) ? $params['pathfield'] : 'path';
        $lang = isset($params['lang']) ? $params['lang'] : ZLanguage::getLanguageCode();
        $recurse = isset($params['recurse']) ? $params['recurse'] : true;
        $relative = isset($params['relative']) ? $params['relative'] : true;
        $includeRoot = isset($params['includeRoot']) ? $params['includeRoot'] : false;
        $includeLeaf = isset($params['includeLeaf']) ? $params['includeLeaf'] : true;
        $all = isset($params['all']) ? $params['all'] : false;
        $list->editLink = isset($params['editLink']) ? $params['editLink'] : true;

        $allCats = array();

        // if we don't have a category-id we see if we can get a category by path
        if (!$list->category && $path) {
            $list->category = CategoryUtil::getCategoryByPath($path, $pathfield);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all);

        } elseif (is_array($list->category) && isset($list->category['id']) && is_integer($list->category['id'])) {
            // check if we have an actual category object with a numeric ID set
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all);

        } elseif (is_numeric($list->category)) {
            // check if we have a numeric category
            $list->category = CategoryUtil::getCategoryByID($list->category);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all);

        } elseif (is_string($list->category) && strpos($list->category, '/') === 0) {
            // check if we have a string/path category
            $list->category = CategoryUtil::getCategoryByPath($list->category, $pathfield);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all);
        }

        if ($list->mandatory)
            $list->addItem('- - -', null);

        $line = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -';

        if ($includeEmptyElement)
            $list->addItem('', null);

        foreach ($allCats as $cat) {
            $cslash = StringUtil::countInstances(isset($cat['ipath_relative']) ? $cat['ipath_relative'] : $cat['ipath'], '/');
            $indent = '';
            if ($cslash > 0) {
                $indent = '| ' . substr($line, 0, $cslash * 2);
            }

            $catName = html_entity_decode((isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name']));
            $list->addItem($indent . ' ' . $catName, $cat['id']);
        }

    }

    /**
     * Load event handler.
     *
     * @param Form_Render &$render Reference to pnForm render object.
     * @param array       $params  Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    function load(&$render, $params)
    {
        $this->includeEmptyElement = (isset($params['includeEmptyElement']) ? $params['includeEmptyElement'] : false);
        $this->enableDBUtil = (isset($params['enableDBUtil']) ? $params['enableDBUtil'] : false);
        pnFormCategorySelector::loadParameters($this, $this->includeEmptyElement, $params);
        parent::load($render, $params);
    }

    /**
     * Render event handler.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return string The rendered output
     */
    function render(&$render)
    {
        $result = parent::render($render);

        if ($this->editLink && !empty($this->category) && SecurityUtil::checkPermission('Categories::', "$this->category[id]::", ACCESS_EDIT)) {
            $url = DataUtil::formatForDisplay(ModUtil::url('Categories', 'user', 'edit', array(
                'dr' => $this->category['id'])));
            $result .= "&nbsp;&nbsp;<a href=\"$url\"><img src=\"images/icons/extrasmall/xedit.gif\" title=\"" . __('Edit') . '" alt="' . __('Edit') . '" /></a>';
        }

        return $result;
    }

    /**
     * Saves value in data object.
     *
     * Called by the render when doing $render->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Form_Render &$render Reference to Form render object.
     * @param array       &$data   Data object.
     *
     * @return void
     */
    function saveValue(&$render, &$data)
    {
        if ($this->enableDBUtil && $this->dataBased) {
            if ($this->group == null) {
                $data['__CATEGORIES__'][$this->dataField] = $this->getSelectedValue();
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group]['__CATEGORIES__'][$this->dataField] = $this->getSelectedValue();
            }
        } else {
            parent::saveValue($render, $data);
        }
    }

    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's pnFormSetValues.
     *
     * @param Form_Render &$render Reference to pnForm render object.
     * @param array       &$values Values to load.
     *
     * @return void
     */
    function loadValue(&$render, &$values)
    {
        if ($this->enableDBUtil && $this->dataBased) {
            $items = null;
            $value = null;

            if ($this->group == null) {
                if ($this->dataField != null && isset($values['__CATEGORIES__'][$this->dataField])) {
                    $value = $values['__CATEGORIES__'][$this->dataField];
                }
                if ($this->itemsDataField != null && isset($values[$this->itemsDataField])) {
                    $items = $values[$this->itemsDataField];
                }
            } else {
                if (isset($values[$this->group])) {
                    $data = $values[$this->group];
                    if (isset($data['__CATEGORIES__'][$this->dataField])) {
                        $value = $data['__CATEGORIES__'][$this->dataField];
                        if ($this->itemsDataField != null && isset($data[$this->itemsDataField])) {
                            $items = $data[$this->itemsDataField];
                        }
                    }
                }
            }

            if ($items != null) {
                $this->setItems($items);
            }

            $this->setSelectedValue($value);

        } else {
            parent::loadValue($render, $values);
        }
    }
}

