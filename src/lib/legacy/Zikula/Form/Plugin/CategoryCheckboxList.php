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
 * Category selector
 *
 * This plugin creates a category selector using a series of checkboxes
 *
 * @deprecated for Symfony2 Forms
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
     * [
     *   'title' => 'Item title',
     *   '__CATEGORIES__' => ['myCat' => XX]
     * ]
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
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     * @param array            &$params Parameters passed from the Smarty plugin function
     *
     * @return void
     */
    public function load(Zikula_Form_View $view, &$params)
    {
        if (is_array($this->selectedValue)) {
            $selectedValue = [];
            foreach ($this->selectedValue as $v) {
                if (!is_array($v)) {
                    $selectedValue[] = $v;
                } elseif (isset($v['categoryId'])) {
                    $selectedValue[] = $v['categoryId'];
                }
            }
            $this->selectedValue = $selectedValue;
        }

        Zikula_Form_Plugin_CategorySelector::loadParameters($this, false, $params);

        parent::load($view, $params);
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $result = parent::render($view);

        if ($this->editLink && !empty($this->category) && SecurityUtil::checkPermission('ZikulaCategoriesModule::', "{$this->category['id']}::", ACCESS_EDIT)) {
            $url = DataUtil::formatForDisplay(ModUtil::url('ZikulaCategoriesModule', 'user', 'edit', ['dr' => $this->category['id']]));
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
     * @param Zikula_Form_View $view Reference to Form render object
     * @param array            &$data Data object
     *
     * @return void
     */
    public function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->enableDBUtil && $this->dataBased) {
            if (null == $this->group) {
                $data['__CATEGORIES__'][$this->dataField] = $this->getSelectedValue();
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = [];
                }
                $data[$this->group]['__CATEGORIES__'][$this->dataField] = $this->getSelectedValue();
            }
        } elseif ($this->enableDoctrine && $this->dataBased) {
            if (null == $this->group) {
                $data['Categories'][$this->dataField] = [
                    'category_id' => $this->getSelectedValue(),
                    'reg_property' => $this->dataField
                ];
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = [];
                }
                $data[$this->group]['Categories'][$this->dataField] = [
                    'category_id' => $this->getSelectedValue(),
                    'reg_property' => $this->dataField
                ];
            }
        } elseif ($this->doctrine2) {
            $entity = $view->get_template_vars($this->group);
            $entityClass = get_class($entity);

            // load category from db
            $entityManager = ServiceUtil::getService('doctrine.orm.default_entity_manager');

            $collection = $entityManager->getClassMetadata($entityClass)
                ->getFieldValue($entity, $this->dataField);

            if (!$collection) {
                $collection = new \Doctrine\Common\Collections\ArrayCollection();
                $entityManager->getClassMetadata($entityClass)
                   ->setFieldValue($entity, $this->dataField, $collection);
            }

            if (is_array($this->getSelectedValue())) {
                $selectedValues = $this->getSelectedValue();
            } else {
                $selectedValues[] = $this->getSelectedValue();
            }
            $selectedValues = array_combine($selectedValues, $selectedValues);

            foreach ($collection->getKeys() as $key) {
                $entityCategory = $collection->get($key);

                if ($entityCategory->getCategoryRegistryId() == $this->registryId) {
                    $categoryId = $entityCategory->getCategory()->getId();

                    if (isset($selectedValues[$categoryId])) {
                        unset($selectedValues[$categoryId]);
                    } else {
                        $collection->remove($key);
                    }
                }
            }

            // we do NOT flush here, as the calling module is responsible for that (Guite)
            //$em->flush();

            $categoryEntityClass = 'Zikula_Doctrine2_Entity_Category';
            if (false !== strpos($entityClass, '\\')) {
                // if using namespaces, use new base class
                $categoryEntityClass = 'Zikula\CategoriesModule\Entity\CategoryEntity';
            }

            foreach ($selectedValues as $selectedValue) {
                if (null === $selectedValue) {
                    // If no category has been selected.
                    continue;
                }
                $category = $em->find($categoryEntityClass, $selectedValue);
                $class = $em->getClassMetadata($entityClass)->getAssociationTargetClass($this->dataField);
                $collection->add(new $class($this->registryId, $category, $entity));
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
     * @param Zikula_Form_View $view Reference to Zikula_Form_View render object
     * @param array            &$values Values to load
     *
     * @return void
     */
    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->enableDBUtil && $this->dataBased) {
            $items = null;
            $value = null;

            if (null == $this->group) {
                if (null != $this->dataField && isset($values['__CATEGORIES__'][$this->dataField])) {
                    $value = $values['__CATEGORIES__'][$this->dataField];
                }
                if (null != $this->itemsDataField && isset($values[$this->itemsDataField])) {
                    $items = $values[$this->itemsDataField];
                }
            } else {
                if (isset($values[$this->group])) {
                    $data = $values[$this->group];
                    if (isset($data['__CATEGORIES__'][$this->dataField])) {
                        $value = $data['__CATEGORIES__'][$this->dataField];
                        if (null != $this->itemsDataField && isset($data[$this->itemsDataField])) {
                            $items = $data[$this->itemsDataField];
                        }
                    }
                }
            }

            if (null != $items) {
                $this->setItems($items);
            }

            $this->setSelectedValue($value);
        } elseif ($this->enableDoctrine && $this->dataBased) {
            $items = null;
            $value = null;

            if (null == $this->group) {
                if (null != $this->dataField && isset($values['Categories'][$this->dataField])) {
                    $value = $values['Categories'][$this->dataField]['category_id'];
                }
                if (null != $this->itemsDataField && isset($values[$this->itemsDataField])) {
                    $items = $values[$this->itemsDataField];
                }
            } else {
                if (isset($values[$this->group])) {
                    $data = $values[$this->group];
                    if (isset($data['Categories'][$this->dataField])) {
                        $value = $data['Categories'][$this->dataField]['category_id'];
                        if (null != $this->itemsDataField && isset($data[$this->itemsDataField])) {
                            $items = $data[$this->itemsDataField];
                        }
                    }
                }
            }

            if (null != $items) {
                $this->setItems($items);
            }

            $this->setSelectedValue($value);
        } elseif ($this->doctrine2) {
            if (isset($values[$this->group])) {
                $entity = $values[$this->group];
                if (isset($entity[$this->dataField])) {
                    $collection = $entity[$this->dataField];
                    $selectedValues = [];
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
