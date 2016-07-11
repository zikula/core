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
 * Language selector
 *
 * This plugin creates a language selector using a dropdown list.
 * The selected value of the base dropdown list will be set to the 3-letter language code of
 * the selected language.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_LanguageSelector extends Zikula_Form_Plugin_DropdownList
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
        if ($this->mandatory) {
            $this->addItem('---', null);
        }

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

        parent::load($view, $params);
    }
}
