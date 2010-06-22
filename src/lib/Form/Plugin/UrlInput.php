<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Form
 * @subpackage Form_Plugin
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * URL input for pnForms
 *
 * The URL input plugin is a text input plugin that only allows URLs to be posted.
 *
 * You can also use all of the features from the pnFormTextInput plugin since the URL input
 * inherits from it.
 *
 * A valid URL must contain a protocol prefix ("http:" for instance)
 */
class Form_Plugin_URLInput extends Form_Plugin_TextInput
{
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
        $this->maxLength = 2000;

        parent::create($render, $params);

        $this->cssClass .= ' url';
    }

    /**
     * Validates the input.
     * 
     * @param Form_Render &$render Reference to Form render object.
     * 
     * @return void
     */
    function validate(&$render)
    {
        parent::validate($render);
        if (!$this->isValid) {
            return;
        }

        if (!empty($this->text)) {
            if (!System::varValidate($this->text, 'url')) {
                $this->setError(__('Error! Invalid URL.'));
            }
        }
    }
}

