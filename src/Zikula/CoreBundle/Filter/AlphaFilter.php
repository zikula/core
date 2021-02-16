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
    private $currentLetter;

    private $route;

    private $routeParameters;

    private $template = '@Core/Filter/alphaFilter.html.twig';

    private $includeNumbers = false;

    public function __construct(string $route, array $routeParameters = [], $currentLetter = 'a', $includeNumbers = false)
    {
        $this->route = $route;
        $this->routeParameters = $routeParameters;
        $this->currentLetter = $currentLetter;
        $this->includeNumbers = $includeNumbers;
    }

    public function getCurrentLetter(): string
    {
        return $this->currentLetter;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRouteParameters(array $parameters): self
    {
        $this->routeParameters = $parameters;

        return $this;
    }

    public function setRouteParameter(string $name, ?string $value): void
    {
        $this->routeParameters[$name] = $value;
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function setTemplate(string $templateName): void
    {
        $this->template = $templateName;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setIncludeNumbers(bool $include): void
    {
        $this->includeNumbers = $include;
    }

    public function getIncludeNumbers(): bool
    {
        return $this->includeNumbers;
    }
}
