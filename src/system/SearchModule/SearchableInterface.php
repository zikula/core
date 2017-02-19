<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
     *
     * @param FormBuilderInterface $form
     */
    public function amendForm(FormBuilderInterface $form);

    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though (form data from `amendForm` method)
     * @return array (Core-2.0 modules MUST return an array of SearchResultEntity[])
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null);

    /**
     * Return an array of errors generated during the search action
     * in the format [<extensionName>: <errorText>]
     * @return array
     */
    public function getErrors();
}
