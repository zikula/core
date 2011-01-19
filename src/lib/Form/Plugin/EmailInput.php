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
 * E-mail input for Form_View
 *
 * The e-mail input plugin is a text input plugin that only allows e-mails to be posted.
 *
 * You can also use all of the features from the Form_Plugin_TextInput plugin since the e-mail input
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
     * @param Form_View $view    Reference to Form_View object.
     * @param array     &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Form_Plugin
     * @return void
     */
    function create($view, &$params)
    {
        $this->maxLength = 100;

        parent::create($view, $params);

        $this->cssClass .= ' z-form-email';
    }

    /**
     * Validates the input.
     *
     * @param Form_View $view Reference to Form_View object.
     *
     * @return void
     */
    function validate($view)
    {
        parent::validate($view);

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
