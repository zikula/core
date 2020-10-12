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

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SearchExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('zikulasearchmodule_searchVarToFieldNames', [SearchRuntime::class, 'searchVarToFieldNames'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('zikulasearchmodule_generateUrl', [SearchRuntime::class, 'generateUrl']),
            new TwigFilter('zikulasearchmodule_highlightWords', [SearchRuntime::class, 'highlightWords'], ['is_safe' => ['html']])
        ];
    }
}
