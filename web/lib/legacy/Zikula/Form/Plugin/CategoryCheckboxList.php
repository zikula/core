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
     * Get filename of this file.
     *
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Load event handler.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    function load(Zikula_Form_View $view, &$params)
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
    function render(Zikula_Form_View $view)
    {
        $result = parent::render($view);

        if ($this->editLink && !empty($this->category) && SecurityUtil::checkPermission('Categories::', "{$this->category['id']}::", ACCESS_EDIT)) {
            $url = DataUtil::formatForDisplay(ModUtil::url ('Categories', 'user', 'edit', array('dr' => $this->category['id'])));
            $result .= "<a class=\"z-formnote\" href=\"{$url}\">" . __('Edit') . '</a>';
        }

        return $result;
    }
}
