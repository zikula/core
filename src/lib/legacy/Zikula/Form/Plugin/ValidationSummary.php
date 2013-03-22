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
 * Validation summary
 */
class Zikula_Form_Plugin_ValidationSummary extends Zikula_Form_AbstractPlugin
{
    /**
     * CSS class of the summary.
     *
     * @var string
     */
    public $cssClass = 'z-form-validationSummary z-errormsg';

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
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $validators = & $view->validators;

        $html = '';
        foreach ($validators as $validator) {
            if (!$validator->isValid) {
                $label = '';
                if (get_class($validator) == 'Zikula_Form_Plugin_RadioButton') {
                    foreach ($view->plugins as $plugin) {
                        if (get_class($plugin) == 'Zikula_Form_Plugin_Label' && $plugin->for == $validator->dataField) {
                            $label = $plugin->text;
                            break;
                        }
                    }
                }
                $label = !empty($label) ? $label : $validator->myLabel;
                $html .= "<li><label for=\"{$validator->id}\">" . DataUtil::formatForDisplay($label) . ': ';
                $html .=  DataUtil::formatForDisplay($validator->errorMessage) . "</label></li>\n";
            }
        }

        if ($html != '') {
            $html = "<div class=\"{$this->cssClass}\">\n<ul>\n{$html}</ul>\n</div>\n";
        }

        return $html;
    }
}
