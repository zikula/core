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

interface SearchableInterface
{
    /**
     * Get the UI options for search form
     *
     * @param boolean $active if the module checkbox should be checked as active
     * @param array|null $modVars module form vars as previously set
     * @return string
     */
    public function getOptions($active, $modVars = null);

    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though
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
