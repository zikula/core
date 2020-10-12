<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Twig;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\Bundle\CoreBundle\RouteUrl;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class SearchRuntime implements RuntimeExtensionInterface
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(VariableApiInterface $variableApi, RouterInterface $router)
    {
        $this->variableApi = $variableApi;
        $this->router = $router;
    }

    /**
     * The zikulasearchmodule_searchVarToFieldNames function generates a flat lost of field names
     * for hidden form fields from a nested array set.
     *
     * @param array|string $data The data that should be stored in hidden fields (nested arrays allowed).
     *                           If an empty string is given and $isRecursiveCall is false the module vars are used by default
     */
    public function searchVarToFieldNames($data = '', string $prefix = 'modvar', bool $isRecursiveCall = false): array
    {
        $dataValues = '' !== $data && $isRecursiveCall ? $data : $this->variableApi->getAll('ZikulaSearchModule');

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
     * Generate the url from a search result.
     */
    public function generateUrl(RouteUrl $routeUrl): string
    {
        try {
            $url = $this->router->generate($routeUrl->getRoute(), $routeUrl->getArgs()) . $routeUrl->getFragment();
        } catch (RouteNotFoundException $exception) {
            $url = '';
        }

        return $url;
    }

    /**
     * Highlight words in a string by adding `class="highlight1"`.
     */
    public function highlightWords(string $string, string $words, int $highlightType = 1): string
    {
        $singleWords = explode(' ', $words);
        foreach ($singleWords as $word) {
            $string = str_ireplace($word, '<strong class="highlight' . $highlightType . '">' . $word . '</strong>', $string);
        }

        return $string;
    }
}
