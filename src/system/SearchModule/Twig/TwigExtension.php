<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Twig;

use ModUtil;
use StringUtil;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Twig extension class.
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * TwigExtension constructor.
     *
     * @param VariableApi $variableApi VariableApi service instance.
     */
    public function __construct(VariableApi $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    /**
     * Returns a list of custom Twig functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('zikulasearchmodule_searchVarToFieldNames', [$this, 'searchVarToFieldNames']),
            new \Twig_SimpleFunction('zikulasearchmodule_modUrlLegacy', [$this, 'modUrlLegacy'])
        ];
    }

    /**
     * Returns a list of custom Twig filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('zikulasearchmodule_highlightGoogleKeywords', [$this, 'highlightGoogleKeywords']),
        ];
    }

    /**
     * The zikulamailermodule_searchVarToFieldNames function generates a flat lost of field names
     * for hidden form fields from a nested array set
     *
     * @param array|string $data            The data that should be stored in hidden fields (nested arrays allowed).
     *                                      If an empty string is given and $isRecursiveCall is false the module vars are used by default.
     * @param string       $prefix          Optional prefix.
     * @param bool         $isRecursiveCall Flag to determine whether this method has been called recursively.
     *
     * @return array List of hidden form fields.
     */
    public function searchVarToFieldNames($data = '', $prefix = 'modvar', $isRecursiveCall = false)
    {
        $dataValues = $data != '' && $isRecursiveCall ? $data : $this->variableApi->getAll('ZikulaSearchModule');

        $fields = [];
        if (empty($dataValues)) {
            return $fields;
        }

        if (is_array($dataValues)) {
            foreach ($dataValues as $key => $entryData) {
                if (empty($entryData)) {
                    continue;
                }
                $subFields = $this->searchVarToFieldNames($entryData, $prefix . '[' . $key . ']', true);
                $fields = array_merge($fields, $subFields);
            }
        } else {
            $fields[$prefix] = $dataValues;
        }

        return $fields;
    }

    /**
     * Legacy bridging method for arbitrary module urls.
     *
     * @param string $moduleName Name of target module.
     *
     * @return string
     */
    public function modUrlLegacy($moduleName)
    {
        return ModUtil::url($moduleName, 'user', 'index');
    }

    /**
     * Highlights case insensitive google search phrase.
     *
     * @param string  $text         The string to operate on.
     * @param string  $searchPhrase The search phrase.
     * @param integer $contextSize  The number of chars shown as context around the search phrase.
     *
     * @return string
     */
    public function highlightGoogleKeywords($text, $searchPhrase, $contextSize)
    {
        return StringUtil::highlightWords($text, $searchPhrase, $contextSize);
    }

    /**
     * Returns internal name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'zikulasearchmodule_twigextension';
    }
}
