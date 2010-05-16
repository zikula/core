<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Validation summary
 */
class Form_Plugin_ValidationSummary extends Form_Plugin
{
    /**
     * CSS class of the summary
     */
    public $cssClass = 'validationSummary z-errormsg';

    function getFilename()
    {
        return __FILE__;
    }

    function render(&$render)
    {
        $validators = & $render->Validators;
        $html = '';
        foreach ($validators as $validator) {
            if (!$validator->isValid) {
                $html .= "<li><label for=\"$validator->id\">" . DataUtil::formatForDisplay($validator->myLabel) . ': ';
        $html .=  DataUtil::formatForDisplay($validator->errorMessage) . "</label></li>\n";
            }
        }

        if ($html != '') {
            $html = "<div class=\"{$this->cssClass}\">\n<ul>\n" . $html . "</ul>\n</div>\n";
        }

        return $html;
    }
}

