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
 * This plugin creates a category selector using a series of checkboxes
 */
class Zikula_Form_Plugin_CategoryCheckboxList extends Zikula_Form_Plugin_CheckboxList
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

    public $doctrine2;

    public $registryId;


    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Load event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function load(Zikula_Form_View $view, &$params)
    {
        Zikula_Form_Plugin_CategorySelector::loadParameters($this, false, $params);

        parent::load($view, $params);
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $result = parent::render($view);

        if ($this->editLink && !empty($this->category) && SecurityUtil::checkPermission('ZikulaCategoriesModule::', "{$this->category['id']}::", ACCESS_EDIT)) {
            $url = DataUtil::formatForDisplay(ModUtil::url ('ZikulaCategoriesModule', 'user', 'edit', array('dr' => $this->category['id'])));
            $result .= "<a class=\"help-block\" href=\"{$url}\">" . __('Edit') . '</a>';
        }

        return $result;
    }


    /**
     * Saves value in data object.
     *
     * Called by the render when doing $render->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     * @param array            &$data Data object.
     *
     * @return void
     */
    public function saveValue(Zikula_Form_View $view, &$data)
    {
        Zikula_Form_Plugin_CategorySelector::saveValue($view, $data);
    }


    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's Zikula_Form_View::setValues.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View render object.
     * @param array            &$values Values to load.
     *
     * @return void
     */
    public function loadValue(Zikula_Form_View $view, &$values)
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

        } elseif ($this->enableDoctrine && $this->dataBased) {
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

        } elseif ($this->doctrine2) {

            if (isset($values[$this->group])) {
                $entity = $values[$this->group];
                if (isset($entity[$this->dataField])) {
                    $collection = $entity[$this->dataField];
                    $selectedValues = array();
                    foreach ($collection as $c) {
                        $categoryId = $c->getCategoryRegistryId();
                        if ($categoryId == $this->registryId) {
                            $selectedValues[$c->getCategory()->getId()] = $c->getCategory()->getId();
                        }
                    }
                    $this->setSelectedValue($selectedValues);
                }
            }
        } else {
            parent::loadValue($view, $values);
        }
    }
}
