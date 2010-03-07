<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Web form label
 * Use this to create labels for your input fields in a web form. Example:
 * <code>
 * <!--[formlabel text="Title" for="title"]-->:
 * <!--[formtextinput id="title"]-->
 * </code>
 * The rendered output is an HTML label element with the "for" value
 * set to the supplied id. In addition to this, the pnFormLabel plugin also sets
 * "myLabel" on the "pointed-to" plugin to the supplied label text. This enables
 * the validation summary to display the label text.
 *
 * @package pnForm
 * @subpackage Plugins
 */
class Form_Plugin_Label extends Form_StyledPlugin
{
    /**
     * Text to show as label
     * @var string
     */
    protected $text;

    /**
     * Allow HTML in label? 1=yes, otherwise no
     * @var int
     */
    protected $html;

    /**
     * Labelled plugin's ID
     * @var string
     */
    protected $for;

    /**
     * CSS class to use
     * @var string
     */
    protected $cssClass;

    /**
     * Enable or disable the mandatory asterisk
     * @var bool
     */
    protected $mandatorysym;

    function getFilename()
    {
        return __FILE__;
    }

    function create(&$render, &$params)
    {
    }

    function render(&$render)
    {
        $idHtml = $this->getIdHtml();

        $text = $render->TranslateForDisplay($this->text, ($this->html == 1) ? false : true);

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

    function postRender(&$render)
    {
        $plugin = & $render->GetPluginById($this->for);
        if ($plugin != null) {
            $plugin->myLabel = $render->TranslateForDisplay($this->text, ($this->html == 1) ? false : true);
            //echo "Set label '$this->text' on $plugin->id. ";
        }
    }
}


