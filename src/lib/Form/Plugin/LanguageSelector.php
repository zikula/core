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
 * Language selector
 *
 * This plugin creates a language selector using a dropdown list.
 * The selected value of the base dropdown list will be set to the 3-letter language code of
 * the selected language.
 */
class Form_Plugin_LanguageSelector extends Form_Plugin_DropdownList
{
    /**
     * Enable or disable use of installed languages only.
     *
     * Normally you can only choose one of the installed languages with the language selector,
     * but by setting onlyInstalledLanguages to false you can get a list of all possible language.
     *
     * @var boolean
     */
    protected $onlyInstalledLanguages = true;

    /**
     * Add an option 'All' on top of the language list.
     *
     * @var boolean
     */
    protected $addAllOption = true;

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
        if ($this->mandatory)
            $this->addItem('---', null);

        if ($this->addAllOption) {
            $this->addItem(DataUtil::formatForDisplay(__('All')), '');
        }

        if ($this->onlyInstalledLanguages) {
            $langList = ZLanguage::getInstalledLanguageNames();

            foreach ($langList as $code => $name) {
                $this->addItem($name, $code);
            }
        } else {
            $langList = ZLanguage::languageMap();

            foreach ($langList as $code => $name) {
                $this->addItem($name, $code);
            }
        }

        parent::load($render, $params);
    }
}

