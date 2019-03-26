<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\Content;

interface ContentTypeInterface
{
    const CATEGORY_BASIC = 'basic';

    const CATEGORY_EXTERNAL = 'external';

    const CATEGORY_INTEGRATION = 'integration';

    const CATEGORY_EXPERT = 'expert';

    const CONTEXT_VIEW = 'view';

    const CONTEXT_EDIT = 'edit';

    const CONTEXT_TRANSLATION = 'translation';

    /**
     * Returns the name of this content type.
     * @return string
     */
    public function getName();

    /**
     * Returns the category of this content type.
     * @return boolean
     */
    public function getCategory();

    /**
     * Returns the icon name (FontAwesome icon code suffix, e.g. "pencil").
     * @return string
     */
    public function getIcon();

    /**
     * Returns the title of this content type.
     * @return string
     */
    public function getTitle();

    /**
     * Returns the description of this content type.
     * @return string
     */
    public function getDescription();

    /**
     * Returns an extended plugin information shown on settings page.
     * @return string
     */
    public function getAdminInfo();

    /**
     * Returns whether this content type is active or not.
     * @return boolean
     */
    public function isActive();

    /**
     * Returns the minimum amount of (Bootstrap) grid columns required by this content type.
     * This layout constraint is used during page editing to avoid unwanted shrinking.
     * @return integer
     */
    public function getMinimumAmountOfGridColumns();

    /**
     * Returns an array of data values retrieved from persistence with proper default values.
     * @return array
     */
    public function getDefaultData();

    /**
     * Returns a list of translatable field names if any.
     * @return array
     */
    public function getTranslatableDataFields();

    /**
     * Returns searchable text, that is all the text that is searchable through Zikula's standard
     * search interface. You must strip the text of any HTML tags and other structural information
     * before returning the text. If you have multiple searchable text fields then concatenate all
     * the text from these and return the full string.
     * @return string
     */
    public function getSearchableText();

    /**
     * Returns output for normal display.
     * @return string
     */
    public function displayView();

    /**
     * Returns output for display in editing mode.
     * @return string
     */
    public function displayEditing();

    /**
     * Returns the full path to the template for the display function in 'namespaced' name-style.
     *     e.g. `return '@AcmeMyBundle/ContentType/headingView.html.twig';`
     * @return string
     */
    public function getViewTemplatePath();

    /**
     * Returns the full name of the edit form's template in 'namespaced' name-style.
     *     e.g. `return '@AcmeMyBundle/ContentType/headingEdit.html.twig';`
     * @return string
     */
    public function getEditTemplatePath();

    /**
     * Returns the FqCN of the form class (e.g. return HeadingType::class;)
     * @return string
     */
    public function getEditFormClass();

    /**
     * Returns an array of form options.
     * @param string $context The target page context (one of CONTEXT* constants)
     * @return array
     */
    public function getEditFormOptions($context);

    /**
     * Returns an array of required assets.
     * @param string $context The target page context (one of CONTEXT* constants)
     * @return array
     */
    public function getAssets($context);

    /**
     * Returns the name of the JS function to execute or null for nothing.
     * The function must be registered in the global scope and must not expect any arguments.
     * @param string $context The target page context (one of CONTEXT* constants)
     * @return string
     */
    public function getJsEntrypoint($context);

    /**
     * Return the name of the providing bundle.
     * @return string
     */
    public function getBundleName();
}
