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

namespace Zikula\ExtensionsModule\ModuleInterface\Content;

interface ContentTypeInterface
{
    public const CATEGORY_BASIC = 'basic';

    public const CATEGORY_EXTERNAL = 'external';

    public const CATEGORY_INTEGRATION = 'integration';

    public const CATEGORY_EXPERT = 'expert';

    public const CONTEXT_VIEW = 'view';

    public const CONTEXT_EDIT = 'edit';

    public const CONTEXT_TRANSLATION = 'translation';

    /**
     * Returns the name of this content type.
     */
    public function getName(): string;

    /**
     * Returns the category of this content type.
     */
    public function getCategory(): string;

    /**
     * Returns the icon name (FontAwesome icon code suffix, e.g. "pencil").
     */
    public function getIcon(): string;

    /**
     * Returns the title of this content type.
     */
    public function getTitle(): string;

    /**
     * Returns the description of this content type.
     */
    public function getDescription(): string;

    /**
     * Returns an extended plugin information shown on settings page.
     */
    public function getAdminInfo(): string;

    /**
     * Returns whether this content type is active or not.
     */
    public function isActive(): bool;

    /**
     * Returns the minimum amount of (Bootstrap) grid columns required by this content type.
     * This layout constraint is used during page editing to avoid unwanted shrinking.
     */
    public function getMinimumAmountOfGridColumns(): int;

    /**
     * Returns an array of data values retrieved from persistence with proper default values.
     */
    public function getDefaultData(): array;

    /**
     * Returns a list of translatable field names if any.
     */
    public function getTranslatableDataFields(): array;

    /**
     * Returns searchable text, that is all the text that is searchable through Zikula's standard
     * search interface. You must strip the text of any HTML tags and other structural information
     * before returning the text. If you have multiple searchable text fields then concatenate all
     * the text from these and return the full string.
     */
    public function getSearchableText(): string;

    /**
     * Returns output for normal or editing display.
     */
    public function display(bool $editMode = false): string;

    /**
     * Returns output for normal display.
     */
    public function displayView(): string;

    /**
     * Returns output for display in editing mode.
     */
    public function displayEditing(): string;

    /**
     * Returns the full path to the template for the display function in 'namespaced' name-style.
     *     e.g. `return '@AcmeMyBundle/ContentType/headingView.html.twig';`
     */
    public function getViewTemplatePath(): string;

    /**
     * Returns the full name of the edit form's template in 'namespaced' name-style.
     *     e.g. `return '@AcmeMyBundle/ContentType/headingEdit.html.twig';`
     */
    public function getEditTemplatePath(): string;

    /**
     * Returns the FqCN of the form class (e.g. return HeadingType::class;)
     */
    public function getEditFormClass(): string;

    /**
     * Returns an array of form options for a target page context (one of CONTEXT* constants).
     */
    public function getEditFormOptions(string $context): array;

    /**
     * Returns an array of required assets for a target page context (one of CONTEXT* constants).
     */
    public function getAssets(string $context): array;

    /**
     * Returns the name of the JS function to execute for a target page context
     * (one of CONTEXT* constants) or null for nothing.
     * The function must be registered in the global scope and must not expect any arguments.
     */
    public function getJsEntrypoint(string $context): ?string;

    /**
     * Return the name of the providing bundle.
     */
    public function getBundleName(): string;
}
