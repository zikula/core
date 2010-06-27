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
 * Web form label.
 *
 * Use this to create labels for your input fields in a web form. Example:
 * <code>
 * <!--[formlabel text="Title" for="title"]-->:
 * <!--[formtextinput id="title"]-->
 * </code>
 * The rendered output is an HTML label element with the "for" value
 * set to the supplied id. In addition to this, the pnFormLabel plugin also sets
 * "myLabel" on the "pointed-to" plugin to the supplied label text. This enables
 * the validation summary to display the label text.
 */
class Form_Plugin_Label extends Form_StyledPlugin
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
     * @param Form_Render &$render Reference to Form render object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Form_Plugin
     * @return void
     */
    function create(&$render, &$params)
    {
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
        $idHtml = $this->getIdHtml();

        $text = $render->translateForDisplay($this->text, ($this->html == 1) ? false : true);

        if ($this->cssClass != null) {
            $classHtml = " class=\"$this->cssClass\"";
        } else {
            $classHtml = '';
        }

        $attributes = $this->renderAttributes($render);

        $result = "<label{$idHtml} for=\"{$this->for}\"{$classHtml}{$attributes}>$text";

        if ($this->mandatorysym) {
            $result .= '<span class="z-mandatorysym">*</span>';
        }

        $result .= '</label>';
        return $result;
    }


    /**
     * PostRender event handler.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return void
     */
    function postRender(&$render)
    {
        $plugin = & $render->getPluginById($this->for);
        if ($plugin != null) {
            $plugin->myLabel = $render->translateForDisplay($this->text, ($this->html == 1) ? false : true);
            //echo "Set label '$this->text' on $plugin->id. ";
        }
    }
}


