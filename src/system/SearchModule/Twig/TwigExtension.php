<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Twig;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\ModUrl;
use Zikula\Core\RouteUrl;
use Zikula\Core\UrlInterface;
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
     * @var RouterInterface
     */
    private $router;

    /**
     * TwigExtension constructor.
     *
     * @param VariableApi $variableApi VariableApi service instance
     * @param RouterInterface $router
     */
    public function __construct(VariableApi $variableApi, RouterInterface $router)
    {
        $this->variableApi = $variableApi;
        $this->router = $router;
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
            new \Twig_SimpleFilter('zikulasearchmodule_highlightGoogleKeywords', [$this, 'highlightGoogleKeywords']), // @deprecated
            new \Twig_SimpleFilter('zikulasearchmodule_highlightWords', [$this, 'highlightWords'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('zikulasearchmodule_generateUrl', [$this, 'generateUrl']),
        ];
    }

    /**
     * The zikulasearchmodule_searchVarToFieldNames function generates a flat lost of field names
     * for hidden form fields from a nested array set
     *
     * @param array|string $data            The data that should be stored in hidden fields (nested arrays allowed).
     *                                      If an empty string is given and $isRecursiveCall is false the module vars are used by default
     * @param string       $prefix          Optional prefix
     * @param bool         $isRecursiveCall Flag to determine whether this method has been called recursively
     *
     * @return array List of hidden form fields
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
     * @deprecated remove at Core-2.0
     *
     * @param string $moduleName Name of target module
     *
     * @return string
     */
    public function modUrlLegacy($moduleName)
    {
        return \ModUtil::url($moduleName, 'user', 'index');
    }

    /**
     * Generate the url from a search result
     * @param UrlInterface $url
     * @return string
     */
    public function generateUrl(UrlInterface $url)
    {
        if ($url instanceof ModUrl) { // @deprecated
            return $url->getUrl();
        } elseif ($url instanceof RouteUrl) {
            try {
                return $this->router->generate($url->getRoute(), $url->getArgs()) . $url->getFragment();
            } catch (RouteNotFoundException $exception) {
                // do nothing
            }
        }

        return '';
    }

    /**
     * Highlights case insensitive google search phrase.
     *
     * @deprecated remove at Core-2.0
     * @param string  $text         The string to operate on
     * @param string  $searchPhrase The search phrase
     * @param integer $contextSize  The number of chars shown as context around the search phrase
     *
     * @return string
     */
    public function highlightGoogleKeywords($text, $searchPhrase, $contextSize)
    {
        return \StringUtil::highlightWords($text, $searchPhrase, $contextSize);
    }

    /**
     * Highlight words in a string by adding `class="highlight1"`
     * @param string $string
     * @param string $words
     * @param int $highlightType
     * @return string
     */
    public function highlightWords($string, $words, $highlightType = 1)
    {
        $words = explode(' ', $words);
        foreach ($words as $word) {
            $string = str_ireplace($word, '<strong class="highlight' . $highlightType . '">' . $word . '</strong>', $string);
        }

        return $string;
    }
}
