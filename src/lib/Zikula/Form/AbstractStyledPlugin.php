<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Base plugin class for plugins that uses CSS styling.
 *
 * This plugin adds attributes like "color", "back_groundcolor" and "font_weight" to plugins that extends it.
 * The extending plugin must call {@link Zikula_Form_Plugin::renderAttributes()} to use the added CSS features.
 * See also {@link Zikula_Form_Plugin_TextInput} for an example implementation.
 *
 * The support CSS styles are listed in the $styleElements array. Please use this as a reference. Underscores
 * are converted to hyphens in the resulting output to match the correct CSS styles. When you need to use unsupported
 * CSS styles then just write them directly in the style parameter of the plugin:
 * <code>
 * {formtextinput id='title' maxLength='100' width='30em' style='border-left: 1px solid red;'}
 * </code>
 *
 * You can also add styling in the code by adding key/value pairs to $styleAttributes. Example:
 * <code>
 * $this->styleAttributes['border-right'] = '1px solid green';
 * </code>
 */
abstract class Zikula_Form_AbstractStyledPlugin extends Zikula_Form_AbstractPlugin
{
    /**
     * Styles added programatically.
     *
     * @var array
     */
    public $styleAttributes = array();


    /**
     * Retrieve the styles added programatically.
     *
     * @return array The styles.
     */
    public function getStyleAttributes()
    {
        return $this->styleAttributes;
    }

    /**
     * Render attributes.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string
     */
    public function renderAttributes(Zikula_Form_View $view)
    {
        static $styleElements = array('width', 'height', 'color', 'background_color', 'border', 'padding', 'margin', 'float', 'display', 'position', 'visibility', 'overflow', 'clip', 'font', 'font_family', 'font_style', 'font_weight', 'font_size');

        $attr = '';
        $style = '';
        foreach ($this->attributes as $name => $value) {
            if ($name == 'style') {
                $style = $value;
            } else if (in_array($name, $styleElements)) {
                $this->styleAttributes[$name] = $value;
            } else {
                $attr .= " {$name}=\"{$value}\"";
            }
        }

        $style = trim($style);
        if (count($this->styleAttributes) > 0 && strlen($style) > 0 && $style[strlen($style) - 1] != ';') {
            $style .= ';';
        }

        foreach ($this->styleAttributes as $name => $value) {
            $style .= str_replace('_', '-', $name) . ":$value;";
        }

        if (!empty($style)) {
            $attr .= " style=\"{$style}\"";
        }

        return $attr;
    }
}
