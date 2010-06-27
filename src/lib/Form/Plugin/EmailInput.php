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
 * E-mail input for pnForms
 *
 * The e-mail input plugin is a text input plugin that only allows e-mails to be posted.
 *
 * You can also use all of the features from the pnFormTextInput plugin since the e-mail input
 * inherits from it.
 */
class Form_Plugin_EMailInput extends Form_Plugin_TextInput
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
        $this->maxLength = 100;

        parent::create($render, $params);

        $this->cssClass .= ' email';
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
            if (!System::varValidate($this->text, 'email')) {
                $this->setError(__('Error! Invalid e-mail address.'));
            }
        }
    }
}

