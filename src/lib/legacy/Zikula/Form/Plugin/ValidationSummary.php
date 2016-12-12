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
 * Validation summary
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_ValidationSummary extends Zikula_Form_AbstractPlugin
{
    /**
     * CSS class of the summary.
     *
     * @var string
     */
    public $cssClass = 'z-form-validationSummary alert alert-danger';

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
     * @param Zikula_Form_View $view Reference to Form render object
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $validators = $view->validators;

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
                $html .= DataUtil::formatForDisplay($validator->errorMessage) . "</label></li>\n";
            }
        }

        if ($html != '') {
            $html = "<div class=\"{$this->cssClass}\">\n<ul>\n{$html}</ul>\n</div>\n";
        }

        return $html;
    }
}
