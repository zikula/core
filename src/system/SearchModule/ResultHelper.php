<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule;

use ModUtil;

/**
 * Class for doing module based access check and URL creation of search result
 *
 * - The module based access is somewhat deprecated (it still works but is not
 *   used since it makes it impossible to count the number of search result).
 * - The URL for each found item is created here. By doing this we only create
 *   URLs for results the user actually view and save some time this way.
 */
class ResultHelper
{
    /**
     * This variable contains a table of all search plugins (indexed by module name)
     *
     * @var array
     */
    private $search_modules = [];

    /**
     * Setup this helper object
     *
     * @param array $search_modules array of search capable modules
     */
    public function __construct($search_modules)
    {
        $this->search_modules = $search_modules;
    }

    /**
     * Validate search results
     *
     * The decision is delegated to the search plugin (module) that generated the result
     *
     * @param array $datarow the input search result
     *
     * @return bool true to keep the result, false to discard
     */
    public function checkResult(&$datarow)
    {
        // make sure module is supposed to check the result, if not, return true to include result
        if (!in_array($datarow['module'], $this->search_modules)) {
            return true;
        }

        // Get module name
        $module = $datarow['module'];

        // Get plugin information
        $mod = $this->search_modules[$module];

        $ok = true;

        if (!isset($mod['functions'])) {
            return $ok;
        }

        foreach ($mod['functions'] as $contenttype => $function) {
            // Delegate check to search plugin
            // (also allow plugin to write 'url' => ... into $datarow by passing it by reference)
            $ok = $ok && ModUtil::apiFunc($mod['title'], 'search', $function . '_check',
                [
                    'datarow' => &$datarow,
                    'contenttype' => $contenttype
                ]
            );
        }

        return $ok;
    }
}
