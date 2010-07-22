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
 * URL input for Forms
 *
 * The URL input plugin is a text input plugin that only allows URLs to be posted.
 *
 * You can also use all of the features from the Form_Plugin_TextInput plugin since the URL input
 * inherits from it.
 *
 * A valid URL must contain a protocol prefix ("http:" for instance)
 */
class Form_Plugin_URLInput extends Form_Plugin_TextInput
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
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Render event handler.
     *
     * @param Form_View $view Reference to Form_View object.
     *
     * @return string The rendered output
     */
    function render($view)
    {
        if (!empty($this->defaultText) && ($this->text == null || empty($this->text))) {
            $this->text = $this->defaultText;
        }

        return parent::render($view);
    }

    /**
     * Create event handler.
     *
     * @param Form_View $view Reference to Form_View object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Form_Plugin
     * @return void
     */
    function create($view, &$params)
    {
        $this->maxLength = 2000;

        parent::create($view, $params);

        $this->cssClass .= ' url';
    }

    /**
     * Decode post back event.
     *
     * @param Form_View $view Reference to Form_View object.
     *
     * @return void
     */
    function decodePostBackEvent($view)
    {
        if (!empty($this->defaultText) && $this->text == $this->defaultText) {
            $this->text = null;
        }
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
            if (!System::varValidate($this->text, 'url')) {
                $this->setError(__('Error! Invalid URL.'));
            }
        }
    }
}
