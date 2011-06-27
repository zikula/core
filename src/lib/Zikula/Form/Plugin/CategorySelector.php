<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 * @subpackage Zikula_Form_AbstractPlugin
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
class Zikula_Form_Plugin_CategorySelector extends Zikula_Form_Plugin_DropdownList
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
     * // Template: {formcategoryselector id=myCat category=xxx enableDBUtil=1}
     * // Result:
     * array(
     *   'title' => 'Item title',
     *   '__CATEGORIES__' => array('myCat' => XX)
     * )
     * </code>
     *
     * @var boolean (default false)
     */
    public $enableDBUtil;

    /**
     * Enable save/load of values in separate Categories field for use in Doctrine.
     *
     * @var boolean (default false)
     */
    public $enableDoctrine;

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
     * this method is static because it is also called by the
     * CategoryCheckboxList plugin
     *
     * @param object  &$list               The list object (here: $this).
     * @param boolean $includeEmptyElement Whether or not to include an empty null item.
     * @param array   $params              The parameters passed from the Smarty plugin.
     *
     * @return void
     */
    static function loadParameters(&$list, $includeEmptyElement, $params)
    {
        $all            = isset($params['all'])         ? $params['all']         : false;
        $lang           = isset($params['lang'])        ? $params['lang']        : ZLanguage::getLanguageCode();
        $list->category = isset($params['category'])    ? $params['category']    : 0;
        $list->editLink = isset($params['editLink'])    ? $params['editLink']    : true;
        $includeLeaf    = isset($params['includeLeaf']) ? $params['includeLeaf'] : true;
        $includeRoot    = isset($params['includeRoot']) ? $params['includeRoot'] : false;
        $path           = isset($params['path'])        ? $params['path']        : '';
        $pathfield      = isset($params['pathfield'])   ? $params['pathfield']   : 'path';
        $recurse        = isset($params['recurse'])     ? $params['recurse']     : true;
        $relative       = isset($params['relative'])    ? $params['relative']    : true;
        $sortField      = isset($params['sortField'])   ? $params['sortField']   : 'sort_value';

        $allCats = array();

        // if we don't have a category-id we see if we can get a category by path
        if (!$list->category && $path) {
            $list->category = CategoryUtil::getCategoryByPath($path, $pathfield);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all, null, '', null, $sortField);

        } elseif (is_array($list->category) && isset($list->category['id']) && is_integer($list->category['id'])) {
            // check if we have an actual category object with a numeric ID set
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all, null, '', null, $sortField);

        } elseif (is_numeric($list->category)) {
            // check if we have a numeric category
            $list->category = CategoryUtil::getCategoryByID($list->category);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all, null, '', null, $sortField);

        } elseif (is_string($list->category) && strpos($list->category, '/') === 0) {
            // check if we have a string/path category
            $list->category = CategoryUtil::getCategoryByPath($list->category, $pathfield);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all, null, '', null, $sortField);
        }

        if (!$allCats) {
            $allCats = array();
        }

        if ($list->mandatory) {
            $list->addItem('- - -', null);
        }

        $line = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -';

        if ($includeEmptyElement) {
            $list->addItem('', null);
        }

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
     * @param Zikula_Form_View $view    Reference to Form render object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    function load(Zikula_Form_View $view, &$params)
    {
        $this->includeEmptyElement = (isset($params['includeEmptyElement']) ? $params['includeEmptyElement'] : false);
        $this->enableDBUtil = (isset($params['enableDBUtil']) ? $params['enableDBUtil'] : false);
        $this->enableDoctrine = (isset($params['enableDoctrine']) ? $params['enableDoctrine'] : false);

        self::loadParameters($this, $this->includeEmptyElement, $params);

        parent::load($view, $params);
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return string The rendered output
     */
    function render(Zikula_Form_View $view)
    {
        $result = parent::render($view);

        if ($this->editLink && !empty($this->category) && SecurityUtil::checkPermission('Categories::', "$this->category[id]::", ACCESS_EDIT)) {
            $url = DataUtil::formatForDisplay(ModUtil::url('Categories', 'user', 'edit', array('dr' => $this->category['id'])));
            $result .= "&nbsp;&nbsp;<a href=\"{$url}\"><img src=\"images/icons/extrasmall/xedit.png\" title=\"" . __('Edit') . '" alt="' . __('Edit') . '" /></a>';
        }

        return $result;
    }

    /**
     * Saves value in data object.
     *
     * Called by the render when doing $render->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Zikula_Form_View $view  Reference to Form render object.
     * @param array            &$data Data object.
     *
     * @return void
     */
    function saveValue(Zikula_Form_View $view, &$data)
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
        } else if ($this->enableDoctrine && $this->dataBased) {
            if ($this->group == null) {
                $data['Categories'][$this->dataField] = array('category_id' => $this->getSelectedValue(),
                                                              'reg_property' => $this->dataField);
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group]['Categories'][$this->dataField] = array('category_id' => $this->getSelectedValue(),
                                                                            'reg_property' => $this->dataField);
            }
        } else {
            parent::saveValue($view, $data);
        }
    }

    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's Zikula_Form_View::setValues.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View render object.
     * @param array            &$values Values to load.
     *
     * @return void
     */
    function loadValue(Zikula_Form_View $view, &$values)
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
        } else if ($this->enableDoctrine && $this->dataBased) {
            $items = null;
            $value = null;

            if ($this->group == null) {
                if ($this->dataField != null && isset($values['Categories'][$this->dataField])) {
                    $value = $values['Categories'][$this->dataField]['category_id'];
                }
                if ($this->itemsDataField != null && isset($values[$this->itemsDataField])) {
                    $items = $values[$this->itemsDataField];
                }
            } else {
                if (isset($values[$this->group])) {
                    $data = $values[$this->group];
                    if (isset($data['Categories'][$this->dataField])) {
                        $value = $data['Categories'][$this->dataField]['category_id'];
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
            parent::loadValue($view, $values);
        }
    }
}
