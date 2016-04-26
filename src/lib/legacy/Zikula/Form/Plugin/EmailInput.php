<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * E-mail input for Zikula_Form_View
 *
 * The e-mail input plugin is a text input plugin that only allows e-mails to be posted.
 *
 * You can also use all of the features from the Zikula_Form_Plugin_TextInput plugin since the e-mail input
 * inherits from it.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_EmailInput extends Zikula_Form_Plugin_TextInput
{
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
     * Create event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        $this->maxLength = 100;
        $params['textMode'] = 'email';

        parent::create($view, $params);

        $this->cssClass .= ' z-form-email';
    }

    /**
     * Validates the input.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function validate(Zikula_Form_View $view)
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
