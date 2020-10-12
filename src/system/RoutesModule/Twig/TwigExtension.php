<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Twig;

use Twig\TwigFilter;
use Zikula\RoutesModule\Twig\Base\AbstractTwigExtension;

/**
 * Twig extension implementation class.
 */
class TwigExtension extends AbstractTwigExtension
{
    public function getFunctions()
    {
        return [];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('zikularoutesmodule_listEntry', [TwigRuntime::class, 'getListEntry']), // from base class
            new TwigFilter('zikularoutesmodule_formattedTitle', [TwigRuntime::class, 'getFormattedEntityTitle']), // from base class
            new TwigFilter('zikularoutesmodule_arrayToString', [TwigRuntime::class, 'displayArrayAsString'], ['is_safe' => ['html']]),
            new TwigFilter('zikularoutesmodule_pathToString', [TwigRuntime::class, 'displayPathAsString'], ['is_safe' => ['html']])
        ];
    }
}
