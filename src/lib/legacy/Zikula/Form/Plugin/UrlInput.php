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
 * URL input for Forms
 *
 * The URL input plugin is a text input plugin that only allows URLs to be posted.
 *
 * You can also use all of the features from the Zikula_Form_Plugin_TextInput plugin since the URL input
 * inherits from it.
 *
 * A valid URL must contain a protocol prefix ("http:" for instance)
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_UrlInput extends Zikula_Form_Plugin_TextInput
{
    /**
     * Default text to display instead of empty.
     *
     * @var string
     */
    protected $defaultText;

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
        $this->maxLength = 2000;
        $params['textMode'] = 'url';

        parent::create($view, $params);

        $this->cssClass .= ' z-form-url';
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        if (!empty($this->defaultText) && ($this->text == null || empty($this->text))) {
            $this->text = $this->defaultText;
        }

        return parent::render($view);
    }

    /**
     * Decode post back event.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function decodePostBackEvent(Zikula_Form_View $view)
    {
        if (!empty($this->defaultText) && $this->text == $this->defaultText) {
            $this->text = null;
        }
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
            if (!System::varValidate($this->text, 'url')) {
                $this->setError(__('Error! Invalid URL.'));
            }
        }
    }
}
