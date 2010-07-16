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
 * This plugin creates a category selector using a series of checkboxes
 */
class Form_Plugin_CategoryCheckboxList extends Form_Plugin_CheckboxList
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
     * @param Form_View &$render Reference to pnForm render object.
     * @param array       $params  Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    function load(&$render, $params)
    {
        pnFormCategorySelector::loadParameters($this, false, $params);
        parent::load($render, $params);
    }

    /**
     * Render event handler.
     *
     * @param Form_View &$render Reference to Form render object.
     *
     * @return string The rendered output
     */
    function render(&$render)
    {
        $result = parent::render($render);

        if ($this->editLink && !empty($this->category) && SecurityUtil::checkPermission('Categories::', "$this->category[id]::", ACCESS_EDIT)) {
            $url = DataUtil::formatForDisplay(ModUtil::url ('Categories', 'user', 'edit', array('dr' => $this->category['id'])));
            $result .= "<a class=\"z-formnote\" href=\"$url\">" . __('Edit') . '</a>';
        }

        return $result;
    }
}

