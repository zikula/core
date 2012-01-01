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
 * Web form label.
 *
 * Use this to create labels for your input fields in a web form. Example:
 * <code>
 * {formlabel __text='Title' for='title'}:
 * {formtextinput id='title'}
 * </code>
 * The rendered output is an HTML label element with the "for" value
 * set to the supplied id. In addition to this, the Zikula_Form_Plugin_Label plugin also sets
 * "myLabel" on the "pointed-to" plugin to the supplied label text. This enables
 * the validation summary to display the label text.
 */
class Zikula_Form_Plugin_Label extends Zikula_Form_AbstractStyledPlugin
{
    /**
     * Text to show as label.
     *
     * @var string
     */
    public $text;

    /**
     * Allow HTML in label? 1=yes, otherwise no.
     *
     * @var integer
     */
    public $html;

    /**
     * Labelled plugin's ID.
     *
     * @var string
     */
    public $for;

    /**
     * CSS class to use.
     *
     * @var string
     */
    public $cssClass;

    /**
     * Enable or disable the mandatory asterisk.
     *
     * @var boolean
     */
    public $mandatorysym;

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
     * Create event handler.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    function create(Zikula_Form_View $view, &$params)
    {
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
        $idHtml = $this->getIdHtml();

        $text = $view->translateForDisplay($this->text, ($this->html == 1) ? false : true);

        if ($this->cssClass != null) {
            $classHtml = " class=\"$this->cssClass\"";
        } else {
            $classHtml = '';
        }

        $attributes = $this->renderAttributes($view);

        $result = "<label{$idHtml} for=\"{$this->for}\"{$classHtml}{$attributes}>{$text}";

        if ($this->mandatorysym) {
            $result .= '<span class="z-form-mandatory-flag">*</span>';
        }

        $result .= '</label>';

        return $result;
    }

    /**
     * PostRender event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    function postRender(Zikula_Form_View $view)
    {
        $plugin = $view->getPluginById($this->for);

        if ($plugin != null) {
            $plugin->myLabel = $view->translateForDisplay($this->text, ($this->html == 1) ? false : true);
        }
    }
}
