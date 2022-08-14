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

namespace Zikula\Bundle\CoreBundle\Filter;

/**
 * Assist in the display of an alphabetical selector for large result sets
 * In controller:
 *     return [
 *         'templateParam' => $value,
 *         'alpha' => new AlphaFilter('mycustomroute', $routeParameters, $currentLetter),
 *     ];
 * In template:
 *     {{ include(alpha.template) }}
 */
class AlphaFilter
{
    private string $template = '@Core/Filter/alphaFilter.html.twig';

    public function __construct(
        private readonly string $route,
        private readonly array $routeParameters = [],
        private readonly $currentLetter = 'a',
        private readonly $includeNumbers = false
    ) {
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function getCurrentLetter(): string
    {
        return $this->currentLetter;
    }

    public function getIncludeNumbers(): bool
    {
        return $this->includeNumbers;
    }
}
