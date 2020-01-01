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

namespace Zikula\SearchModule;

use Symfony\Component\Form\FormBuilderInterface;

interface SearchableInterface
{
    /**
     * Modify the search form
     * Note that the `active` status (checkbox) is already included
     */
    public function amendForm(FormBuilderInterface $form): void;

    /**
     * Get the search results.
     * Must return an array of SearchResultEntity[].
     */
    public function getResults(array $words, string $searchType = 'AND', ?array $modVars = []): array;

    /**
     * Return an array of errors generated during the search action
     * in the format [<extensionName>: <errorText>].
     */
    public function getErrors(): array;

    /**
     * Return the name of the providing bundle.
     */
    public function getBundleName(): string;
}
